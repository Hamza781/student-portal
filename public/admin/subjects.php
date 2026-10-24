<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('admin');

if (is_post()) {
    csrf_check();
    $action = post_str('action');

    if ($action === 'add') {
        $name = post_str('name');
        if (mb_strlen($name) < 1 || mb_strlen($name) > 80) {
            flash_set('error', 'Subject name must be 1 to 80 characters.');
        } else {
            try {
                run('INSERT INTO subjects (name) VALUES (?)', [$name]);
                flash_set('success', 'Subject added.');
            } catch (PDOException $e) {
                flash_set('error', 'This subject already exists.');
            }
        }
    } elseif ($action === 'delete') {
        $id = read_int($_POST['subject_id'] ?? null);
        run('DELETE FROM subjects WHERE id = ?', [$id]);   // also deletes its grades (CASCADE)
        flash_set('success', 'Subject deleted.');
    }
    redirect('/admin/subjects.php');
}

$subjects = run('SELECT id, name FROM subjects ORDER BY name')->fetchAll();

page_header('Subjects', $user);
?>
<h2>Subjects</h2>
<div class="card">
  <form method="post" class="row-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div><label for="name">New subject name</label><input type="text" id="name" name="name" required></div>
    <div><button type="submit" class="btn">Add subject</button></div>
  </form>
</div>
<div class="table-wrap">
<table>
  <tr><th>Subject</th><th></th></tr>
  <?php foreach ($subjects as $s): ?>
  <tr>
    <td><?= e($s['name']) ?></td>
    <td>
      <form method="post" class="inline-form" data-confirm="Delete this subject? All its grades will be deleted too.">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="subject_id" value="<?= (int) $s['id'] ?>">
        <button type="submit" class="btn btn-danger btn-small">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php page_footer(); ?>