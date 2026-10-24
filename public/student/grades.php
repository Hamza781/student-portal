<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('student');

$rows = run(
    'SELECT s.name AS subject, g.grade
       FROM grades g
       JOIN subjects s ON s.id = g.subject_id
      WHERE g.student_id = ?
      ORDER BY s.name',
    [$user['id']]
)->fetchAll();

page_header('My grades', $user);
?>
<h2>My grades</h2>
<div class="table-wrap">
<table>
  <tr><th>Subject</th><th>Grade (out of 100)</th><th>Result</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['subject']) ?></td>
    <td><?= (int) $r['grade'] ?></td>
    <td>
      <?php if ((int) $r['grade'] >= PASS_MARK): ?><span class="badge badge-ok">Pass</span>
      <?php else: ?><span class="badge badge-bad">Fail</span><?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="3">No grades yet.</td></tr><?php endif; ?>
</table>
</div>
<p class="muted">Pass mark: <?= PASS_MARK ?></p>
<?php page_footer(); ?>