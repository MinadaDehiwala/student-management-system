# Student Management System

A modern, responsive student management web app built with **PHP 8.x**, **MySQL/MariaDB**, **HTML5/CSS3**, and lightweight **vanilla JavaScript**.

## Highlights
- Clean, bold UI with animations and responsive layout
- Secure admin authentication with session handling + CSRF protection
- Student CRUD: register, edit, delete, and quick NIC search
- Full student directory with filtering, sorting, and pagination
- Insights dashboard:
  - total students
  - new registrations in last 7 days
  - course/gender distribution
  - recent registrations + activity feed
- Activity logging for `LOGIN`, `CREATE`, `UPDATE`, `DELETE`
- Works on macOS local stack and WAMP (Windows)

## Tech Stack
- PHP 8.x (no framework)
- MySQL/MariaDB
- HTML5 + CSS3
- Vanilla JS (small UI enhancements)

## Project Structure
```text
campus-app/
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── includes/
│   ├── activity.php
│   ├── auth.php
│   ├── config.php
│   ├── constants.php
│   ├── csrf.php
│   ├── footer.php
│   └── header.php
├── scripts/
│   ├── dev_up.sh
│   ├── dev_down.sh
│   ├── mac_mysql_setup.sh
│   ├── mac_import_sql.sh
│   ├── mac_apply_schema.sh
│   └── schema_updates.sql
├── dashboard.php
├── database.sql
├── login.php
├── register.php
├── students.php
├── search.php
├── edit.php
├── delete.php
└── logout.php
```

## Quick Start (macOS)
From project root:

```bash
chmod +x scripts/*.sh
./scripts/dev_up.sh
```

Open:
- `http://localhost:8000/login.php`

Default admin credentials:
- Username: `admin`
- Password: `Admin@12345!`

Stop the local PHP server:

```bash
./scripts/dev_down.sh
```

## WAMP Setup (Windows)
Follow:
- `README_WAMP_SETUP.md`

Main URL:
- `http://localhost/campus-app/login.php`

## Database Notes
- Main schema: `database.sql`
- Includes:
  - `students` table (with `updated_at`)
  - `admins` table
  - `student_activity` table
  - performance indexes for dashboard/directory queries

## Security
- Passwords stored as hashes (`password_hash`)
- CSRF token checks on state-changing requests
- Prepared statements used for DB operations
- Session-based access control for admin pages


## License
This project is for educational purposes.
