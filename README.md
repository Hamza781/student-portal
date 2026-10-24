# Student Portal (school project)

Simple student management site. Plain PHP (PDO), HTML, CSS, JavaScript.
Roles: admin, teacher, student.

## Requirements
Windows, XAMPP (Apache, PHP 8.2, MariaDB, phpMyAdmin). Apache DocumentRoot = D:/student-portal/public, port 8080.

## Setup
1. Start Apache and MySQL in XAMPP.
2. In phpMyAdmin, import database/schema.sql.
3. Edit database/create_db_user.sql (set a password), then import it.
4. Copy config/db.example.php to config/db.php and put the same password.
5. Run: C:\xampp\php\php.exe database\make_hashes.php
   Paste the printed hash into database/seed.sql (replace PASTE_HASH_HERE). Import seed.sql.
6. Open http://localhost:8080

## Fake test logins (password for all: Test@1234)
- Admin:    admin@school.test
- Teachers: teacher1@school.test, teacher2@school.test
- Students: student1@school.test ... student10@school.test
  (student1-5 are in Network 1A, student6-10 are in Network 1B)

## Security
Prepared statements (PDO), password_hash, output escaping, CSRF tokens,
server-side role checks, secrets outside public/, limited MySQL user.