<?php
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('admin');

if (is_post()) {
    csrf_check();
    $action = post_str('action');

    if ($action === 'add') {
        $name = post_str('name');
        if (mb_strlen($name) < 1 || mb_strlen($name) > 50) {
            flash_set('error', 'Class name must be 1 to 50 characters.');
        } else {
            try {
                run('INSERT INTO classes (name) VALUES (?)', [$name]);
                flash_set('success', 'Class added.');
            } catch (PDOException $e) {
                flash_set('error', 'This class name already exists.');
            }
        }
    } elseif ($action === 'delete') {
        $id = read_int($_POST['class_id'] ?? null);
        try {
            run('DELETE FROM classes WHERE id = ?', [$id]);
            flash_set('success', 'Class deleted.');
        } catch (PDOException $e) {
            // The foreign key (RESTRICT) blocks deleting a class with students.
            flash_set('error', 'Cannot delete: students still belong to this class.');
        }
    }
    redirect('/admin/classes.php');
}

$classes = run(
    "SELECT c.id, c.name, COUNT(u.id) AS students
       FROM classes c
       LEFT JOIN users u ON u.class_id = c.id AND u.role = 'student'
      GROUP BY c.id, c.name
      ORDER BY c.name"
)->fetchAll();

page_header('Classes', $user);
?>
<h2>Classes</h2>
<div class="card">
  <form method="post" class="row-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div><label for="name">New class name</label><input type="text" id="name" name="name" required></div>
    <div><button type="submit" class="btn">Add class</button></div>
  </form>
</div>
<div class="table-wrap">
<table>
  <tr><th>Class</th><th>Students</th><th></th></tr>
  <?php foreach ($classes as $c): ?>
  <tr>
    <td><?= e($c['name']) ?></td>
    <td><?= (int) $c['students'] ?></td>
    <td>
      <form method="post" class="inline-form" data-confirm="Delete this class?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="class_id" value="<?= (int) $c['id'] ?>">
        <button type="submit" class="btn btn-danger btn-small">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
</div>
<?php page_footer(); ?>