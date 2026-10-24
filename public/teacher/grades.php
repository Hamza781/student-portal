<?php
// Enter grades (0-100) for the students of one subject/class pair.
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('teacher');

// My subject/class pairs. This list is also our ownership check.
$mine = run(
    'SELECT t.id, t.subject_id, t.class_id, s.name AS subject, c.name AS class_name
       FROM teaching t
       JOIN subjects s ON s.id = t.subject_id
       JOIN classes c  ON c.id = t.class_id
      WHERE t.teacher_id = ?
      ORDER BY c.name, s.name',
    [$user['id']]
)->fetchAll();

$tid     = read_int($_GET['teaching_id'] ?? null);
$current = null;
foreach ($mine as $m) {
    if ($tid !== null && (int) $m['id'] === $tid) {
        $current = $m;
    }
}
if ($tid !== null && $current === null) {
    http_response_code(404);              // not one of MY pairs
    exit('Not found.');
}

$students = [];
$errors   = [];

if ($current) {
    // Students of the class, with their current grade (LEFT JOIN: grade can be empty).
    $students = run(
        "SELECT u.id, u.full_name, g.grade
           FROM users u
           LEFT JOIN grades g ON g.student_id = u.id AND g.subject_id = ?
          WHERE u.role = 'student' AND u.class_id = ?
          ORDER BY u.full_name",
        [$current['subject_id'], $current['class_id']]
    )->fetchAll();

    if (is_post()) {
        csrf_check();
        $input = $_POST['grade'] ?? [];
        if (!is_array($input)) {
            $input = [];
        }

        $toSave = [];
        foreach ($students as $s) {                       // only real students of this class
            $raw = $input[$s['id']] ?? '';
            $raw = is_string($raw) ? trim($raw) : '';
            if ($raw === '') {
                continue;                                  // empty box = no change
            }
            // Must be a whole number from 0 to 100.
            if (!ctype_digit($raw) || strlen($raw) > 3 || (int) $raw > 100) {
                $errors[] = 'Grade for ' . $s['full_name'] . ' must be a whole number from 0 to 100.';
            } else {
                $toSave[$s['id']] = (int) $raw;
            }
        }

        if (!$errors) {
            db()->beginTransaction();
            foreach ($toSave as $studentId => $grade) {
                // One grade per student per subject: insert, or update if it exists.
                run(
                    'INSERT INTO grades (student_id, subject_id, grade, graded_by) VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE grade = VALUES(grade), graded_by = VALUES(graded_by)',
                    [$studentId, $current['subject_id'], $grade, $user['id']]
                );
            }
            db()->commit();
            flash_set('success', 'Grades saved.');
            redirect('/teacher/grades.php?teaching_id=' . (int) $current['id']);
        }
    }
}

page_header('Grades', $user);
?>
<h2>Grades</h2>
<div class="card">
  <h3>Choose subject / class</h3>
  <?php foreach ($mine as $m): ?>
    <a class="btn btn-light btn-small" href="/teacher/grades.php?teaching_id=<?= (int) $m['id'] ?>"><?= e($m['subject']) ?> - <?= e($m['class_name']) ?></a>
  <?php endforeach; ?>
  <?php if (!$mine): ?><p>You have no subjects yet. Ask the admin.</p><?php endif; ?>
</div>

<?php if ($current): ?>
  <h3><?= e($current['subject']) ?> - <?= e($current['class_name']) ?></h3>
  <?php show_errors($errors); ?>
  <form method="post" action="/teacher/grades.php?teaching_id=<?= (int) $current['id'] ?>">
    <?= csrf_field() ?>
    <div class="table-wrap">
    <table>
      <tr><th>Student</th><th>Grade (0-100)</th><th>Result</th></tr>
      <?php foreach ($students as $s): ?>
      <tr>
        <td><?= e($s['full_name']) ?></td>
        <td><input type="number" class="grade-input" min="0" max="100" name="grade[<?= (int) $s['id'] ?>]" value="<?= e($s['grade'] === null ? '' : (string) $s['grade']) ?>"></td>
        <td>
          <?php if ($s['grade'] === null): ?>-
          <?php elseif ((int) $s['grade'] >= PASS_MARK): ?><span class="badge badge-ok">Pass</span>
          <?php else: ?><span class="badge badge-bad">Fail</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    </div>
    <p class="muted">Pass mark: <?= PASS_MARK ?>. Leave a box empty to keep it unchanged.</p>
    <button type="submit" class="btn">Save grades</button>
  </form>
<?php endif; ?>
<?php page_footer(); ?>