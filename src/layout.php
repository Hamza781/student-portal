<?php
// Draws the top and bottom of every page, so we do not repeat the HTML.

// Top of the page: <head>, dark blue header, menu, and the flash message.
function page_header(string $title, ?array $user = null): void
{
    // The menu depends on the role.
    $menus = [
        'admin' => [
            '/admin/dashboard.php' => 'Dashboard',
            '/admin/users.php'     => 'Users',
            '/admin/classes.php'   => 'Classes',
            '/admin/subjects.php'  => 'Subjects',
            '/admin/teaching.php'  => 'Teaching',
        ],
        'teacher' => [
            '/teacher/dashboard.php' => 'Dashboard',
            '/teacher/lessons.php'   => 'My lessons',
            '/teacher/grades.php'    => 'Grades',
        ],
        'student' => [
            '/student/dashboard.php'  => 'Dashboard',
            '/student/attendance.php' => 'My attendance',
            '/student/grades.php'     => 'My grades',
            '/student/profile.php'    => 'My profile',
        ],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> - Student Portal</title>
  <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="/">Student Portal</a>
    <?php if ($user): ?>
      <nav>
        <?php foreach ($menus[$user['role']] as $url => $label): ?>
          <a href="<?= e($url) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
      <!-- Logout is a POST form with a CSRF token, so another site cannot log you out. -->
      <form method="post" action="/logout.php" class="logout-form">
        <?= csrf_field() ?>
        <span class="who"><?= e($user['full_name']) ?> (<?= e($user['role']) ?>)</span>
        <button type="submit" class="btn btn-light">Logout</button>
      </form>
    <?php endif; ?>
  </div>
</header>
<main class="container">
<?php if ($flash = flash_get()): ?>
  <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
<?php
}

// Bottom of the page.
function page_footer(): void
{
    ?>
</main>
<footer class="site-footer"><div class="container">School project - Student Portal</div></footer>
<script src="/js/app.js" defer></script>
</body>
</html>
<?php
}

// Big blue welcome banner for dashboards.
function welcome_banner(array $user, string $text): void
{
    ?>
<section class="banner">
  <h1>Welcome, <?= e($user['full_name']) ?></h1>
  <p><?= e($text) ?></p>
</section>
<?php
}

// Shows a list of error messages in a red box.
function show_errors(array $errors): void
{
    if (!$errors) {
        return;
    }
    echo '<div class="flash flash-error"><ul>';
    foreach ($errors as $err) {
        echo '<li>' . e($err) . '</li>';
    }
    echo '</ul></div>';
}