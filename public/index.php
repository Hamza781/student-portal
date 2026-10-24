<?php
// Home page: send the visitor to the right place.
require_once __DIR__ . '/../src/bootstrap.php';

$user = current_user();
redirect($user ? home_for($user['role']) : '/login.php');