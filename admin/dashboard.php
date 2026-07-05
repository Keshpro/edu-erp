<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses  = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$upcomingExams = (int)$pdo->query("SELECT COUNT(*) FROM exams WHERE exam_date >= CURDATE()")->fetchColumn();
$activeFaculty = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='faculty'")->fetchColumn();
$recent = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5")->fetchAll();

$page_title = 'Institutional Dashboard';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Institutional Dashboard</h1>
    <p>Overview of key university metrics for the current Semester.</p>
  </div>
  <div class="actions">
    <select class="form-control" style="width:auto"><option>Fall Semester 2024</option><option>Spring 2025</option></select>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-user-graduate"></i></div>
    <div>
      <div class="value"><?= number_format($totalStudents) ?></div>
      <div class="label">Total Students</div>
      <div class="trend"><i class="fa-solid fa-arrow-up"></i> 8% capacity filled</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-book"></i></div>
    <div>
      <div class="value"><?= number_format($totalCourses) ?></div>
      <div class="label">Registered Courses</div>
      <div class="trend">Across all departments</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-file-pen"></i></div>
    <div>
      <div class="value"><?= number_format($upcomingExams) ?></div>
      <div class="label">Upcoming Exams</div>
      <div class="trend">Next 14 days</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-chalkboard-user"></i></div>
    <div>
      <div class="value"><?= number_format($activeFaculty) ?></div>
      <div class="label">Active Faculty</div>
      <div class="trend"><i class="fa-solid fa-arrow-up"></i> +2 this semester</div>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-title">Student Performance Trends <span class="badge badge-info">Fall 2024</span></div>
    <div style="display:flex;gap:10px;align-items:flex-end;height:180px;padding:10px 0">
      <?php $bars = [65,72,80,68,90,85,78,82,88,76,84,92];
      foreach ($bars as $i => $b): ?>
        <div style="flex:1;background:linear-gradient(180deg,#2C5282,#1A365D);border-radius:6px 6px 0 0;height:<?= $b ?>%" title="<?= $b ?>%"></div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--neutral);margin-top:6px">
      <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
      <span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
    </div>
  </div>
  <div class="card">
    <div class="card-title">Upcoming Deadlines</div>
    <ul class="deadline-list">
      <li><span class="dot"></span><div><div class="title">Final Grade Submission</div><div class="desc">All faculty must submit by Dec 18</div></div></li>
      <li><span class="dot"></span><div><div class="title">Course Registration Opens</div><div class="desc">Spring 2025 registration begins Nov 4</div></div></li>
      <li><span class="dot"></span><div><div class="title">Faculty Senate Meeting</div><div class="desc">Monthly general assembly</div></div></li>
      <li><span class="dot"></span><div><div class="title">Budget Proposal Due</div><div class="desc">Departmental budgets due Nov 30</div></div></li>
    </ul>
  </div>
</div>

<div class="card">
  <div class="card-title">Recent Students <a href="students.php" class="btn btn-outline btn-sm">View all</a></div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Program</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($recent as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['student_id']) ?></td>
            <td>
              <div class="student-cell">
                <div class="av"><?= strtoupper(substr($s['name'],0,2)) ?></div>
                <span><?= htmlspecialchars($s['name']) ?></span>
              </div>
            </td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= htmlspecialchars($s['program']) ?></td>
            <td>
              <?php $cls = $s['status']==='active'?'success':($s['status']==='pending'?'warning':'danger'); ?>
              <span class="badge badge-<?= $cls ?>"><?= htmlspecialchars($s['status']) ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
