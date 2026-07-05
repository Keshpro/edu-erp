<?php
require_once __DIR__ . '/../config/db.php';
$uid = $_SESSION['user_id'] ?? 0;
$uname = $_SESSION['user_name'] ?? 'User';
$urole = ucfirst($_SESSION['role'] ?? 'student');
$initials = strtoupper(substr($uname,0,1));
$parts = explode(' ', $uname);
if (count($parts) > 1) $initials .= strtoupper(substr($parts[1],0,1));
$notifCount = 0;
try {
    $st = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
    $st->execute([$uid]);
    $notifCount = (int)$st->fetchColumn();
} catch (Exception $e) {}
?>
<header class="navbar">
  <!-- Mobile Toggle Button -->
  <button class="mobile-nav-toggle" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
  </button>

  <div class="search">
    <i class="fa-solid fa-magnifying-glass"></i>
    <input type="text" placeholder="Search across systems...">
  </div>
  <div class="navbar-right">
    <div class="bell" title="Notifications">
      <i class="fa-regular fa-bell"></i>
      <?php if ($notifCount > 0): ?>
        <span class="badge"><?= $notifCount ?></span>
      <?php endif; ?>
    </div>
    <div class="user">
      <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      <div class="meta">
        <div class="name"><?= htmlspecialchars($uname) ?></div>
        <div class="role"><?= htmlspecialchars($urole) ?></div>
      </div>
    </div>
  </div>
</header>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar.style.getPropertyValue('display') === 'block') {
        sidebar.style.setProperty('display', 'none', 'important');
    } else {
        sidebar.style.setProperty('display', 'block', 'important');
    }
}
</script>
<div class="content">