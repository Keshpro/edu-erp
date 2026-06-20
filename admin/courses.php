<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

$departments = ['Computer Science','Mathematics','English','Psychology','Business Admin','Mechanical Eng','Information Systems'];

// Handle POST add/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $credits = (int)($_POST['credits'] ?? 3);
    $lecturer = trim($_POST['lecturer'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    $room = trim($_POST['room'] ?? '');
    $seats_total = (int)($_POST['seats_total'] ?? 40);
    $department = $_POST['department'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $u = $pdo->prepare("UPDATE courses SET code=?, name=?, credits=?, lecturer=?, schedule=?, room=?, seats_total=?, department=? WHERE id=?");
        $u->execute([$code,$name,$credits,$lecturer,$schedule,$room,$seats_total,$department,$id]);
        header('Location: courses.php?msg=updated'); exit;
    } else {
        $i = $pdo->prepare("INSERT INTO courses (code, name, credits, lecturer, schedule, room, seats_taken, seats_total, department) VALUES (?,?,?,?,?,?,?,?,?)");
        $i->execute([$code,$name,$credits,$lecturer,$schedule,$room,0,$seats_total,$department]);
        header('Location: courses.php?msg=added'); exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $d = $pdo->prepare("DELETE FROM courses WHERE id=?");
    $d->execute([(int)$_GET['delete']]);
    header('Location: courses.php?msg=deleted'); exit;
}

// Load editing course
$editing = null;
if (isset($_GET['edit'])) {
    $e = $pdo->prepare("SELECT * FROM courses WHERE id=?");
    $e->execute([(int)$_GET['edit']]);
    $editing = $e->fetch();
}

$showForm = isset($_GET['action']) && $_GET['action']==='add' || $editing;
$courses = $pdo->query("SELECT * FROM courses ORDER BY id DESC")->fetchAll();

$msg = $_GET['msg'] ?? '';
$flash = ['added'=>'Course added.','updated'=>'Course updated.','deleted'=>'Course deleted.'][$msg] ?? '';

$page_title = 'Course Catalog';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Course Catalog</h1>
    <p>Manage all courses offered across departments.</p>
  </div>
  <div class="actions">
    <?php if (!$showForm): ?>
      <a href="?action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Course</a>
    <?php else: ?>
      <a href="courses.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<?php if ($showForm): ?>
  <div class="modal-card">
    <h2><?= $editing ? 'Edit Course' : 'Add New Course' ?></h2>
    <p class="sub">Provide the academic course information.</p>
    <form method="post">
      <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
      <?php endif; ?>
      <div class="form-row">
        <div class="form-group">
          <label>Course Code</label>
          <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($editing['code'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Credits</label>
          <input type="number" name="credits" class="form-control" min="1" max="6" required value="<?= htmlspecialchars($editing['credits'] ?? 3) ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Course Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editing['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Lecturer</label>
        <input type="text" name="lecturer" class="form-control" value="<?= htmlspecialchars($editing['lecturer'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Schedule</label>
          <input type="text" name="schedule" class="form-control" placeholder="Mon/Wed 10:00 AM" value="<?= htmlspecialchars($editing['schedule'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Room</label>
          <input type="text" name="room" class="form-control" value="<?= htmlspecialchars($editing['room'] ?? '') ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Seats Total</label>
          <input type="number" name="seats_total" class="form-control" min="1" required value="<?= htmlspecialchars($editing['seats_total'] ?? 40) ?>">
        </div>
        <div class="form-group">
          <label>Department</label>
          <select name="department" class="form-control" required>
            <option value="">Select department</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d ?>" <?= ($editing['department'] ?? '')===$d?'selected':'' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <a href="courses.php" class="btn btn-outline">Cancel</a>
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Course</button>
      </div>
    </form>
  </div>
<?php else: ?>
  <div class="card">
    <div class="card-title">All Courses (<?= count($courses) ?>)</div>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr><th>Code</th><th>Name</th><th>Credits</th><th>Lecturer</th><th>Schedule</th><th>Room</th><th>Seats</th><th>Department</th><th style="text-align:right">Actions</th></tr>
        </thead>
        <tbody>
        <?php if (!$courses): ?>
          <tr><td colspan="9" style="text-align:center;padding:20px;color:var(--neutral)">No courses found.</td></tr>
        <?php endif; ?>
        <?php foreach ($courses as $c): ?>
          <tr>
            <td><strong style="color:var(--primary)"><?= htmlspecialchars($c['code']) ?></strong></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= (int)$c['credits'] ?></td>
            <td><?= htmlspecialchars($c['lecturer']) ?></td>
            <td><?= htmlspecialchars($c['schedule']) ?></td>
            <td><?= htmlspecialchars($c['room']) ?></td>
            <td><?= (int)$c['seats_taken'] ?>/<?= (int)$c['seats_total'] ?></td>
            <td><?= htmlspecialchars($c['department']) ?></td>
            <td class="actions-cell" style="text-align:right">
              <a href="?edit=<?= $c['id'] ?>" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
              <a href="?delete=<?= $c['id'] ?>" class="delete" title="Delete" onclick="return confirm('Delete this course?')"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
