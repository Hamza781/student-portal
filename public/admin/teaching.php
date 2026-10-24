<?php
// Admin decides: "teacher X teaches subject Y to class Z".
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('admin');

if (is_post()) {
    csrf_check();
    $action = post_str('action');

    if ($action === 'add') {
        $teacherId = read_int($_POST['teacher_id'] ?? null);
        $subjectId = read_int($_POST['subject_id'] ?? null);
        $classId   = read_int($_POST['class_id'] ?? null);

        // The chosen user must really be a teacher.
        $isTeacher = $teacherId !== null &&
            run("SELECT id FROM users WHERE id = ? AND role = 'teacher'", [$teacherId])->fetch();

        if (!$isTeacher || $subjectId === null || $classId === null) {
            flash_set('error', 'Please choose a teacher, a subject and a class.');
        } else {
            try {
                run('INSERT INTO teaching (teacher_id, subject_id, class_id) VALUES (?, ?, ?)',
                    [$teacherId, $subjectId, $classId]);
                flash_set('success', 'Assignment added.');
            } catch (PDOException $e) {
                // Duplicate row, or a subject/class id that does not exist.
                flash_set('error', 'This assignment already exists or is not valid.');
            }
        }
    } elseif ($action === 'delete') {
        run('DELETE FROM teaching WHERE id = ?', [read_int($_POST['teaching_id'] ?? null)]);
        flash_set('success', 'Assignment deleted.');
    }
    redirect('/admin/teaching.php');
}

$teachers = run("SELECT id, full_name FROM users WHERE role = 'teacher' ORDER BY full_name")->fetchAll();
$subjects = run('SELECT id, name FROM subjects ORDER BY name')->fetchAll();
$classes  = run('SELECT id, name FROM classes ORDER BY name')->fetchAll();
$rows = run(
    'SELECT t.id, u.full_name AS teacher, s.name AS subject, c.name AS class_name
       FROM teaching t
       JOIN users u    ON u.id = t.teacher_id
       JOIN subjects s ON s.id = t.subject_id
       JOIN classes c  ON c.id = t.class_id
      ORDER BY u.full_name, c.name, s.name'
)->fetchAll();

page_header('Teaching', $user);
?>
<h2>Teaching assignments</h2>
<div class="card">
  <form method="post" class="row-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div>
      <label for="teacher_id">Teacher</label>
      <select id="teacher_id" name="teacher_id" required>
        <option value="">-- choose --</option>
        <?php foreach ($teachers as $t): ?><option value="<?= (int) $t['id'] ?>"><?= e($t['full_name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="subject_id">Subject</label>
      <select id="subject_id" name="subject_id" required>
        <option value="">-- choose --</option>
        <?php foreach ($subjects as $s): ?><option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="class_id">Class</label>
      <select id="class_id" name="class_id" required>
        <option value="">-- choose --</option>
        <?php foreach ($classes as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div><button type="submit" class="btn">Add</button></div>
  </form>
</div>
<div class="table-wrap">
<table>
  <tr><th>Teacher</th><th>Subject</th><th>Class</th><th></th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= e($r['teacher']) ?></td>
    <td><?= e($r['subject']) ?></td>
    <td><?= e($r['class_name']) ?></td>
    <td>
      <form method="post" class="inline-form" data-confirm="Delete this assignment? Its lessons will be deleted too.">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="teaching_id" value="<?= (int) $r['id'] ?>">
        <button type="submit" class="btn btn-danger btn-small">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php page_footer(); ?>