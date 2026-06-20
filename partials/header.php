<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$page_title = $page_title ?? 'EduCore ERP';
$base = '/edu-erp';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?> | EduCore ERP</title>
<link rel="icon" type="image/svg+xml" href="<?= $base ?>/assets/images/logo.svg">
<link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="layout">
