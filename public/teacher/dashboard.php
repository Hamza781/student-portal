<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('teacher');

// Only MY data: the id comes from the session, not from the URL.
$nTeaching = (int) run('SELECT COUNT(*) FROM teaching WHERE teacher_id = ?', [$user['id']])->fetchColumn();
$nLessons  = (int) run(
    'SELECT COUNT(*) FROM lessons l JOIN teaching t ON t.id = l.teaching_id WHERE t.teacher_id = ?',
    [$user['id']]
)->fetchColumn();

page_header('Teacher dashboard', $user);
welcome_banner($user, 'Create lessons, mark attendance and enter grades.');
?>
<div class="cards">
  <div class="card"><h3>My subject/class pairs</h3><div class="big"><?= $nTeaching ?></div></div>
  <div class="card"><h3>My lessons</h3><div class="big"><?= $nLessons ?></div></div>
</div>
<div class="card">
  <a class="btn" href="/teacher/lessons.php">My lessons and attendance</a>
  <a class="btn" href="/teacher/grades.php">Enter grades</a>
</div>
<?php page_footer(); ?>