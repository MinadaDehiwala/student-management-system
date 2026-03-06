# Student Management System - macOS Setup

## Prerequisites
- Homebrew
- PHP 8.x (`php -v`)
- MySQL via Homebrew (`mysql --version`)

## Quick Start (Recommended)
From the project folder:
```bash
chmod +x scripts/*.sh
./scripts/dev_up.sh
```

This command will:
1. Start MySQL service
2. Try to ensure database/user exist with root (if available)
3. Import/apply schema + seed data (root path or app-user fallback)
4. Start the app at `http://localhost:8000`

Open:
- `http://localhost:8000/login.php`

## Manual Setup (Alternative)
### 1) Install MySQL via Homebrew
```bash
brew install mysql
brew services start mysql
```

### 2) Secure MySQL root account (one-time)
```bash
mysql_secure_installation
```

### 3) Optional root password env variable
```bash
export MYSQL_ROOT_PASSWORD='your-root-password'
```

### 4) Setup DB + user
```bash
chmod +x scripts/mac_mysql_setup.sh scripts/mac_import_sql.sh
./scripts/mac_mysql_setup.sh
```

### 5) Import schema
```bash
./scripts/mac_import_sql.sh
```

### 6) Run PHP app
```bash
php -S localhost:8000
```

## Stop Local Dev Server
```bash
./scripts/dev_down.sh
```

## Default Admin Login
- Username: `admin`
- Password: `Admin@12345!`
