<?php
session_start();
require "config.php";

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT fullname FROM adminstafflogs WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle error if admin data is not found
if (!$admin) {
    echo "Admin data not found!";
    exit();
}

$students = 0;
$totalVouchers = 0;
$availableVouchers = 0;
$usedVouchers = 0;

$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $students = $result->fetch_assoc()['count'];
}

// Query for the vouchers statistics
$result = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as used
    FROM vouchers");
if ($result) {
    $voucherData = $result->fetch_assoc();
    $totalVouchers = $voucherData['total'] ?? 0;
    $availableVouchers = $voucherData['available'] ?? 0;
    $usedVouchers = $voucherData['used'] ?? 0;
}

// Calculate progress percentage for used vouchers
$progressPercent = ($totalVouchers > 0) ? ($usedVouchers / $totalVouchers) * 100 : 0;

// Fetch today's redeemed vouchers for the table
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT 
        CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS student_name,
        u.student_id,
        v.voucher_code,
        sv.redeemed_at
    FROM student_vouchers sv
    JOIN users u ON sv.student_id = u.student_id
    JOIN vouchers v ON sv.voucher_id = v.id
    WHERE DATE(sv.redeemed_at) = ?
    ORDER BY sv.redeemed_at DESC
");
$stmt->bind_param("s", $today);
$stmt->execute();
$dailyRedemptions = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Voucher System Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #38A169;
            --green-dark: #2F855A;
            --green-light: #48BB78;
            --secondary: #2c3e50;
            --light: #ecf0f1;
            --dark: #34495e;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        .dashboard {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: linear-gradient(to bottom, var(--primary), var(--green-dark));
            color: white;
            padding-top: 20px;
            z-index: 100;
            overflow-y: auto;
        }
        .logo-placeholder {
            text-align: center;
            padding: 10px;
        }
        .logo-placeholder img {
            max-width: 80%;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: background 0.3s, transform 0.2s;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }
        .nav-item i {
            min-width: 20px;
        }
        .nav-item.active {
            background: rgba(255, 255, 255, 0.3);
            font-weight: bold;
            border-left: 4px solid white;
        }
        .sidebar-section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            padding: 10px 20px 5px;
            color: rgba(255, 255, 255, 0.7);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .user-info {
            padding: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 240px;
            padding: 20px;
            width: 100%;
            overflow-y: auto;
        }
        .main-header {
            background: linear-gradient(to right, var(--primary), var(--green-dark));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .left-section {
            max-width: 60%;
        }
        .title {
            margin: 0;
            font-size: 1.8rem;
        }
        .welcome-text {
            margin: 5px 0 0;
            font-size: 1rem;
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 20px;
        }
        .avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            color: var(--green-dark);
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .admin-id {
            font-size: 0.9rem;
            color: white;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border-left: 5px solid var(--primary);
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--secondary);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin: 10px 0;
        }
        .stat-description {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        .progress-container {
            margin-top: 10px;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        .progress-bar {
            background: #e0e0e0;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: var(--primary);
            width: 0%;
        }
     .table-wrapper {
    max-height: 500px;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    margin-top: 0px;
}

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        }
        table thead tr {
            background: var(--primary);
            color: white;
        }
        table th, table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table tbody tr:hover {
            background-color: #f1f1f1;
        }
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .admin-info {
                align-self: flex-end;
            }
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="sidebar">
        <div class="logo-placeholder">
            <img src="isu-logo.png" alt="University Logo" onerror="this.src='fallback.png'">
        </div>

        <div class="sidebar-section-title">Navigation</div>
        <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="vouchers.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'vouchers.php' ? 'active' : '' ?>">
            <i class="fas fa-ticket-alt"></i> Voucher Management
        </a>
        <a href="students.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : '' ?>">
            <i class="fas fa-user-graduate"></i> Student Records
        </a>
        <a href="accounts.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'accounts.php' ? 'active' : '' ?>">
            <i class="fas fa-users-cog"></i> Manage Accounts
        </a>
        <a href="reports.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i> Reports
        </a>
        <div class="sidebar-section-title">Settings</div>
        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    <div class="main-content">
        <header class="main-header">
            <div class="header-content">
                <div class="left-section">
                    <h1 class="title">Isabela State University Voucher System</h1>
                    <p class="welcome-text">Welcome back!</p>
                </div>
                <div class="admin-info">
                    <div class="avatar">
                        <?php
                        $fullnameInitials = strtoupper($admin['fullname'][0]);
                        echo $fullnameInitials;
                        ?>
                    </div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>

        <div class="stats-container" id="stats-container">
            <div class="stat-card">
                <h3>No. of Students</h3>
                <div class="stat-value"><?= $students ?></div>
                <div class="stat-description">Total registered students</div>
            </div>
            <div class="stat-card">
                <h3>Total Vouchers</h3>
                <div class="stat-value"><?= $totalVouchers ?></div>
                <div class="stat-description">Vouchers issued</div>
            </div>
            <div class="stat-card">
                <h3>Available Vouchers</h3>
                <div class="stat-value"><?= $availableVouchers ?></div>
                <div class="stat-description">Still usable</div>
            </div>
            <div class="stat-card">
                <h3>Used Vouchers</h3>
                <div class="stat-value"><?= $usedVouchers ?></div>
                <div class="stat-description">Already redeemed</div>
                <div class="progress-container">
                    <div class="progress-label">
                        <span>Progress</span>
                        <span id="progress-label"><?= round($progressPercent, 1) ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $progressPercent ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>




<h2 style= color: var(--secondary);">Today's Voucher Redemptions</h2>
<div class="table-wrapper">

    <table>
        <thead>

            <tr>
                <th>Student Name</th>
                <th>Student ID</th>
                <th>Voucher Code</th>
                <th>Redeemed At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($dailyRedemptions && $dailyRedemptions->num_rows > 0): ?>
                <?php while($row = $dailyRedemptions->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['voucher_code']) ?></td>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['redeemed_at']))) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No voucher redemptions today.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ...other code... -->


</div>

</body>
</html>
