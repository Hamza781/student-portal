<?php
// ONE-TIME script. It makes a password hash for the fake users.
// Run it in a terminal (not in the browser):
//   C:\xampp\php\php.exe D:\student-portal\database\make_hashes.php
// If XAMPP is not in C:\xampp, change the path.
// Copy the long text it prints and paste it into seed.sql.

if (PHP_SAPI !== 'cli') {
    exit('Run this script from the terminal only.');
}

$fakePassword = 'Test@1234';   // password of ALL fake users
echo password_hash($fakePassword, PASSWORD_DEFAULT) . PHP_EOL;