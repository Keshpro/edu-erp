<?php
require_once __DIR__ . '/../config/auth.php';
require_role('student');
require_once __DIR__ . '/../config/db.php';

// Find linked student record
$u = $pdo->prepare("SELECT * FROM users WHERE id=?");
$u->execute([$_SESSION['user_id']]);
$user = $u->fetch();

$s = $pdo->prepare("SELECT * FROM students WHERE email=?");
$s->execute([$user['email']]);
$student = $s->fetch();
$student_pk = $student['id'] ?? 0;

$regs = [];
$totalCredits = 0;
$attendancePct = 0;
if ($student_pk) {
    $r = $pdo->prepare("SELECT r.*, c.code, c.name, c.credits, c.lecturer, c.schedule, c.room FROM registrations r JOIN courses c ON r.course_id=c.id WHERE r.student_pk=?");
    $r->execute([$student_pk]);
    $regs = $r->fetchAll();
    foreach ($regs as $reg) {
        if ($reg['status'] === 'approved') $totalCredits += (int)$reg['credits'];
    }
    $a = $pdo->prepare("SELECT SUM(status='present') p, COUNT(*) t FROM attendance WHERE student_pk=?");
    $a->execute([$student_pk]);
    $att = $a->fetch();
    $attendancePct = ($att['t'] ?? 0) ? round(($att['p']/$att['t'])*100) : 92;
}

$n = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$n->execute([$_SESSION['user_id']]);
$notifCount = (int)$n->fetchColumn();

$page_title = 'Student Dashboard';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Welcome, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋</h1>
    <p>Your academic snapshot for Fall Semester 2024.</p>
  </div>
  <div class="actions">
    <a href="registration.php" class="btn btn-primary"><i class="fa-solid fa-clipboard-list"></i> Register Courses</a>
  </div>
</div>

<?php if (!$student): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-circle-exclamation"></i>
    Your student profile is not yet linked. Please contact the administration.
  </div>
<?php endif; ?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-award"></i></div>
    <div><div class="value">3.84</div><div class="label">Cumulative GPA</div><div class="trend">/ 4.0</div></div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-graduation-cap"></i></div>
    <div><div class="value"><?= $totalCredits ?: 10 ?></div><div class="label">Current Credits</div><div class="trend">of 18 max</div></div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-calendar-check"></i></div>
    <div><div class="value"><?= $attendancePct ?>%</div><div class="label">Attendance</div><div class="trend">Excellent</div></div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-bell"></i></div>
    <div><div class="value"><?= $notifCount ?></div><div class="label">Unread Alerts</div><div class="trend"><a href="notifications.php" style="color:var(--primary)">View all</a></div></div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Current Semester Courses <a href="registration.php" class="btn btn-outline btn-sm">Manage</a></div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Code</th><th>Course</th><th>Credits</th><th>Schedule</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (!$regs): ?>
            <tr><td colspan="5" style="text-align:center;padding:18px;color:var(--neutral)">No courses registered yet. <a href="registration.php">Register now</a>.</td></tr>
          <?php endif; ?>
          <?php foreach ($regs as $r): ?>
            <tr>
              <td><strong style="color:var(--primary)"><?= htmlspecialchars($r['code']) ?></strong></td>
              <td><?= htmlspecialchars($r['name']) ?></td>
              <td><?= (int)$r['credits'] ?></td>
              <td><?= htmlspecialchars($r['schedule']) ?></td>
              <td>
                <?php $cls = $r['status']==='approved'?'success':'warning'; ?>
                <span class="badge badge-<?= $cls ?>"><?= htmlspecialchars($r['status']) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Quick Actions</div>
    <ul class="deadline-list">
      <li><span class="dot"></span><div><div class="title"><a href="registration.php">Course Registration</a></div><div class="desc">Add or drop courses for this semester.</div></div></li>
      <li><span class="dot"></span><div><div class="title"><a href="profile.php">View Profile</a></div><div class="desc">Academic records and standing.</div></div></li>
      <li><span class="dot"></span><div><div class="title"><a href="notifications.php">Notifications (<?= $notifCount ?>)</a></div><div class="desc">Important alerts and deadlines.</div></div></li>
      <li><span class="dot"></span><div><div class="title"><a href="../auth/logout.php">Logout</a></div><div class="desc">Sign out of EduPortal.</div></div></li>
    </ul>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
