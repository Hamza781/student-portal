<?php
// Every page loads this file FIRST.
// It sets up errors, security headers, the session, and loads our helper files.

// Development switch. Set to false before you show the site to other people.
const DEBUG = true;

ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(E_ALL);

// If something crashes: show details in DEBUG mode, a simple message otherwise.
set_exception_handler(function (Throwable $e) {
    error_log((string) $e);                       // saved in C:\xampp\apache\logs\error.log
    http_response_code(500);
    echo DEBUG ? htmlspecialchars($e->getMessage()) : 'Something went wrong.';
});

// Security headers (the browser obeys them).
header('X-Content-Type-Options: nosniff');                          // no guessing file types
header('X-Frame-Options: DENY');                                    // no clickjacking
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'"); // only our own CSS/JS

// Safer session cookie.
ini_set('session.use_strict_mode', '1');   // refuse session ids made by an attacker
session_set_cookie_params([
    'lifetime' => 0,          // cookie dies when the browser closes
    'path'     => '/',
    'httponly' => true,       // JavaScript cannot read the cookie (helps against XSS)
    'samesite' => 'Lax',      // helps against CSRF
]);
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/layout.php';