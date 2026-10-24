<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$me = require_role('admin');

// Delete a user (POST only).
if (is_post()) {
    csrf_check();
    $id = read_int($_POST['user_id'] ?? null);

    if ($id === null) {
        flash_set('error', 'Invalid user.');
    } elseif ($id === (int) $me['id']) {
        flash_set('error', 'You cannot delete your own account.');
    } else {
        run('DELETE FROM users WHERE id = ?', [$id]);
        flash_set('success', 'User deleted.');
    }
    redirect('/admin/users.php');
}

$users = run(
    'SELECT u.id, u.full_name, u.email, u.role, c.name AS class_name
       FROM users u
       LEFT JOIN classes c ON c.id = u.class_id
      ORDER BY u.role, u.full_name'
)->fetchAll();

page_header('Users', $me);
?>
<h2>Users</h2>
<p><a class="btn" href="/admin/user_create.php">+ Create user</a></p>
<div class="table-wrap">
<table>
  <tr><th>Name</th><th>Email</th><th>Role</th><th>Class</th><th></th></tr>
  <?php foreach ($users as $u): ?>
  <tr>
    <td><?= e($u['full_name']) ?></td>
    <td><?= e($u['email']) ?></td>
    <td><?= e($u['role']) ?></td>
    <td><?= e($u['class_name'] ?? '-') ?></td>
    <td>
      <form method="post" class="inline-form" data-confirm="Delete this user? This also deletes his grades and attendance.">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
        <button type="submit" class="btn btn-danger btn-small">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php page_footer(); ?>