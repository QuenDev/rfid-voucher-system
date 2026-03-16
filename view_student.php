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

if (!$admin) {
    echo "Admin data not found!";
    exit();
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid student ID!";
    exit();
}

$student_id = intval($_GET['id']);

// Fetch the student details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    echo "Student not found!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Student</title>
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
      .student-card {
    background: #f0fff4; /* Light green background like add_student */
    border-radius: 10px;
    padding: 30px;
    margin: auto;
    max-width: 600px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.student-info-form {
    display: flex;
    flex-direction: column;
}
.student-info-form label {
    font-weight: bold;
    margin-top: 10px;
    font-size: 14px;
    color: var(--secondary);
}
.student-value {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px 12px;
    margin-top: 4px;
    font-size: 15px;
    color: #333;
}
.student-picture {
    text-align: center;
    margin-bottom: 20px;
}
.student-picture img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border: 3px solid var(--primary);
    background: white;
    border-radius: 10px; /* slight rounding for a modern square, or remove if you want it sharp */
}

.back-link {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: var(--primary);
    color: white;
    border-radius: 6px;
    text-decoration: none;
 
}
.back-link:hover {
    background-color: var(--green-light);
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
                    <h1 style="margin: 0; font-size: 1.8rem;">View Student</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">Student Information</p>
                </div>

                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['fullname'][0]) ?></div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>
	<div class="student-card">
    <div class="student-picture">
        <?php if (!empty($student['picture'])): ?>
            <img src="uploads/<?= htmlspecialchars($student['picture']) ?>" alt="Student Picture">
        <?php else: ?>
            <img src="no-photo.png" alt="No Student Picture">
        <?php endif; ?>
    </div>

    <div class="student-info-form">
        <label>RFID:</label>
        <div class="student-value"><?= htmlspecialchars($student['rfid']) ?></div>

        <label>Student ID:</label>
        <div class="student-value"><?= htmlspecialchars($student['student_id']) ?></div>

        <label>Last Name:</label>
        <div class="student-value"><?= htmlspecialchars($student['last_name']) ?></div>

        <label>First Name:</label>
        <div class="student-value"><?= htmlspecialchars($student['first_name']) ?></div>

        <label>Middle Name:</label>
        <div class="student-value"><?= htmlspecialchars($student['middle_name']) ?></div>

        <label>Sex:</label>
      <div class="student-value"><?= $student['sex'] === 'F' ? 'Female' : ($student['sex'] === 'M' ? 'Male' : 'N/A') ?></div>

        <label>C	ourse:</label>
        <div class="student-value"><?= htmlspecialchars($student['course']) ?></div>

        <label>Year:</label>
        <div class="student-value"><?= htmlspecialchars($student['year']) ?></div>

        <label>Section:</label>
        <div class="student-value"><?= htmlspecialchars($student['section']) ?></div>

        <a href="students.php" class="back-link">← Back to Student List</a>
    </div>
</div>




    </div>
</div>

</body>
</html>
