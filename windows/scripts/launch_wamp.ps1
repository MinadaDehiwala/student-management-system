$ErrorActionPreference = 'Stop'

$wampRoot = 'C:\wamp64'
$appName = 'campus-app'
$apacheServiceName = 'wampapache64'
$mysqlServiceName = 'wampmysqld64'

function Assert-PathExists {
    param(
        [string] $Path,
        [string] $Label
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        throw "$Label not found at '$Path'."
    }
}

function Assert-Admin {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($identity)
    if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
        throw 'Run this script from an elevated PowerShell session (Run as Administrator).'
    }
}

function Start-WampService {
    param([string] $Name)

    $service = Get-Service -Name $Name -ErrorAction Stop
    if ($service.Status -ne 'Running') {
        Write-Host "Starting service: $Name"
        Start-Service -Name $Name -ErrorAction Stop
        $service.WaitForStatus('Running', [TimeSpan]::FromSeconds(20))
    }

    $service.Refresh()
    if ($service.Status -ne 'Running') {
        throw "Service '$Name' is not running."
    }

    Write-Host "Service running: $Name"
}

Assert-Admin
Assert-PathExists -Path $wampRoot -Label 'WAMP root'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$windowsRoot = Split-Path -Parent $scriptDir
$wwwRoot = Join-Path $wampRoot 'www'
$targetRoot = Join-Path $wwwRoot $appName
$loginUrl = "http://localhost/$appName/login.php"

Assert-PathExists -Path $windowsRoot -Label 'Windows app folder'
Assert-PathExists -Path $wwwRoot -Label 'WAMP www root'

Write-Host '[1/5] Syncing windows app into WAMP www...'
New-Item -ItemType Directory -Path $targetRoot -Force | Out-Null
$robocopyArgs = @(
    $windowsRoot,
    $targetRoot,
    '/MIR',
    '/R:1',
    '/W:1',
    '/XD', '.git',
    '/XF', '.DS_Store'
)
$null = & robocopy @robocopyArgs
if ($LASTEXITCODE -gt 7) {
    throw "robocopy failed with exit code $LASTEXITCODE"
}

Write-Host '[2/5] Starting WAMP services...'
Start-WampService -Name $apacheServiceName
Start-WampService -Name $mysqlServiceName

Write-Host '[3/5] Locating WAMP mysql client...'
$mysqlCandidates = Get-ChildItem -Path (Join-Path $wampRoot 'bin\\mysql\\mysql*\\bin\\mysql.exe') -ErrorAction SilentlyContinue |
    Sort-Object FullName -Descending
if (-not $mysqlCandidates -or $mysqlCandidates.Count -eq 0) {
    throw "Could not find mysql.exe under '$wampRoot\\bin\\mysql\\mysql*\\bin\\mysql.exe'."
}
$mysqlExe = $mysqlCandidates[0].FullName
Write-Host "Using mysql client: $mysqlExe"

$sqlFile = Join-Path $targetRoot 'database.sql'
Assert-PathExists -Path $sqlFile -Label 'database.sql'

Write-Host '[4/5] Importing database.sql into MySQL as root (blank password)...'
Get-Content -Raw -LiteralPath $sqlFile |
    & $mysqlExe '--protocol=TCP' '-h127.0.0.1' '-uroot'
if ($LASTEXITCODE -ne 0) {
    throw "MySQL import failed with exit code $LASTEXITCODE"
}

Write-Host '[5/5] Validating schema and admin seed...'
$validationQuery = @"
SELECT
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'campus_db' AND table_name = 'students') AS students_table,
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'campus_db' AND table_name = 'admins') AS admins_table,
  (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'campus_db' AND table_name = 'student_activity') AS activity_table,
  (SELECT COUNT(*) FROM campus_db.admins WHERE username = 'admin') AS admin_seed;
"@

$validationRaw = & $mysqlExe '--protocol=TCP' '-h127.0.0.1' '-uroot' '--batch' '--skip-column-names' '--execute' $validationQuery
if ($LASTEXITCODE -ne 0) {
    throw "Validation query failed with exit code $LASTEXITCODE"
}

$parts = ($validationRaw -join ' ') -split '\s+'
if ($parts.Count -lt 4) {
    throw "Unexpected validation output: '$validationRaw'"
}

$studentsTable = [int]$parts[0]
$adminsTable = [int]$parts[1]
$activityTable = [int]$parts[2]
$adminSeed = [int]$parts[3]

if ($studentsTable -lt 1 -or $adminsTable -lt 1 -or $activityTable -lt 1 -or $adminSeed -lt 1) {
    throw "Validation failed. students_table=$studentsTable, admins_table=$adminsTable, activity_table=$activityTable, admin_seed=$adminSeed"
}

Write-Host ''
Write-Host 'Launch successful.'
Write-Host "Login URL: $loginUrl"
Write-Host 'Default admin: admin / Admin@12345!'
Start-Process $loginUrl
