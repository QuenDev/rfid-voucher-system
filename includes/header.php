<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'ISU Voucher System'; ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
<?php if (!isset($hideLayout) || !$hideLayout): ?>
<div class="app-container">
<?php include 'sidebar.php'; ?>
<main class="main-content">
    <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; color: var(--text-main);"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
        <div class="user-profile" style="display: flex; align-items: center; gap: 1rem;">
            <div class="admin-details" style="text-align: right;">
                <p style="font-weight: 600; font-size: 0.9rem;"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Administrator'; ?></p>
                <p style="font-size: 0.8rem; color: var(--text-muted);"><?php echo isset($_SESSION['role']) ? ucfirst(htmlspecialchars($_SESSION['role'])) : 'Admin'; ?></p>
            </div>
            <div class="avatar" style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?php echo isset($_SESSION['fullname']) ? strtoupper($_SESSION['fullname'][0]) : 'A'; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
