# Student Management System - WAMP Setup

## 1) Copy project into WAMP web root
Copy `campus-app` to:
- `C:\wamp64\www\campus-app`

## 2) Start WAMP services
Start WAMP and verify both services are green:
- Apache: running
- MySQL/MariaDB: running

## 3) Create database and import schema
In phpMyAdmin:
1. Create database `campus_db` (utf8mb4 collation preferred).
2. Import `database.sql` from the project root.

Imported schema includes:
- `students` (with `updated_at`)
- `admins`
- `student_activity`
- Indexes for dashboard and directory queries

## 4) Create/update DB credentials
Default app credentials are in `includes/config.php`:
- Host: `127.0.0.1`
- DB: `campus_db`
- User: `campus_user`
- Pass: `Ga$9vL!2QxR#8tPm`

If your WAMP MySQL user/password differ, update only `includes/config.php`.

## 5) Run in browser
Open:
- `http://localhost/campus-app/login.php`

## 6) Validate key pages
After login, verify:
- Dashboard: `http://localhost/campus-app/dashboard.php`
- Student Directory: `http://localhost/campus-app/students.php`
- Register: `http://localhost/campus-app/register.php`
- Quick Search: `http://localhost/campus-app/search.php`

## Default Admin Login
- Username: `admin`
- Password: `Admin@12345!`
