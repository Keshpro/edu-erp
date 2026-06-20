<?php
require_once __DIR__ . '/../config/auth.php';
require_role('student');
require_once __DIR__ . '/../config/db.php';

if (isset($_GET['mark_read'])) {
    $st = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
    $st->execute([$_SESSION['user_id']]);
    header('Location: notifications.php?msg=read'); exit;
}

$n = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$n->execute([$_SESSION['user_id']]);
$notifs = $n->fetchAll();

$msg = $_GET['msg'] ?? '';
$flash = $msg === 'read' ? 'All notifications marked as read.' : '';

$page_title = 'Notifications';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Notifications</h1>
    <p>Important alerts, deadlines, and updates from your university.</p>
  </div>
  <div class="actions">
    <a href="?mark_read=1" class="btn btn-outline"><i class="fa-solid fa-check-double"></i> Mark all as read</a>
    <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
  <ul class="notif-list">
    <?php if (!$notifs): ?>
      <li style="border:none;color:var(--neutral);text-align:center;padding:20px">No notifications yet.</li>
    <?php endif; ?>
    <?php foreach ($notifs as $n): ?>
      <li>
        <div class="ico"><i class="fa-solid fa-bell"></i></div>
        <div style="flex:1">
          <div class="title">
            <?= htmlspecialchars($n['title']) ?>
            <?php if (!$n['is_read']): ?><span class="badge badge-info" style="margin-left:6px">NEW</span><?php endif; ?>
          </div>
          <div class="msg"><?= htmlspecialchars($n['message']) ?></div>
          <div class="time"><?= htmlspecialchars(date('M d, Y H:i', strtotime($n['created_at']))) ?></div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
