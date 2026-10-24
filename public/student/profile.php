<?php
// The student edits HIS OWN file: name, phone, birth date.
require_once __DIR__ . '/../../src/bootstrap.php';
$user = require_role('student');

$errors = [];
// Values shown in the form. Start with the saved values.
$form = [
    'full_name'  => $user['full_name'],
    'phone'      => (string) $user['phone'],
    'birth_date' => (string) $user['birth_date'],
];

if (is_post()) {
    csrf_check();
    $form['full_name']  = post_str('full_name');
    $form['phone']      = post_str('phone');
    $form['birth_date'] = post_str('birth_date');

    if (mb_strlen($form['full_name']) < 2 || mb_strlen($form['full_name']) > 100) {
        $errors[] = 'Full name must be 2 to 100 characters.';
    }
    if ($form['phone'] !== '' && !preg_match('/^[0-9+ ]{6,20}$/', $form['phone'])) {
        $errors[] = 'Phone: use 6 to 20 digits, spaces or +.';
    }
    if ($form['birth_date'] !== '') {
        if (!valid_date($form['birth_date'])
            || $form['birth_date'] < '1900-01-01'
            || $form['birth_date'] > date('Y-m-d')) {
            $errors[] = 'Birth date is not valid.';
        }
    }

    if (!$errors) {
        // We update ONLY these 3 columns, and only the row of the logged-in user.
        // The student cannot change his role, email or class, even if he adds fields to the form.
        run(
            'UPDATE users SET full_name = ?, phone = ?, birth_date = ? WHERE id = ?',
            [
                $form['full_name'],
                $form['phone'] === '' ? null : $form['phone'],
                $form['birth_date'] === '' ? null : $form['birth_date'],
                $user['id'],
            ]
        );
        flash_set('success', 'Profile updated.');
        redirect('/student/profile.php');
    }
}

page_header('My profile', $user);
?>
<div class="card">
  <h2>My personal file</h2>
  <p class="muted">Email: <?= e($user['email']) ?> (cannot be changed here)</p>
  <?php show_errors($errors); ?>
  <form method="post" action="/student/profile.php">
    <?= csrf_field() ?>
    <label for="full_name">Full name</label>
    <input type="text" id="full_name" name="full_name" value="<?= e($form['full_name']) ?>" required>
    <label for="phone">Phone</label>
    <input type="text" id="phone" name="phone" value="<?= e($form['phone']) ?>">
    <label for="birth_date">Birth date</label>
    <input type="date" id="birth_date" name="birth_date" value="<?= e($form['birth_date']) ?>">
    <button type="submit" class="btn">Save</button>
  </form>
</div>
<?php page_footer(); ?>