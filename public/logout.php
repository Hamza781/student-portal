<?php
// Logout. Only works with POST and a valid token.
require_once __DIR__ . '/../src/bootstrap.php';

if (!is_post()) {
    redirect('/');
}
csrf_check();
logout_user();
redirect('/login.php');