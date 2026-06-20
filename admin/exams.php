<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $st = $pdo->prepare("INSERT INTO exams (course_id, exam_date, exam_time, location, status) VALUES (?,?,?,?, 'scheduled')");
        $st->execute([(int)$_POST['course_id'], $_POST['exam_date'], $_POST['exam_time'], trim($_POST['location'] ?? '')]);
        header('Location: exams.php?msg=created'); exit;
    }
    if ($action === 'publish') {
        $st = $pdo->prepare("UPDATE exams SET status='published' WHERE id=?");
        $st->execute([(int)$_POST['id']]);
        header('Location: exams.php?msg=published'); exit;
    }
}
if (isset($_GET['delete'])) {
    $d = $pdo->prepare("DELETE FROM exams WHERE id=?");
    $d->execute([(int)$_GET['delete']]);
    header('Location: exams.php?msg=deleted'); exit;
}

$exams = $pdo->query("SELECT e.*, c.code AS ccode, c.name AS cname FROM exams e JOIN courses c ON e.course_id=c.id ORDER BY e.exam_date ASC")->fetchAll();
$courses = $pdo->query("SELECT id, code, name FROM courses ORDER BY code")->fetchAll();
$pending = array_filter($exams, fn($e) => $e['status']==='scheduled');

$msg = $_GET['msg'] ?? '';
$flash = ['created'=>'Exam created.','published'=>'Results published.','deleted'=>'Exam deleted.'][$msg] ?? '';

$page_title = 'Examination Management';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Examination Management</h1>
    <p>Manage schedules, assignments, and results publishing.</p>
  </div>
  <div class="actions">
    <a href="#create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create New Exam</a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-title">Upcoming Exam Schedule</div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Course</th><th>Date</th><th>Time</th><th>Location</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
      <tbody>
        <?php if (!$exams): ?>
          <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--neutral)">No exams scheduled.</td></tr>
        <?php endif; ?>
        <?php foreach ($exams as $e): ?>
          <tr>
            <td>
              <strong style="color:var(--primary)"><?= htmlspecialchars($e['ccode']) ?></strong><br>
              <span style="font-size:12px;color:var(--neutral)"><?= htmlspecialchars($e['cname']) ?></span>
            </td>
            <td><?= htmlspecialchars(date('M d, Y', strtotime($e['exam_date']))) ?></td>
            <td><?= htmlspecialchars(date('h:i A', strtotime($e['exam_time']))) ?></td>
            <td><?= htmlspecialchars($e['location']) ?></td>
            <td>
              <?php $cls = $e['status']==='published'?'success':'warning'; ?>
              <span class="badge badge-<?= $cls ?>"><?= htmlspecialchars($e['status']) ?></span>
            </td>
            <td class="actions-cell" style="text-align:right">
              <?php if ($e['status']==='scheduled'): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="action" value="publish">
                  <input type="hidden" name="id" value="<?= $e['id'] ?>">
                  <button class="btn btn-sm btn-success" title="Publish Results"><i class="fa-solid fa-upload"></i> Publish</button>
                </form>
              <?php endif; ?>
              <a href="?delete=<?= $e['id'] ?>" class="delete" title="Delete" onclick="return confirm('Delete this exam?')"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="grid-2">
  <div class="card" id="create">
    <div class="card-title">Create New Exam</div>
    <form method="post">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label>Course</label>
        <select name="course_id" class="form-control" required>
          <option value="">Select course</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'].' — '.$c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Date</label>
          <input type="date" name="exam_date" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Time</label>
          <input type="time" name="exam_time" class="form-control" required>
        </div>
      </div>
      <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" class="form-control" placeholder="Main Auditorium / Hall A" required>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Exam</button>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="card-title">Results Publishing</div>
    <p style="font-size:13px;color:var(--neutral);margin-bottom:14px">Pending results awaiting final approval and publication to the student portal.</p>
    <?php if (!$pending): ?>
      <div style="color:var(--neutral);font-size:13px">No pending exams.</div>
    <?php endif; ?>
    <?php foreach ($pending as $e): ?>
      <div class="schedule-item">
        <div class="row1">
          <div>
            <div class="name"><?= htmlspecialchars($e['ccode']) ?></div>
            <div class="info"><?= htmlspecialchars($e['cname']) ?></div>
          </div>
          <form method="post">
            <input type="hidden" name="action" value="publish">
            <input type="hidden" name="id" value="<?= $e['id'] ?>">
            <button class="btn btn-sm btn-primary">Publish</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
