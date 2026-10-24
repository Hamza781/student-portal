<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Already logged in? Go to your dashboard.
if ($u = current_user()) {
    redirect(home_for($u['role']));
}

$error = '';
$email = '';

if (is_post()) {
    csrf_check();
    $email    = post_str('email');
    $password = $_POST['password'] ?? '';        // do not trim passwords

    if ($email === '' || !is_string($password) || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        // Prepared statement: the email is a value, not part of the SQL.
        $row = run('SELECT id, password_hash, role FROM users WHERE email = ?', [$email])->fetch();

        // password_verify compares the typed password with the stored hash.
        if ($row && password_verify($password, $row['password_hash'])) {
            login_user((int) $row['id']);
            redirect(home_for($row['role']));
        }
        // Same message for "no such email" and "wrong password". Do not help attackers.
        $error = 'Wrong email or password.';
    }
}

page_header('Login');
?>
<div class="card auth-box">
  <h2>Login</h2>
  <?php show_errors($error ? [$error] : []); ?>
  <form method="post" action="/login.php">
    <?= csrf_field() ?>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <button type="submit" class="btn">Log in</button>
  </form>
  <p class="muted">No account? <a href="/register.php">Register as a student</a></p>
</div>
<?php page_footer(); ?>