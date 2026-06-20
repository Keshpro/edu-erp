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

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student_pk) {
    $action = $_POST['action'] ?? '';
    if ($action === 'register') {
        $course_id = (int)$_POST['course_id'];
        // Check not already registered
        $chk = $pdo->prepare("SELECT id FROM registrations WHERE student_pk=? AND course_id=?");
        $chk->execute([$student_pk, $course_id]);
        if (!$chk->fetch()) {
            $i = $pdo->prepare("INSERT INTO registrations (student_pk, course_id, status) VALUES (?,?, 'pending')");
            $i->execute([$student_pk, $course_id]);
            $pdo->prepare("UPDATE courses SET seats_taken = seats_taken + 1 WHERE id=?")->execute([$course_id]);
            header('Location: registration.php?msg=registered'); exit;
        }
        header('Location: registration.php?msg=duplicate'); exit;
    }
    if ($action === 'drop') {
        $reg_id = (int)$_POST['reg_id'];
        $g = $pdo->prepare("SELECT course_id FROM registrations WHERE id=? AND student_pk=?");
        $g->execute([$reg_id, $student_pk]);
        $row = $g->fetch();
        if ($row) {
            $pdo->prepare("DELETE FROM registrations WHERE id=?")->execute([$reg_id]);
            $pdo->prepare("UPDATE courses SET seats_taken = GREATEST(seats_taken - 1, 0) WHERE id=?")->execute([$row['course_id']]);
        }
        header('Location: registration.php?msg=dropped'); exit;
    }
}

$q = trim($_GET['q'] ?? '');
$dept = $_GET['dept'] ?? '';

// Departments
$depts = $pdo->query("SELECT DISTINCT department FROM courses ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

// Available courses (not already registered)
$params = [$student_pk];
$where = "WHERE c.id NOT IN (SELECT course_id FROM registrations WHERE student_pk=?)";
if ($q !== '') { $where .= " AND (c.name LIKE ? OR c.code LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($dept !== '') { $where .= " AND c.department = ?"; $params[] = $dept; }
$cq = $pdo->prepare("SELECT c.* FROM courses c $where ORDER BY c.code");
$cq->execute($params);
$available = $cq->fetchAll();

// My schedule
$myRegs = [];
$currentLoad = 0;
if ($student_pk) {
    $mr = $pdo->prepare("SELECT r.id AS reg_id, r.status, c.* FROM registrations r JOIN courses c ON r.course_id=c.id WHERE r.student_pk=?");
    $mr->execute([$student_pk]);
    $myRegs = $mr->fetchAll();
    foreach ($myRegs as $r) $currentLoad += (int)$r['credits'];
}

$msg = $_GET['msg'] ?? '';
$flashMap = [
    'registered' => ['success','Course registered. Awaiting approval.'],
    'duplicate'  => ['danger','You are already registered in this course.'],
    'dropped'    => ['success','Course dropped from your schedule.'],
];
$flash = $flashMap[$msg] ?? null;

$page_title = 'Course Registration';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header no-print">
  <div>
    <h1>Course Registration</h1>
    <p>Fall Semester 2024 — select courses to add to your schedule.</p>
  </div>
  <div class="actions">
    <button onclick="window.print()" class="btn btn-outline"><i class="fa-solid fa-print"></i> Print Schedule</button>
    <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-<?= $flash[0] ?>"><i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($flash[1]) ?></div>
<?php endif; ?>

<div class="grid-2">
  <div>
    <div class="card no-print">
      <div class="card-title">Available Courses</div>
      <form method="get" class="filter-row">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Search by course code or name...">
        <select name="dept" class="form-control">
          <option value="">All Departments</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= htmlspecialchars($d) ?>" <?= $dept===$d?'selected':'' ?>><?= htmlspecialchars($d) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
      </form>

      <div class="courses-grid">
        <?php if (!$available): ?>
          <div style="color:var(--neutral);font-size:13px;grid-column:1/-1;text-align:center;padding:20px">No matching courses.</div>
        <?php endif; ?>
        <?php foreach ($available as $c):
          $almostFull = $c['seats_taken'] >= ($c['seats_total'] * 0.9); ?>
          <div class="course-card">
            <div class="top">
              <span class="code"><?= htmlspecialchars($c['code']) ?></span>
              <span class="credits"><?= (int)$c['credits'] ?> Credits</span>
            </div>
            <h3><?= htmlspecialchars($c['name']) ?></h3>
            <div class="meta"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($c['lecturer']) ?: 'TBA' ?></div>
            <div class="meta"><i class="fa-solid fa-clock"></i> <?= htmlspecialchars($c['schedule']) ?></div>
            <div class="meta"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($c['room']) ?></div>
            <div class="seats <?= $almostFull?'warn':'' ?>">
              Seats: <?= (int)$c['seats_taken'] ?>/<?= (int)$c['seats_total'] ?>
              <?= $almostFull?'<span class="badge badge-warning" style="margin-left:6px">Almost Full</span>':'' ?>
            </div>
            <div class="footer">
              <form method="post" style="width:100%">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                <button class="btn btn-primary btn-block" <?= ($c['seats_taken']>=$c['seats_total'])?'disabled':'' ?>>
                  <i class="fa-solid fa-plus"></i> Select &amp; Register
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div>
    <div class="schedule-panel">
      <div class="card-title" style="margin-bottom:10px">My Schedule</div>
      <div class="load">
        Current Load
        <strong><?= $currentLoad ?> / 18 Credits</strong>
        <div style="background:#eee;border-radius:6px;height:8px;margin-top:6px;overflow:hidden">
          <div style="background:var(--primary);height:100%;width:<?= min(100, ($currentLoad/18)*100) ?>%"></div>
        </div>
      </div>
      <?php if (!$myRegs): ?>
        <div style="color:var(--neutral);font-size:13px">No courses registered yet.</div>
      <?php endif; ?>
      <?php foreach ($myRegs as $r): ?>
        <div class="schedule-item">
          <div class="row1">
            <div class="name"><?= htmlspecialchars($r['name']) ?></div>
            <?php $cls = $r['status']==='approved'?'success':'warning'; ?>
            <span class="badge badge-<?= $cls ?>"><?= htmlspecialchars($r['status']) ?></span>
          </div>
          <div class="info"><?= htmlspecialchars($r['code']) ?> · <?= (int)$r['credits'] ?> Credits · <?= htmlspecialchars($r['schedule']) ?></div>
          <form method="post" class="no-print" style="margin-top:6px">
            <input type="hidden" name="action" value="drop">
            <input type="hidden" name="reg_id" value="<?= $r['reg_id'] ?>">
            <button class="btn btn-sm btn-outline" onclick="return confirm('Drop this course?')"><i class="fa-solid fa-xmark"></i> Drop</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
