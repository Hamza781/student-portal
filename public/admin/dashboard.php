<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('admin');          // only admins pass this line

// Count users by role.
$counts = ['admin' => 0, 'teacher' => 0, 'student' => 0];
foreach (run('SELECT role, COUNT(*) AS n FROM users GROUP BY role')->fetchAll() as $r) {
    $counts[$r['role']] = (int) $r['n'];
}
$nClasses  = (int) run('SELECT COUNT(*) FROM classes')->fetchColumn();
$nSubjects = (int) run('SELECT COUNT(*) FROM subjects')->fetchColumn();

page_header('Admin dashboard', $user);
welcome_banner($user, 'Manage users, classes and subjects.');
?>
<div class="cards">
  <div class="card"><h3>Students</h3><div class="big"><?= $counts['student'] ?></div></div>
  <div class="card"><h3>Teachers</h3><div class="big"><?= $counts['teacher'] ?></div></div>
  <div class="card"><h3>Admins</h3><div class="big"><?= $counts['admin'] ?></div></div>
  <div class="card"><h3>Classes</h3><div class="big"><?= $nClasses ?></div></div>
  <div class="card"><h3>Subjects</h3><div class="big"><?= $nSubjects ?></div></div>
</div>
<div class="card">
  <h3>Quick links</h3>
  <a class="btn" href="/admin/users.php">Users</a>
  <a class="btn" href="/admin/user_create.php">Create user</a>
  <a class="btn" href="/admin/teaching.php">Teaching assignments</a>
</div>
<?php page_footer(); ?>