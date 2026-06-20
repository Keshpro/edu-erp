<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 10;
$offset = ($page - 1) * $per;

$where = '';
$params = [];
if ($q !== '') {
    $where = "WHERE name LIKE ? OR email LIKE ? OR student_id LIKE ? OR program LIKE ?";
    $like = "%$q%";
    $params = [$like, $like, $like, $like];
}
$cnt = $pdo->prepare("SELECT COUNT(*) FROM students $where");
$cnt->execute($params);
$total = (int)$cnt->fetchColumn();
$pages = max(1, (int)ceil($total / $per));

$sql = "SELECT * FROM students $where ORDER BY id DESC LIMIT $per OFFSET $offset";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$msg = $_GET['msg'] ?? '';
$flash = [
    'added'   => 'Student added successfully.',
    'updated' => 'Student updated successfully.',
    'deleted' => 'Student deleted.',
][$msg] ?? '';

$page_title = 'Student Directory';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/sidebar.php';
require __DIR__ . '/../partials/navbar.php';
?>

<div class="page-header">
  <div>
    <h1>Student Directory</h1>
    <p>Manage enrolled students across all programs.</p>
  </div>
  <div class="actions">
    <a href="add_student.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Student</a>
  </div>
</div>

<?php if ($flash): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
  <form method="get" class="filter-row">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Search by name, email, or ID...">
    <select class="form-control" name="program" disabled>
      <option>All Programs</option>
    </select>
    <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
  </form>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Program</th><th>Status</th><th style="text-align:right">Actions</th></tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" style="text-align:center;padding:24px;color:var(--neutral)">No students found.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $s): ?>
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
            <td class="actions-cell" style="text-align:right">
              <a href="edit_student.php?id=<?= $s['id'] ?>" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
              <a href="delete_student.php?id=<?= $s['id'] ?>" class="delete" title="Delete" onclick="return confirm('Delete this student?')"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px">
    <div style="font-size:12px;color:var(--neutral)">Showing <?= count($rows) ?> of <?= $total ?> students</div>
    <div class="pagination">
      <?php for ($i=1;$i<=$pages;$i++): ?>
        <a href="?q=<?= urlencode($q) ?>&page=<?= $i ?>" class="<?= $i==$page?'current':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
