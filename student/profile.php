<?php
require_once __DIR__ . '/../config/auth.php';
require_role('student');
require_once __DIR__ . '/../config/db.php';

$u = $pdo->prepare("SELECT * FROM users WHERE id=?");
$u->execute([$_SESSION['user_id']]);
$user = $u->fetch();
$s = $pdo->prepare("SELECT * FROM students WHERE email=?");
$s->execute([$user['email']]);
$student = $s->fetch();
$student_pk = $student['id'] ?? 0;

$regs = [];
$totalCredits = 0;
$attendancePct = 92;
if ($student_pk) {
    $r = $pdo->prepare("SELECT r.*, c.code, c.name, c.credits, c.lecturer FROM registrations r JOIN courses c ON r.course_id=c.id WHERE r.student_pk=?");
    $r->execute([$student_pk]);
    $regs = $r->fetchAll();
    foreach ($regs as $reg) if ($reg['status']==='approved') $totalCredits += (int)$reg['credits'];

    $a = $pdo->prepare("SELECT SUM(status='present') p, COUNT(*) t FROM attendance WHERE student_pk=?");
    $a->execute([$student_pk]);
    $att = $a->fetch();
    if (($att['t'] ?? 0) > 0) $attendancePct = round(($att['p']/$att['t'])*100);
}

$placeholderGrades = ['A','B+','A-','A','B','A+'];

$page_title = 'Student Profile';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Student Profile</h1>
    <p>Your academic records, standing, and current semester overview.</p>
  </div>
  <div class="actions">
    <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
  </div>
</div>

<?php if (!$student): ?>
  <div class="alert alert-danger">Your student profile is not linked. Contact administration.</div>
<?php else: ?>

<div class="card">
  <div class="profile-header">
    <div class="av" style="width:64px;height:64px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:22px">
      <?= strtoupper(substr($student['name'],0,2)) ?>
    </div>
    <div>
      <h2 style="margin:0;color:var(--primary)"><?= htmlspecialchars($student['name']) ?></h2>
      <div style="color:var(--neutral);font-size:13px"><?= htmlspecialchars($student['student_id']) ?> ·
        <?php $cls = $student['status']==='active'?'success':($student['status']==='pending'?'warning':'danger'); ?>
        <span class="badge badge-<?= $cls ?>"><?= htmlspecialchars($student['status']) ?></span>
      </div>
    </div>
  </div>

  <div class="profile-meta">
    <div class="item"><div class="lbl">Program</div><div class="val"><?= htmlspecialchars($student['program']) ?></div></div>
    <div class="item"><div class="lbl">Enrollment Date</div><div class="val"><?= htmlspecialchars(date('F j, Y', strtotime($student['enrollment_date']))) ?></div></div>
    <div class="item"><div class="lbl">Advisor</div><div class="val">Dr. Alan Turing</div></div>
    <div class="item"><div class="lbl">Expected Graduation</div><div class="val">May 2027</div></div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Academic Standing</div>
    <div class="profile-meta">
      <div class="item"><div class="lbl">Cumulative GPA</div><div class="val" style="font-size:22px;color:var(--primary)">3.84 <span style="font-size:13px;color:var(--neutral)">/ 4.0</span></div></div>
      <div class="item"><div class="lbl">Credits Earned</div><div class="val" style="font-size:22px;color:var(--primary)"><?= $totalCredits ?: 45 ?></div></div>
      <div class="item"><div class="lbl">Academic Year</div><div class="val">Sophomore</div></div>
      <div class="item"><div class="lbl">Standing</div><div class="val"><span class="badge badge-success">Good</span></div></div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Attendance Overview</div>
    <div style="text-align:center;padding:14px 0">
      <div style="font-size:48px;color:var(--primary);font-weight:700"><?= $attendancePct ?>%</div>
      <div style="color:var(--neutral);font-size:13px">EXCELLENT</div>
      <div style="background:#eee;border-radius:6px;height:10px;margin:14px 0;overflow:hidden">
        <div style="background:var(--success);height:100%;width:<?= $attendancePct ?>%"></div>
      </div>
      <div style="font-size:12px;color:var(--neutral)">Present for <?= round($attendancePct*1.5) ?> of 150 classes</div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-title">Current Semester Courses</div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Course</th><th>Code</th><th>Credits</th><th>Instructor</th><th>Midterm Grade</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (!$regs): ?>
          <tr><td colspan="6" style="text-align:center;padding:18px;color:var(--neutral)">No courses registered yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($regs as $i => $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><strong style="color:var(--primary)"><?= htmlspecialchars($r['code']) ?></strong></td>
            <td><?= (int)$r['credits'] ?></td>
            <td><?= htmlspecialchars($r['lecturer']) ?: 'TBA' ?></td>
            <td><span class="badge badge-info"><?= $placeholderGrades[$i % count($placeholderGrades)] ?></span></td>
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

<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
