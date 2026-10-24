<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('student');

// All queries use MY id from the session. The student cannot choose another id.
$att = run(
    "SELECT COUNT(*) AS total, SUM(status = 'present') AS present FROM attendance WHERE student_id = ?",
    [$user['id']]
)->fetch();
$total   = (int) $att['total'];
$present = (int) $att['present'];
$rate    = $total > 0 ? round($present * 100 / $total) : null;

$g = run('SELECT AVG(grade) AS avg_grade, COUNT(*) AS n FROM grades WHERE student_id = ?', [$user['id']])->fetch();

$className = $user['class_id'] === null ? null
    : run('SELECT name FROM classes WHERE id = ?', [$user['class_id']])->fetchColumn();

page_header('Student dashboard', $user);
welcome_banner($user, 'Class: ' . ($className ?: 'none'));
?>
<div class="cards">
  <div class="card"><h3>Attendance rate</h3><div class="big"><?= $rate === null ? '-' : $rate . '%' ?></div>
    <div class="muted"><?= $present ?> present out of <?= $total ?> lessons</div></div>
  <div class="card"><h3>Average grade</h3><div class="big"><?= $g['n'] > 0 ? e((string) round((float) $g['avg_grade'], 1)) : '-' ?></div>
    <div class="muted"><?= (int) $g['n'] ?> graded subjects</div></div>
</div>
<div class="card">
  <a class="btn" href="/student/attendance.php">My attendance</a>
  <a class="btn" href="/student/grades.php">My grades</a>
  <a class="btn" href="/student/profile.php">My profile</a>
</div>
<?php page_footer(); ?>