<?php
session_start();
require "config.php";

// Authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin username
$stmt = $conn->prepare("SELECT username FROM adminstafflogs WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) {
    echo "Admin data not found!";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password_input = trim($_POST['password']);
    $office = trim($_POST['office']); // Getting the office/department value
    $role = trim($_POST['role']);

    // Automatically generate password if not provided
    if (empty($password_input)) {
        $password_input = bin2hex(random_bytes(4)); // 8-character hex string
        $_SESSION['generated_password'] = $password_input;
    }

    // Generate password hash
    $password = password_hash($password_input, PASSWORD_DEFAULT);

    // Get the current timestamp
    $timestamp = date("Y-m-d H:i:s");

    try {
        // Insert the new user into the database with the additional office/department field
        $stmt = $conn->prepare("INSERT INTO adminstafflogs (username, fullname, password, office, role, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $fullname, $password, $office, $role, $timestamp);
        $stmt->execute();

        $_SESSION['add_success'] = "Account successfully created!";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $_SESSION['add_error'] = "Username already exists. Please choose a different one.";
        } else {
            $_SESSION['add_error'] = "Database error: " . $e->getMessage();
        }
    }

    header("Location: add_account.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Account</title>
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Add Account</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">Create a new admin or staff account</p>
                </div>
                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['username'][0]) ?></div>
                    <div class="username"><?= htmlspecialchars($admin['username']) ?></div>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['add_success'])): ?>
            <p style="color: green; font-weight: bold;"><?= $_SESSION['add_success'] ?></p>
            <?php if (isset($_SESSION['generated_password'])): ?>
                <p style="color: blue;">Generated password: <strong><?= htmlspecialchars($_SESSION['generated_password']) ?></strong></p>
                <?php unset($_SESSION['generated_password']); ?>
            <?php endif; ?>
            <?php unset($_SESSION['add_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['add_error'])): ?>
            <p style="color: red; font-weight: bold;"><?= $_SESSION['add_error'] ?></p>
            <?php unset($_SESSION['add_error']); ?>
        <?php endif; ?>

 <div class="upload-section">
    <form method="POST" action="add_account.php">
        <label for="fullname">Full Name:</label><br>
        <input type="text" name="fullname" id="fullname" required><br><br>

        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required><br><br>

        <label for="password">Password:</label><br>
        <input type="text" name="password" id="password" value="<?= isset($_SESSION['generated_password']) ? htmlspecialchars($_SESSION['generated_password']) : '' ?>" readonly><br><br>
        <small>Note: A random password will be generated automatically upon account creation.</small><br><br>

        <label for="office">Office/Department:</label><br>
        <input type="text" name="office" id="office" required><br><br>

        <label for="role">Role:</label><br>
        <select name="role" id="role" required>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select><br><br>

<div style="display: flex; gap: 10px;">
    <button type="submit">Create Account</button>
    <a href="accounts.php" style="display: inline-block; background: #718096; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; border: none; text-align: center; line-height: 1.5;">Back</a>
</div>
    </form>
</div>
    </div>
</div>
</body>
</html>