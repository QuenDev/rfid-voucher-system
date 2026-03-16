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

try {
    // Build the query
$query = "SELECT * FROM users";

// Only apply the search filter if the search field is provided
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $query .= " WHERE 
        student_id LIKE '%$search%' OR
        rfid LIKE '%$search%' OR
        first_name LIKE '%$search%' OR
        last_name LIKE '%$search%' OR
        middle_name LIKE '%$search%' OR
        course LIKE '%$search%' OR
        section LIKE '%$search%' OR
        year LIKE '%$search%' OR
        sex LIKE '%$search%'";
}


    // Now execute it
    $result = $conn->query($query);
} catch (Exception $e) {
    $_SESSION['import_error'] = "Database error: " . $e->getMessage();
}


// Handle deletion of the student if the form is submitted
if (isset($_POST['confirm_delete'])) {
    // Get the student ID from the POST request
    $student_id = $_POST['student_id'];
    
    // Prepare and execute the DELETE query
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $student_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['delete_success'] = "Student has been deleted successfully!";
    } else {
        $_SESSION['delete_error'] = "Failed to delete student.";
    }
    
    // Close the statement
    $delete_stmt->close();
    
    // Redirect back to the students page
    header("Location: students.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Records</title>
	   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Student Records</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">List of all enrolled students</p>
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
 
    

       <div class="upload-section">
            <form action="http://localhost:8080/Voucher_system/import_student.php" method="POST" enctype="multipart/form-data">
                <label for="excel">Import Excel File:</label><br>
                <input type="file" name="excel_file" id="excel" accept=".xls,.xlsx" required>
                <br><br>
                <button type="submit">Upload</button>
            </form>
        </div>	
		
<form method="GET" style="display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; gap: 10px; margin: 20px 0;">
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <input 
            type="text" 
            name="search" 
            placeholder="Search students..." 
            value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
            style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px;">
        
        <button type="submit" style="background-color: #38A169; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer;">
            Search
        </button>

        <a href="students.php" style="text-decoration: none;">
            <button type="button" style="background-color: #718096; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer;">
                Clear
            </button>
        </a>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="add_student.php" style="text-decoration: none;">
            <button type="button" style="background-color: #0F9D58; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer; font-weight: 500;">
                + Add Student
            </button>
        </a>
    </div>
</form>



        <?php if (isset($_SESSION['import_success'])): ?>
            <div style="background: #d4edda; padding: 10px; margin-top: 15px; color: #155724;">
                <?= $_SESSION['import_success']; unset($_SESSION['import_success']); ?>
            </div>
        <?php elseif (isset($_SESSION['import_error'])): ?>
            <div style="background: #f8d7da; padding: 10px; margin-top: 15px; color: #721c24;">
                <?= $_SESSION['import_error']; unset($_SESSION['import_error']); ?>
            </div>
			 <?php elseif (isset($_SESSION['delete_success'])): ?>
            <div style="background: #d4edda; padding: 10px; margin-top: 15px; color: #155724;">
                <?= $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?>
            </div>
        <?php elseif (isset($_SESSION['delete_error'])): ?>
            <div style="background: #f8d7da; padding: 10px; margin-top: 15px; color: #721c24;">
                <?= $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?>
            </div>
        <?php endif; ?>
		
		
<div style="max-height:90vh; overflow-y: auto; border: 0px solid #ddd; border-radius: 1px;">
    <table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>RFID</th>
                    <th>Student ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Sex</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Section</th>
					<th>Action</th>


					
                </tr>
            </thead>

			
			</div>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($student = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['id']) ?></td>
                            <td><?= htmlspecialchars($student['rfid']) ?></td>	
                            <td><?= htmlspecialchars($student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['last_name']) ?></td>
                            <td><?= htmlspecialchars($student['first_name']) ?></td>
                            <td><?= htmlspecialchars($student['middle_name']) ?></td>
                            <td><?= htmlspecialchars($student['sex']) ?></td>
                            <td><?= htmlspecialchars($student['course']) ?></td>
                            <td><?= htmlspecialchars($student['year']) ?></td>
                            <td><?= htmlspecialchars($student['section']) ?></td>
					<td>
    <a href="view_student.php?id=<?= $student['id'] ?>" style="text-decoration: none; color: #28a745;">👁 View</a> 
    <a href="edit_student.php?id=<?= $student['id'] ?>" style="text-decoration: none; color: #007bff;">✎ Edit</a>
      <form method="POST" action="students.php" style="display:inline;">
        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
        <button type="submit" name="confirm_delete" style="color: red; background: none; border: none; cursor: pointer;" onclick="return confirm('Are you sure you want to delete this student?')">🗑 Delete</button>
    </form>
</td>

                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="11">No student records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
	  
</div>
</body>
</html>
