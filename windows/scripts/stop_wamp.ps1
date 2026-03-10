$ErrorActionPreference = 'Stop'

$serviceNames = @('wampapache64', 'wampmysqld64')

foreach ($name in $serviceNames) {
    $service = Get-Service -Name $name -ErrorAction SilentlyContinue
    if (-not $service) {
        Write-Host "Service not found: $name"
        continue
    }

    if ($service.Status -eq 'Running') {
        Write-Host "Stopping service: $name"
        Stop-Service -Name $name -ErrorAction Stop
        $service.WaitForStatus('Stopped', [TimeSpan]::FromSeconds(20))
        Write-Host "Service stopped: $name"
    }
    else {
        Write-Host "Service already stopped: $name"
    }
}
