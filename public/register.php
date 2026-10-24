<?php
// Public registration. It can ONLY create a student.
require_once __DIR__ . '/../src/bootstrap.php';

if ($u = current_user()) {
    redirect(home_for($u['role']));
}

$errors  = [];
$name    = '';
$email   = '';
$classId = null;

$classes = run('SELECT id, name FROM classes ORDER BY name')->fetchAll();

if (is_post()) {
    csrf_check();
    $name    = post_str('full_name');
    $email   = post_str('email');
    $pw      = is_string($_POST['password'] ?? null)  ? $_POST['password']  : '';
    $pw2     = is_string($_POST['password2'] ?? null) ? $_POST['password2'] : '';
    $classId = read_int($_POST['class_id'] ?? null);

    // Server-side validation.
    $errors = validate_user_fields($name, $email, $pw, $pw2);
    if ($classId === null || !class_id_exists($classId)) {
        $errors[] = 'Please choose a class.';
    }
    if (!$errors && email_taken($email)) {
        $errors[] = 'This email is already used.';
    }

    if (!$errors) {
        // The role 'student' is written HERE in the code. It is NOT read from the form.
        run(
            "INSERT INTO users (full_name, email, password_hash, role, class_id)
             VALUES (?, ?, ?, 'student', ?)",
            [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $classId]
        );
        flash_set('success', 'Account created. You can log in now.');
        redirect('/login.php');
    }
}

page_header('Register');
?>
<div class="card auth-box">
  <h2>Student registration</h2>
  <?php show_errors($errors); ?>
  <form method="post" action="/register.php">
    <?= csrf_field() ?>
    <label for="full_name">Full name</label>
    <input type="text" id="full_name" name="full_name" value="<?= e($name) ?>" required>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
    <label for="class_id">Class</label>
    <select id="class_id" name="class_id" required>
      <option value="">-- choose --</option>
      <?php foreach ($classes as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="password">Password (8 characters or more)</label>
    <input type="password" id="password" name="password" required>
    <label for="password2">Repeat password</label>
    <input type="password" id="password2" name="password2" required>
    <button type="submit" class="btn">Create account</button>
  </form>
  <p class="muted">Already registered? <a href="/login.php">Log in</a></p>
</div>
<?php page_footer(); ?>