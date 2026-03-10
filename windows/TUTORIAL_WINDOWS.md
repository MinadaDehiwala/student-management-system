# Windows Tutorial (WAMP + Apache)

This guide runs the app on Windows with a single launcher script.

## 1) Prerequisites
- WAMP Server installed at `C:\wamp64`
- Apache + MySQL services available as:
  - `wampapache64`
  - `wampmysqld64`
- MySQL root account with blank password (local dev only)
- PowerShell running **as Administrator**

## 2) Clone the repository
```powershell
git clone https://github.com/MinadaDehiwala/student-management-system.git
cd student-management-system
```

## 3) Launch in one command
From the repository root:

```powershell
powershell -ExecutionPolicy Bypass -File .\windows\scripts\launch_wamp.ps1
```

What this script does:
1. Syncs `windows/` app files to `C:\wamp64\www\campus-app`
2. Starts Apache + MySQL WAMP services
3. Imports `windows/database.sql` into `campus_db`
4. Validates required tables and default admin seed
5. Opens `http://localhost/campus-app/login.php`

## 4) Login and smoke test
Default admin login:
- Username: `admin`
- Password: `Admin@12345!`

Verify pages:
- Login: `http://localhost/campus-app/login.php`
- Dashboard: `http://localhost/campus-app/dashboard.php`
- Students: `http://localhost/campus-app/students.php`
- Register: `http://localhost/campus-app/register.php`
- Search: `http://localhost/campus-app/search.php`

## 5) Stop services (optional)
```powershell
powershell -ExecutionPolicy Bypass -File .\windows\scripts\stop_wamp.ps1
```

## Troubleshooting
- `WAMP root not found`: install WAMP at `C:\wamp64`.
- `Service not found`: your WAMP service names differ; update names in `windows/scripts/launch_wamp.ps1` and `windows/scripts/stop_wamp.ps1`.
- `Run as Administrator` error: re-open PowerShell as Administrator.
- MySQL import failure:
  - Confirm MySQL service is running.
  - Confirm root password is blank for this local setup.
  - Confirm `database.sql` exists at `C:\wamp64\www\campus-app\database.sql`.
- Page shows DB connection error: check `windows/includes/config.php` uses:
  - host `127.0.0.1`
  - db `campus_db`
  - user `root`
  - pass empty string
