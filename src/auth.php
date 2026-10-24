<?php
// Login, logout and role checks.

// Returns the logged-in user (array) or null.
// We read the user from the DATABASE every time, using only the id stored in the session.
// So the role always comes from the database, never from the browser.
function current_user(): ?array
{
    static $user = false;            // cache for this request
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (!empty($_SESSION['user_id'])) {
        $row = run(
            'SELECT id, full_name, email, role, phone, birth_date, class_id FROM users WHERE id = ?',
            [$_SESSION['user_id']]
        )->fetch();
        if ($row) {
            $user = $row;
        }
    }
    return $user;
}

// Call after the password is correct.
function login_user(int $userId): void
{
    session_regenerate_id(true);     // NEW session id: stops "session fixation"
    $_SESSION['user_id'] = $userId;
    $_SESSION['csrf']    = bin2hex(random_bytes(32));
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// PUT THIS AT THE TOP OF EVERY PROTECTED PAGE.
// Example: $user = require_role('admin');
// Not logged in -> login page. Wrong role -> 403 error.
function require_role(string ...$roles): array
{
    $user = current_user();
    if ($user === null) {
        redirect('/login.php');
    }
    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('403 Forbidden: you are not allowed to see this page.');
    }
    return $user;
}

// Where each role goes after login.
function home_for(string $role): string
{
    return match ($role) {
        'admin'   => '/admin/dashboard.php',
        'teacher' => '/teacher/dashboard.php',
        default   => '/student/dashboard.php',
    };
}