<?php
// Shared checks used by "register" (public) and "create user" (admin).

// Check the common fields. Returns a list of error messages (empty list = OK).
function validate_user_fields(string $name, string $email, string $pw, string $pw2): array
{
    $errors = [];
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = 'Full name must be 2 to 100 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $errors[] = 'Email is not valid.';
    }
    // bcrypt only uses the first 72 bytes, so we limit the length to 72.
    if (strlen($pw) < 8 || strlen($pw) > 72) {
        $errors[] = 'Password must be 8 to 72 characters.';
    }
    if ($pw !== $pw2) {
        $errors[] = 'Passwords do not match.';
    }
    return $errors;
}

function email_taken(string $email): bool
{
    return (bool) run('SELECT id FROM users WHERE email = ?', [$email])->fetch();
}

function class_id_exists(int $id): bool
{
    return (bool) run('SELECT id FROM classes WHERE id = ?', [$id])->fetch();
}