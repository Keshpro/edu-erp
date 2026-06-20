<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM students WHERE id=?");
$st->execute([$id]);
$s = $st->fetch();
if (!$s) { header('Location: students.php'); exit; }

$programs = ['Computer Science','Software Engineering','Business Admin','Mechanical Eng','Information Systems','Psychology','Mathematics','English'];
$statuses = ['active','pending','suspended'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $program = $_POST['program'];
    $status = $_POST['status'];
    $u = $pdo->prepare("UPDATE students SET name=?, email=?, program=?, status=? WHERE id=?");
    $u->execute([$name, $email, $program, $status, $id]);
    header('Location: students.php?msg=updated');
    exit;
}

$page_title = 'Edit Student';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="modal-card">
  <h2>Edit Student</h2>
  <p class="sub">Update the details for <?= htmlspecialchars($s['student_id']) ?>.</p>

  <form method="post">
    <div class="form-group">
      <label>Student ID</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($s['student_id']) ?>" readonly>
    </div>
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($s['name']) ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($s['email']) ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Program</label>
        <select name="program" class="form-control">
          <?php foreach ($programs as $p): ?>
            <option value="<?= $p ?>" <?= $s['program']===$p?'selected':'' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach ($statuses as $st1): ?>
            <option value="<?= $st1 ?>" <?= $s['status']===$st1?'selected':'' ?>><?= ucfirst($st1) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <a href="students.php" class="btn btn-outline">Cancel</a>
      <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Update Student</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
