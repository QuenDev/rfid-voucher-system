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
$stmt = $conn->prepare("SELECT fullname FROM adminstafflogs WHERE id = ?");
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
    $rfid = trim($_POST['rfid']);
    $student_id = trim($_POST['student_id']);
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $sex = $_POST['sex'];
    $course = trim($_POST['course']);
    $year = intval($_POST['year']);
    $section = trim($_POST['section']);
    
    $picture = ""; // default if no upload

    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = basename($_FILES['picture']['name']);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = uniqid() . "." . $ext;

        $uploadPath = $uploadDir . $newFilename;
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $uploadPath)) {
            $picture = $newFilename;
        }
    }	

    try {
        $stmt = $conn->prepare("INSERT INTO users (rfid, student_id, last_name, first_name, middle_name, sex, course, year, section, picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssiss", 
            $rfid, 
            $student_id, 
            $last_name, 
            $first_name, 
            $middle_name, 
            $sex, 
            $course, 
            $year, 
            $section, 
            $picture
        );
        $stmt->execute();

        $_SESSION['add_success'] = "Student successfully added!";
    } catch (mysqli_sql_exception $e) {
        $_SESSION['add_error'] = "Database error: " . $e->getMessage();
    }

    header("Location: add_student.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
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
        .user-info {
            padding: 20px;
            text-align: center;
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Add Student</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">Manually add a new student</p>
                </div>

                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['fullname'][0]) ?></div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>

        <?php if (isset($_SESSION['add_success'])): ?>
            <p style="color: green; font-weight: bold;"><?= $_SESSION['add_success'] ?></p>
            <?php unset($_SESSION['add_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['add_error'])): ?>
            <p style="color: red; font-weight: bold;"><?= $_SESSION['add_error'] ?></p>
            <?php unset($_SESSION['add_error']); ?>
        <?php endif; ?>

        <div class="upload-section">
           <form method="POST" action="add_student.php" enctype="multipart/form-data">
    <label>RFID:</label><br>
    <input type="text" name="rfid" required><br><br>

    <label>Student ID:</label><br>
    <input type="text" name="student_id" required><br><br>

    <label>Last Name:</label><br>
    <input type="text" name="last_name" required><br><br>

    <label>First Name:</label><br>
    <input type="text" name="first_name" required><br><br>

    <label>Middle Name:</label><br>
    <input type="text" name="middle_name"><br><br>

    <label>Sex:</label><br>
    <select name="sex" required>
        <option value="M">Male</option>
        <option value="F">Female</option>
    </select><br><br>

    <label>Course:</label><br>
    <input type="text" name="course" required><br><br>

    <label>Year:</label><br>
    <input type="number" name="year" min="1" max="4" required><br><br>

    <label>Section:</label><br>
    <input type="text" name="section" required><br><br>

    <label>Profile Picture:</label><br>
    <input type="file" name="picture"><br><br>

    <button type="submit" style="background-color: #38A169; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
        Add Student
    </button>
</form>

        </div>
    </div>
</div>
</body>
</html>
