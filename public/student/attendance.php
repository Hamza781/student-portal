<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('student');

// "WHERE a.student_id = ?" with MY id: I only see MY attendance.
$rows = run(
    'SELECT l.lesson_date, l.time_slot, s.name AS subject, a.status
       FROM attendance a
       JOIN lessons l  ON l.id = a.lesson_id
       JOIN teaching t ON t.id = l.teaching_id
       JOIN subjects s ON s.id = t.subject_id
      WHERE a.student_id = ?
      ORDER BY l.lesson_date DESC, l.time_slot',
    [$user['id']]
)->fetchAll();

page_header('My attendance', $user);
?>
<h2>My attendance</h2>
<div class="table-wrap">
<table>
  <tr><th>Date</th><th>Time</th><th>Subject</th><th>Status</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['lesson_date']) ?></td>
    <td><?= e($r['time_slot']) ?></td>
    <td><?= e($r['subject']) ?></td>
    <td><span class="badge <?= $r['status'] === 'present' ? 'badge-ok' : 'badge-bad' ?>"><?= e($r['status']) ?></span></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="4">No attendance yet.</td></tr><?php endif; ?>
</table>
</div>
<?php page_footer(); ?>