<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ? LIMIT 1");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../student/dashboard.php');
            }
            exit;
        }
        $error = 'Invalid email, password, or role.';
    } else {
        $error = 'Please enter your email and password.';
    }
}
$base = '/edu-erp';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign in | EduPortal</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="<?= $base ?>/assets/images/logo.svg">
<link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <img src="<?= $base ?>/assets/images/logo.svg" alt="EduPortal">
      <h2>Sign in to EduPortal</h2>
      <p>University ERP System</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <div class="form-group">
        <label>Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@university.edu" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password <a href="#" class="forgot">Forgot password?</a></label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <div class="form-group">
        <label>Sign in as</label>
        <select name="role" class="form-control">
          <option value="student">Student</option>
          <option value="admin">Admin</option>
          <option value="faculty">Faculty</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fa-solid fa-right-to-bracket"></i> Sign In
      </button>
    </form>

    <div class="login-footer">
      Don't have an account? <a href="#">Contact IT Support</a>
      <div style="margin-top:10px;font-size:11px;color:var(--neutral)">
        Demo: <b>admin@edu.com</b> / admin123 &nbsp;|&nbsp; <b>student@edu.com</b> / student123
      </div>
      <div class="copy">
        &copy; <?= date('Y') ?> University Internal Systems. All rights reserved.<br>
        <a href="#">Privacy Policy</a> · <a href="#">Terms of Service</a> · <a href="#">Accessibility</a> · <a href="#">Contact Support</a>
      </div>
    </div>
  </div>
</body>
</html>
