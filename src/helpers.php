<?php
// Small helper functions used everywhere.

const PASS_MARK = 50;   // a grade of 50 or more = pass

// Escape text before printing it in HTML. Stops XSS.
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Go to another page and stop the script.
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Read a text value from a form. Always returns a trimmed string.
function post_str(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

// Turn a value into an int, or null if it is not a whole number.
function read_int(mixed $value): ?int
{
    $r = filter_var($value, FILTER_VALIDATE_INT);
    return $r === false ? null : $r;
}

// Is this text a real date like 2026-10-21 ?
function valid_date(string $d): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

// Flash message: shown once on the next page.
function flash_set(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];   // type: success or error
}

function flash_get(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// CSRF protection: every form has a secret token. A fake form on another site cannot know it.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// Call this at the start of every POST. Stops the request if the token is wrong.
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(403);
        exit('Invalid form token. Go back, reload the page and try again.');
    }
}