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

// Get the student ID from URL
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

// Initialize messages
$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rfid = $_POST['rfid'];
    $student_id_num = $_POST['student_id'];
    $last_name = $_POST['last_name'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $sex = $_POST['sex'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $section = $_POST['section'];

    // Handle picture upload
if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $new_filename = uniqid() . "_" . basename($_FILES["picture"]["name"]);
    $target_file = $upload_dir . $new_filename;

    if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
        // Remove old picture if it exists and is not empty
        if (!empty($student['picture']) && file_exists($upload_dir . $student['picture'])) {
            unlink($upload_dir . $student['picture']);
        }
        $picture = $new_filename; 
    } else {
        $error = "Error uploading picture!";
    }
} else {
    $picture = $student['picture'];
}

    if (!$error) {
      
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (rfid = ? OR student_id = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $rfid, $student_id_num, $student_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        if ($check_stmt->num_rows > 0) {
            $error = "RFID or Student ID already exists!";
        }
        $check_stmt->close();
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE users SET rfid=?, student_id=?, last_name=?, first_name=?, middle_name=?, sex=?, course=?, year=?, section=?, picture=? WHERE id=?");
        $stmt->bind_param("ssssssssssi", $rfid, $student_id_num, $last_name, $first_name, $middle_name, $sex, $course, $year, $section, $picture, $student_id);

        if ($stmt->execute()) {
            $success = "Student updated successfully!";
           
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
        } else {
            $error = "Error updating student!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
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
        .student-card {
            background: #f0fff4;
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
        .student-info-form input, .student-info-form select {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 4px;
            font-size: 15px;
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
            border-radius: 10px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .save-btn, .cancel-btn {
            flex: 1;
            padding: 1px;
        
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .save-btn {
            background-color: var(--primary);
            color: white;
        }
        .save-btn:hover {
            background-color: var(--green-light);
        }
        .cancel-btn {
            background-color: #ccc;
            color: black;
            text-decoration: none;
            text-align: center;
            line-height: 30px;
        }
        .error-msg {
            background-color: #ffe6e6;
            color: #c00;
            border: 1px solid #c00;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-msg {
            background-color: #e6ffed;
            color: #2e7d32;
            border: 1px solid #2e7d32;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Edit Student</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">Modify Student Information</p>
                </div>
                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['fullname'][0]) ?></div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>

        <div class="student-card">
            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="success-msg"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="student-info-form">
                <div class="student-picture">
                    <?php if (!empty($student['picture'])): ?>
                        <img src="uploads/<?= htmlspecialchars($student['picture']) ?>" alt="Student Picture">
                    <?php else: ?>
                        <img src="no-photo.png" alt="No Student Picture">
                    <?php endif; ?>
                </div>

                <label>Upload New Picture (optional):</label>
                <input type="file" name="picture" accept="image/*">

                <label>RFID:</label>
                <input type="text" name="rfid" value="<?= htmlspecialchars($student['rfid']) ?>" required>

                <label>Student ID:</label>
                <input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id']) ?>" required>

                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" required>

                <label>First Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" required>

                <label>Middle Name:</label>
                <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']) ?>">

                <label>Sex:</label>
                <select name="sex" required>
                    <option value="M" <?= $student['sex'] == 'M' ? 'selected' : '' ?>>Male</option>
                    <option value="F" <?= $student['sex'] == 'F' ? 'selected' : '' ?>>Female</option>
                </select>

                <label>Course:</label>
                <input type="text" name="course" value="<?= htmlspecialchars($student['course']) ?>" required>

                <label>Year:</label>
                <input type="text" name="year" value="<?= htmlspecialchars($student['year']) ?>" required>

                <label>Section:</label>
                <input type="text" name="section" value="<?= htmlspecialchars($student['section']) ?>" required>

                <div class="form-actions">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <a href="students.php" class="cancel-btn">Back<a/>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
