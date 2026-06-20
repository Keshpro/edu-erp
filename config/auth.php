<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /edu-erp/auth/login.php');
        exit;
    }
}
function require_role($role) {
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        header('Location: /edu-erp/auth/login.php');
        exit;
    }
}
