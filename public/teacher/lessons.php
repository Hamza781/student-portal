<?php
// The teacher creates lessons (sessions) and opens them to mark attendance.
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('teacher');

// Allowed time slots. Anything else is refused.
$slots = ['08:00-09:30', '09:45-11:15', '11:30-13:00', '14:00-15:30', '15:45-17:15'];

// My own subject/class pairs. We also use this list to check the form.
$mine = run(
    'SELECT t.id, s.name AS subject, c.name AS class_name
       FROM teaching t
       JOIN subjects s ON s.id = t.subject_id
       JOIN classes c  ON c.id = t.class_id
      WHERE t.teacher_id = ?
      ORDER BY c.name, s.name',
    [$user['id']]
)->fetchAll();
$myIds = array_map(fn($r) => (int) $r['id'], $mine);

$errors = [];
if (is_post()) {
    csrf_check();
    $teachingId = read_int($_POST['teaching_id'] ?? null);
    $date       = post_str('lesson_date');
    $slot       = post_str('time_slot');

    // The teacher can only use HIS OWN assignments.
    if ($teachingId === null || !in_array($teachingId, $myIds, true)) {
        $errors[] = 'Please choose one of your subject/class pairs.';
    }
    if (!valid_date($date)) {
        $errors[] = 'Please enter a valid date.';
    }
    if (!in_array($slot, $slots, true)) {
        $errors[] = 'Please choose a time slot.';
    }

    if (!$errors) {
        try {
            run('INSERT INTO lessons (teaching_id, lesson_date, time_slot) VALUES (?, ?, ?)',
                [$teachingId, $date, $slot]);
            flash_set('success', 'Lesson created.');
            redirect('/teacher/lessons.php');
        } catch (PDOException $e) {
            $errors[] = 'This lesson already exists.';
        }
    }
}

$lessons = run(
    'SELECT l.id, l.lesson_date, l.time_slot, s.name AS subject, c.name AS class_name
       FROM lessons l
       JOIN teaching t ON t.id = l.teaching_id
       JOIN subjects s ON s.id = t.subject_id
       JOIN classes c  ON c.id = t.class_id
      WHERE t.teacher_id = ?
      ORDER BY l.lesson_date DESC, l.time_slot
      LIMIT 50',
    [$user['id']]
)->fetchAll();

page_header('My lessons', $user);
?>
<h2>My lessons</h2>
<div class="card">
  <h3>New lesson</h3>
  <?php show_errors($errors); ?>
  <form method="post" class="row-form">
    <?= csrf_field() ?>
    <div>
      <label for="teaching_id">Subject / class</label>
      <select id="teaching_id" name="teaching_id" required>
        <option value="">-- choose --</option>
        <?php foreach ($mine as $m): ?>
          <option value="<?= (int) $m['id'] ?>"><?= e($m['subject']) ?> - <?= e($m['class_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><label for="lesson_date">Date</label><input type="date" id="lesson_date" name="lesson_date" required></div>
    <div>
      <label for="time_slot">Time slot</label>
      <select id="time_slot" name="time_slot" required>
        <?php foreach ($slots as $s): ?><option value="<?= e($s) ?>"><?= e($s) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div><button type="submit" class="btn">Create</button></div>
  </form>
</div>

<div class="table-wrap">
<table>
  <tr><th>Date</th><th>Time</th><th>Subject</th><th>Class</th><th></th></tr>
  <?php foreach ($lessons as $l): ?>
  <tr>
    <td><?= e($l['lesson_date']) ?></td>
    <td><?= e($l['time_slot']) ?></td>
    <td><?= e($l['subject']) ?></td>
    <td><?= e($l['class_name']) ?></td>
    <td><a class="btn btn-small" href="/teacher/attendance.php?lesson_id=<?= (int) $l['id'] ?>">Mark attendance</a></td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php page_footer(); ?>