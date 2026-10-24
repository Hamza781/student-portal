<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$me = require_role('admin');

$errors  = [];
$name    = '';
$email   = '';
$role    = 'student';
$classId = null;

$classes = run('SELECT id, name FROM classes ORDER BY name')->fetchAll();

if (is_post()) {
    csrf_check();
    $name    = post_str('full_name');
    $email   = post_str('email');
    $role    = post_str('role');
    $pw      = is_string($_POST['password'] ?? null)  ? $_POST['password']  : '';
    $pw2     = is_string($_POST['password2'] ?? null) ? $_POST['password2'] : '';
    $classId = read_int($_POST['class_id'] ?? null);

    $errors = validate_user_fields($name, $email, $pw, $pw2);

    // Here the role comes from a form. This is OK ONLY because this page is
    // admin-only (require_role above). We also allow only 3 known values.
    if (!in_array($role, ['admin', 'teacher', 'student'], true)) {
        $errors[] = 'Invalid role.';
    }
    if ($role === 'student') {
        if ($classId === null || !class_id_exists($classId)) {
            $errors[] = 'A student needs a class.';
        }
    } else {
        $classId = null;          // admins and teachers have no class
    }
    if (!$errors && email_taken($email)) {
        $errors[] = 'This email is already used.';
    }

    if (!$errors) {
        run(
            'INSERT INTO users (full_name, email, password_hash, role, class_id) VALUES (?, ?, ?, ?, ?)',
            [$name, $email, password_hash($pw, PASSWORD_DEFAULT), $role, $classId]
        );
        flash_set('success', 'User created.');
        redirect('/admin/users.php');
    }
}

page_header('Create user', $me);
?>
<div class="card">
  <h2>Create user</h2>
  <?php show_errors($errors); ?>
  <form method="post" action="/admin/user_create.php">
    <?= csrf_field() ?>
    <label for="full_name">Full name</label>
    <input type="text" id="full_name" name="full_name" value="<?= e($name) ?>" required>
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
    <label for="role">Role</label>
    <select id="role" name="role">
      <?php foreach (['student', 'teacher', 'admin'] as $r): ?>
        <option value="<?= e($r) ?>" <?= $role === $r ? 'selected' : '' ?>><?= e($r) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="class_id">Class (students only)</label>
    <select id="class_id" name="class_id">
      <option value="">-- none --</option>
      <?php foreach ($classes as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= $classId === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="password">Password (8 characters or more)</label>
    <input type="password" id="password" name="password" required>
    <label for="password2">Repeat password</label>
    <input type="password" id="password2" name="password2" required>
    <button type="submit" class="btn">Create</button>
  </form>
</div>
<?php page_footer(); ?>