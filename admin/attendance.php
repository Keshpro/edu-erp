<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

// POST: mark attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark') {
    $course_id = (int)$_POST['course_id'];
    $date = $_POST['date'];
    $present_ids = $_POST['present_ids'] ?? [];

    // Get all enrolled students
    $en = $pdo->prepare("SELECT s.id FROM students s JOIN registrations r ON r.student_pk=s.id WHERE r.course_id=?");
    $en->execute([$course_id]);
    $enrolled = $en->fetchAll(PDO::FETCH_COLUMN);

    // Remove existing rows for that course+date to avoid duplicates
    $del = $pdo->prepare("DELETE FROM attendance WHERE course_id=? AND date=?");
    $del->execute([$course_id, $date]);

    $ins = $pdo->prepare("INSERT INTO attendance (student_pk, course_id, date, status) VALUES (?,?,?,?)");
    foreach ($enrolled as $sid) {
        $status = in_array($sid, $present_ids) ? 'present' : 'absent';
        $ins->execute([$sid, $course_id, $date, $status]);
    }
    header('Location: attendance.php?msg=saved&course_id=' . $course_id . '&date=' . urlencode($date));
    exit;
}

// Stats
$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM attendance")->fetchColumn();
$presentRows = (int)$pdo->query("SELECT COUNT(*) FROM attendance WHERE status='present'")->fetchColumn();
$overallPct = $totalRows ? round(($presentRows / $totalRows) * 100) : 0;
$coursesToday = (int)$pdo->query("SELECT COUNT(DISTINCT course_id) FROM attendance WHERE date=CURDATE()")->fetchColumn();

// Students at risk (<75%)
$atRiskSql = "SELECT student_pk, SUM(status='present') AS p, COUNT(*) AS t FROM attendance GROUP BY student_pk HAVING t>0 AND (p/t)<0.75";
$atRisk = count($pdo->query($atRiskSql)->fetchAll());

// Course filter
$courses = $pdo->query("SELECT id, code, name FROM courses ORDER BY code")->fetchAll();
$selected_course = (int)($_GET['course_id'] ?? 0);
$selected_date = $_GET['date'] ?? date('Y-m-d');

$enrolledStudents = [];
if ($selected_course) {
    $st = $pdo->prepare("SELECT s.* FROM students s JOIN registrations r ON r.student_pk=s.id WHERE r.course_id=? ORDER BY s.name");
    $st->execute([$selected_course]);
    $enrolledStudents = $st->fetchAll();
}

// Recent attendance
$recent = $pdo->query("SELECT a.*, s.name AS sname, s.student_id, c.code AS ccode FROM attendance a JOIN students s ON a.student_pk=s.id JOIN courses c ON a.course_id=c.id ORDER BY a.id DESC LIMIT 10")->fetchAll();

$msg = $_GET['msg'] ?? '';
$flash = ($msg === 'saved') ? 'Attendance recorded successfully.' : '';

$page_title = 'Attendance Management';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Attendance Management</h1>
    <p>Track and manage student presence across courses.</p>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-percent"></i></div>
    <div><div class="value"><?= $overallPct ?>%</div><div class="label">Overall Attendance</div></div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-calendar-day"></i></div>
    <div><div class="value"><?= $coursesToday ?></div><div class="label">Courses Today</div></div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div><div class="value"><?= $atRisk ?></div><div class="label">Students at Risk</div><div class="trend down">Below 75%</div></div>
  </div>
  <div class="stat-card">
    <div class="icon"><i class="fa-solid fa-clipboard-list"></i></div>
    <div><div class="value"><?= $totalRows ?></div><div class="label">Total Records</div></div>
  </div>
</div>

<div class="card">
  <div class="card-title">Mark Attendance</div>
  <form method="get" class="filter-row">
    <select name="course_id" class="form-control" required>
      <option value="">Select Course</option>
      <?php foreach ($courses as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $selected_course==$c['id']?'selected':'' ?>>
          <?= htmlspecialchars($c['code'] . ' — ' . $c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($selected_date) ?>" required>
    <button class="btn btn-primary"><i class="fa-solid fa-users"></i> Load Roster</button>
  </form>

  <?php if ($selected_course && $enrolledStudents): ?>
    <form method="post" style="margin-top:14px">
      <input type="hidden" name="action" value="mark">
      <input type="hidden" name="course_id" value="<?= $selected_course ?>">
      <input type="hidden" name="date" value="<?= htmlspecialchars($selected_date) ?>">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Student ID</th><th>Name</th><th>Program</th><th style="text-align:center">Present</th></tr></thead>
          <tbody>
            <?php foreach ($enrolledStudents as $st): ?>
              <tr>
                <td><?= htmlspecialchars($st['student_id']) ?></td>
                <td>
                  <div class="student-cell">
                    <div class="av"><?= strtoupper(substr($st['name'],0,2)) ?></div>
                    <span><?= htmlspecialchars($st['name']) ?></span>
                  </div>
                </td>
                <td><?= htmlspecialchars($st['program']) ?></td>
                <td style="text-align:center">
                  <input type="checkbox" name="present_ids[]" value="<?= $st['id'] ?>" checked>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="form-actions" style="margin-top:14px">
        <button class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Attendance</button>
      </div>
    </form>
  <?php elseif ($selected_course): ?>
    <div style="padding:14px;color:var(--neutral);font-size:13px">No students enrolled in this course yet.</div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-title">Recent Attendance Records</div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Student</th><th>Course</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (!$recent): ?>
          <tr><td colspan="4" style="text-align:center;padding:18px;color:var(--neutral)">No records yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td>
              <div class="student-cell">
                <div class="av"><?= strtoupper(substr($r['sname'],0,2)) ?></div>
                <div>
                  <div><?= htmlspecialchars($r['sname']) ?></div>
                  <div style="font-size:11px;color:var(--neutral)"><?= htmlspecialchars($r['student_id']) ?></div>
                </div>
              </div>
            </td>
            <td><strong style="color:var(--primary)"><?= htmlspecialchars($r['ccode']) ?></strong></td>
            <td><?= htmlspecialchars(date('M d, Y', strtotime($r['date']))) ?></td>
            <td>
              <?php $cls = $r['status']==='present'?'success':'danger'; ?>
              <span class="badge badge-<?= $cls ?>"><?= htmlspecialchars($r['status']) ?></span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
