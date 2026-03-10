# Student Management System - macOS Setup

This guide runs the app from the `mac/` package using Homebrew MySQL.

## Prerequisites
- Homebrew
- PHP 8.x (`php -v`)
- MySQL via Homebrew (`mysql --version`)
- MySQL root account with blank password (local dev mode)

## Quick Start
From repository root:

```bash
cd mac
chmod +x scripts/*.sh
./scripts/dev_up.sh
```

Open:
- `http://localhost:8000/login.php`

Default admin:
- Username: `admin`
- Password: `Admin@12345!`

## Stop local PHP server
From `mac/`:

```bash
./scripts/dev_down.sh
```
