<?php
$role = $_SESSION['role'] ?? 'student';
$current = basename($_SERVER['SCRIPT_NAME']);
$base = '/edu-erp';
$nav = [];
if ($role === 'admin') {
    $nav = [
        ['Dashboard',      'admin/dashboard.php',  'fa-gauge-high'],
        ['Students',       'admin/students.php',   'fa-user-graduate'],
        ['Courses',        'admin/courses.php',    'fa-book'],
        ['Exams',          'admin/exams.php',      'fa-file-pen'],
        ['Attendance',     'admin/attendance.php', 'fa-calendar-check'],
    ];
} else {
    $nav = [
        ['Dashboard',      'student/dashboard.php',     'fa-gauge-high'],
        ['Registration',   'student/registration.php',  'fa-clipboard-list'],
        ['Profile',        'student/profile.php',       'fa-user'],
        ['Notifications',  'student/notifications.php', 'fa-bell'],
    ];
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <img src="<?= $base ?>/assets/images/logo.svg" alt="logo">
    <span>EduCore ERP</span>
  </div>
  <div class="sidebar-section">Main</div>
  <ul class="nav">
    <?php foreach ($nav as $item): 
        $active = (basename($item[1]) === $current) ? 'active' : '';
    ?>
      <li>
        <a href="<?= $base . '/' . $item[1] ?>" class="<?= $active ?>">
          <i class="fa-solid <?= $item[2] ?>"></i>
          <span><?= $item[0] ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <div class="sidebar-section">Account</div>
  <ul class="nav">
    <li>
      <a href="<?= $base ?>/auth/logout.php">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </a>
    </li>
  </ul>
</aside>
<div class="main">
