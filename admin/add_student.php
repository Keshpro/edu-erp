<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

$programs = ['Computer Science','Software Engineering','Business Admin','Mechanical Eng','Information Systems','Psychology','Mathematics','English'];
$statuses = ['active','pending','suspended'];

$preview_id = 'STU-' . date('Y') . '-' . str_pad((string)random_int(100, 9999), 4, '0', STR_PAD_LEFT);
$errors = [];
$old = ['name'=>'','email'=>'','program'=>'','status'=>'active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['program'] = $_POST['program'] ?? '';
    $old['status']  = $_POST['status'] ?? 'active';

    if ($old['name'] === '') $errors[] = 'Name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!in_array($old['program'], $programs)) $errors[] = 'Please select a program.';
    if (!in_array($old['status'], $statuses)) $errors[] = 'Invalid status.';

    if (!$errors) {
        $sid = $preview_id;
        // ensure unique
        $check = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
        do {
            $check->execute([$sid]);
            if ($check->fetch()) {
                $sid = 'STU-' . date('Y') . '-' . str_pad((string)random_int(100, 9999), 4, '0', STR_PAD_LEFT);
            } else break;
        } while (true);

        $st = $pdo->prepare("INSERT INTO students (student_id, name, email, program, status, enrollment_date) VALUES (?,?,?,?,?,?)");
        $st->execute([$sid, $old['name'], $old['email'], $old['program'], $old['status'], date('Y-m-d')]);

        // create user account if not exists
        $u = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $u->execute([$old['email']]);
        if (!$u->fetch()) {
            $hash = password_hash('student123', PASSWORD_BCRYPT);
            $iu = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
            $iu->execute([$old['name'], $old['email'], $hash, 'student']);
        }
        header('Location: students.php?msg=added');
        exit;
    }
}

$page_title = 'Add New Student';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="modal-card">
  <h2>Add New Student</h2>
  <p class="sub">Enter the details to create a New Student record.</p>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>

  <form method="post">
    <div class="form-group">
      <label>Student ID</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($preview_id) ?>" readonly>
    </div>
    <div class="form-group">
      <label>Full Name</label>
      <input type="text" name="name" class="form-control" placeholder="Enter student's full name" required value="<?= htmlspecialchars($old['name']) ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" class="form-control" placeholder="student@university.edu" required value="<?= htmlspecialchars($old['email']) ?>">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Program</label>
        <select name="program" class="form-control" required>
          <option value="">Select an academic program</option>
          <?php foreach ($programs as $p): ?>
            <option value="<?= $p ?>" <?= $old['program']===$p?'selected':'' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= $s ?>" <?= $old['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <a href="students.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Student</button>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
