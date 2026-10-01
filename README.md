# Roll Book: Student Information System

A PHP and MySQL web app for storing and managing student records: profiles with photos, guardians, classes, and daily attendance.

## Features
- Staff login (hashed passwords, CSRF protection, prepared statements)
- Students: add, edit, delete, photo upload, search, filters, pagination, CSV export
- Classes: create, edit, delete, see student counts
- Attendance: mark present / late / absent per class and day
- Dashboard with totals, today's attendance and students per class
- Responsive layout with keyboard focus styles and reduced-motion support

## Requirements
PHP 8.0+ (with PDO MySQL and fileinfo) and MySQL 5.7+ / MariaDB 10.3+.

## Setup
1. Clone the repo into your web root (e.g. `htdocs/student-info-system` for XAMPP).
2. Import the database: `mysql -u root -p < database.sql` (or use phpMyAdmin's Import tab).
3. Edit `config.php` (copy of `config.example.php`) with your database user and password.
4. Make sure the `uploads/` folder is writable by the web server.
5. Open `http://localhost/student-info-system/` and sign in with **admin / Admin@123**. The account is created on first visit. Change the password afterwards.

## Structure
```
database.sql        tables and sample classes
config.example.php  database settings (config.php is git-ignored)
includes/           bootstrap (DB, helpers, CSRF), header, footer
assets/             css and js
uploads/            student photos (git-ignored)
*.php               pages: login, dashboard, students, classes, attendance
```

## Push to GitHub
```
git init
git add .
git commit -m "Initial commit: student information system"
git branch -M main
git remote add origin https://github.com/<your-user>/<repo>.git
git push -u origin main
```
`config.php` and uploaded photos are excluded by `.gitignore`, so credentials stay private.
