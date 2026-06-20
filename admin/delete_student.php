<?php
require_once __DIR__ . '/../config/auth.php';
require_role('admin');
require_once __DIR__ . '/../config/db.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $st = $pdo->prepare("DELETE FROM students WHERE id=?");
    $st->execute([$id]);
}
header('Location: students.php?msg=deleted');
exit;
