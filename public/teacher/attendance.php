<?php
// Mark each student of the class present or absent for ONE lesson.
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('teacher');

$lessonId = read_int($_GET['lesson_id'] ?? null);

// Load the lesson ONLY IF it belongs to this teacher (t.teacher_id = ?).
// If a teacher changes the id in the URL to another teacher's lesson, he gets 404.
$lesson = $lessonId === null ? false : run(
    'SELECT l.id, l.lesson_date, l.time_slot, t.class_id, s.name AS subject, c.name AS class_name
       FROM lessons l
       JOIN teaching t ON t.id = l.teaching_id
       JOIN subjects s ON s.id = t.subject_id
       JOIN classes c  ON c.id = t.class_id
      WHERE l.id = ? AND t.teacher_id = ?',
    [$lessonId, $user['id']]
)->fetch();

if (!$lesson) {
    http_response_code(404);
    exit('Lesson not found.');
}

// The students of this lesson's class.
$students = run(
    "SELECT id, full_name FROM users WHERE role = 'student' AND class_id = ? ORDER BY full_name",
    [$lesson['class_id']]
)->fetchAll();

// Marks already saved: student_id => status.
$saved = [];
foreach (run('SELECT student_id, status FROM attendance WHERE lesson_id = ?', [$lesson['id']])->fetchAll() as $r) {
    $saved[$r['student_id']] = $r['status'];
}

$errors = [];
if (is_post()) {
    csrf_check();
    $input = $_POST['status'] ?? [];
    if (!is_array($input)) {
        $input = [];
    }

    // We loop over the students FROM THE DATABASE, not over the form.
    // So the teacher cannot add marks for students of other classes.
    $marks = [];
    foreach ($students as $s) {
        $st = $input[$s['id']] ?? '';
        if ($st !== 'present' && $st !== 'absent') {
            $errors[] = 'Choose present or absent for ' . $s['full_name'] . '.';
        } else {
            $marks[$s['id']] = $st;
        }
    }

    if (!$errors) {
        // Transaction = all marks are saved, or none.
        db()->beginTransaction();
        foreach ($marks as $studentId => $status) {
            // If the mark already exists, UPDATE it (ON DUPLICATE KEY UPDATE).
            run(
                'INSERT INTO attendance (lesson_id, student_id, status) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE status = VALUES(status)',
                [$lesson['id'], $studentId, $status]
            );
        }
        db()->commit();
        flash_set('success', 'Attendance saved.');
        redirect('/teacher/attendance.php?lesson_id=' . (int) $lesson['id']);
    }
}

page_header('Attendance', $user);
?>
<h2>Attendance</h2>
<p><?= e($lesson['subject']) ?> - <?= e($lesson['class_name']) ?> - <?= e($lesson['lesson_date']) ?> (<?= e($lesson['time_slot']) ?>)</p>
<?php show_errors($errors); ?>

<?php if (!$students): ?>
  <div class="card">There are no students in this class.</div>
<?php else: ?>
<form method="post" action="/teacher/attendance.php?lesson_id=<?= (int) $lesson['id'] ?>">
  <?= csrf_field() ?>
  <p>
    <button type="button" class="btn btn-light" data-set-all="present">All present</button>
    <button type="button" class="btn btn-light" data-set-all="absent">All absent</button>
  </p>
  <div class="table-wrap">
  <table>
    <tr><th>Student</th><th>Status</th></tr>
    <?php foreach ($students as $s):
        $current = $saved[$s['id']] ?? 'present';   // default: present ?>
    <tr>
      <td><?= e($s['full_name']) ?></td>
      <td>
        <label class="inline"><input type="radio" name="status[<?= (int) $s['id'] ?>]" value="present" <?= $current === 'present' ? 'checked' : '' ?>> Present</label>
        <label class="inline"><input type="radio" name="status[<?= (int) $s['id'] ?>]" value="absent"  <?= $current === 'absent'  ? 'checked' : '' ?>> Absent</label>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  </div>
  <button type="submit" class="btn">Save attendance</button>
</form>
<?php endif; ?>
<p><a href="/teacher/lessons.php">&larr; Back to lessons</a></p>
<?php page_footer(); ?>