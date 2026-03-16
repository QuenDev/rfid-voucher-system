<?php
session_start();
require "config.php";

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

if (!$admin) {
    echo "Admin data not found!";
    exit();
}

// Handle search
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = "%" . $_GET['search'] . "%";
    $search_query = "WHERE username LIKE ? OR fullname LIKE ? OR office LIKE ? OR role LIKE ?";
}

$query = "SELECT * FROM adminstafflogs $search_query ORDER BY id DESC";
$stmt = $conn->prepare($query);

if ($search_query) {
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Handle delete
if (isset($_POST['confirm_delete'])) {
    $account_id = $_POST['account_id'];
    $delete_stmt = $conn->prepare("DELETE FROM adminstafflogs WHERE id = ?");
    $delete_stmt->bind_param("i", $account_id);
    if ($delete_stmt->execute()) {
        $_SESSION['delete_success'] = "Account deleted successfully!";
    } else {
        $_SESSION['delete_error'] = "Failed to delete account.";
    }
    $delete_stmt->close();
    header("Location: accounts.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>Manage Accounts</title>
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
    position: fixed; /* Fix the sidebar to the left */
    top: 0;
    left: 0;
    width: 240px;
    height: 100vh;
    background: linear-gradient(to bottom, var(--primary), var(--green-dark));
    color: white;
    padding-top: 20px;
    z-index: 100; /* Ensure the sidebar stays on top */
    overflow-y: auto; /* Allow the sidebar to scroll if needed */
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
    margin-left: 240px; /* Push content to the right of the fixed sidebar */
    padding: 20px;
    width: 100%;
    overflow-y: auto; /* Ensure content is scrollable */
}

.main-header {
    background: linear-gradient(to right, var(--primary), var(--green-dark));
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: none; /* Remove box effect if any */
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

.admin-id {
    font-size: 0.9rem;
    color: white;
}
        .upload-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f0fdf4;
            border: 1px solid #c6f6d5;
            border-radius: 10px;
        }
        .upload-section input[type="file"] {
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #38A169;
            color: white;
        }
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0px;
    background: #fff;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

th {
    background: #38A169;
    color: white;
    position: sticky;
    top: 0;
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Manage Accounts</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">List of all admin/staff accounts</p>
                </div>
                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['fullname'][0]) ?></div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>

        <!-- Search Form -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin: 20px 0;">
    <form method="GET" style="display: flex; align-items: center; gap: 8px; margin: 0;">
        <input type="text" name="search" placeholder="Search accounts..." value="<?= $_GET['search'] ?? '' ?>" style="padding: 8px;">
        <button type="submit" style="padding: 8px 14px; background: #38A169; color: white; border: none; border-radius: 5px;">Search</button>
        <a href="accounts.php" style="padding: 8px 14px; background: #718096; color: white; border: none; border-radius: 5px; text-decoration: none;">Clear</a>
    </form>
    <a href="add_account.php" style="padding: 8px 14px; background: #0F9D58; color: white; border: none; border-radius: 5px; text-decoration: none; font-size: 1rem;">+ Add Account</a>
</div>

        <?php if (isset($_SESSION['delete_success'])): ?>
            <div style="background: #d4edda; padding: 10px; color: #155724;">
                <?= $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?>
            </div>
        <?php elseif (isset($_SESSION['delete_error'])): ?>
            <div style="background: #f8d7da; padding: 10px; color: #721c24;">
                <?= $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Office/Department</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0):
                    while ($row = mysqli_fetch_assoc($result)):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['username'])) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['fullname'])) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['office'])) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['role'])) ?></td>
                        <td>
                            <a href="edit_account.php?id=<?= $row['id'] ?>" style="text-decoration: none; color: #007bff;">✎ Edit</a>
                            
                      
                            <form method="POST" action="accounts.php" style="display: inline;">
                                <input type="hidden" name="account_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="confirm_delete" style="color: red; background: none; border: none; cursor: pointer;" onclick="return confirm('Are you sure you want to delete this account?')">🗑 Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="6">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>