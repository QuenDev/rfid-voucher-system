<?php
session_start();
require "config.php";

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$edit_id) {
    echo "Invalid user ID.";
    exit();
}


$stmt = $conn->prepare("SELECT fullname FROM adminstafflogs WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch account to edit
$stmt = $conn->prepare("SELECT * FROM adminstafflogs WHERE id = ?");
$stmt->bind_param("i", $edit_id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$account) {
    echo "Account not found!";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $office = trim($_POST['office']);
    $role = trim($_POST['role']);
    $new_password = trim($_POST['password']);

    try {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE adminstafflogs SET fullname = ?, username = ?, password = ?, office = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $fullname, $username, $hashed, $office, $role, $edit_id);
        } else {
            $stmt = $conn->prepare("UPDATE adminstafflogs SET fullname = ?, username = ?, office = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $fullname, $username, $office, $role, $edit_id);
        }
        $stmt->execute();
        $_SESSION['edit_success'] = "Account updated successfully!";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $_SESSION['edit_error'] = "Username already exists. Please choose a different one.";
        } else {
            $_SESSION['edit_error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: edit_account.php?id=$edit_id");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(to bottom, var(--primary), var(--green-dark));
            color: white;
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
        .main-content {
            padding: 20px;
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
        .upload-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f0fdf4;
            border: 1px solid #c6f6d5;
            border-radius: 10px;
        }
        .upload-section input[type="text"],
        .upload-section input[type="password"],
        .upload-section select {
            margin-top: 10px;
            width: 200px;
        }
        .upload-section button {
            background-color: #38A169;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            .sidebar {
                display: none;
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
                <div>
                    <h1 style="margin: 0; font-size: 1.8rem;">Edit Account</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">Modify existing admin or staff account</p>
                </div>
                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['fullname'][0]) ?></div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['edit_success'])): ?>
            <p style="color: green; font-weight: bold;"><?= $_SESSION['edit_success'] ?></p>
            <?php unset($_SESSION['edit_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['edit_error'])): ?>
            <p style="color: red; font-weight: bold;"><?= $_SESSION['edit_error'] ?></p>
            <?php unset($_SESSION['edit_error']); ?>
        <?php endif; ?>

        <div class="upload-section">
            <form method="POST">
                <label for="fullname">Full Name:</label><br>
                <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($account['fullname']) ?>" required><br><br>

                <label for="username">Username:</label><br>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($account['username']) ?>" required><br><br>

                <label for="password">New Password (leave blank to keep current):</label><br>
                <input type="password" name="password" id="password"><br><br>

                <label for="office">Office/Department:</label><br>
                <input type="text" name="office" id="office" value="<?= htmlspecialchars($account['office']) ?>" required><br><br>

                <label for="role">Role:</label><br>
                <select name="role" id="role" required>
                    <option value="admin" <?= $account['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="staff" <?= $account['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                </select><br><br>

                <button type="submit">Update Account</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
