<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT fullname FROM adminstafflogs WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get filter and search inputs
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$whereClause = "";
$params = [];
$types = "";

// Search handling
if ($search) {
    $whereClause .= " AND (u.last_name LIKE ? OR u.first_name LIKE ? OR u.student_id LIKE ? OR v.voucher_code LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, array_fill(0, 4, $search_param));
    $types .= "ssss";
}

// Date range takes priority
if ($start_date && $end_date) {
    $whereClause .= " AND sv.redeemed_at BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
} else {
    switch ($filter) {
        case 'daily':
            $whereClause .= " AND sv.redeemed_at >= CURDATE()";
            break;
        case 'weekly':
            $whereClause .= " AND sv.redeemed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'monthly':
            $whereClause .= " AND sv.redeemed_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
            break;
        case 'yearly':
            $whereClause .= " AND sv.redeemed_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            break;
    }
}

$sql = "
SELECT 
  CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS student_name,
  u.student_id,
  v.voucher_code,
  sv.redeemed_at
FROM student_vouchers sv
JOIN users u ON sv.student_id = u.student_id
JOIN vouchers v ON sv.voucher_id = v.id
WHERE 1=1 $whereClause
ORDER BY sv.redeemed_at DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
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

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column; /* Stack sidebar and content vertically on smaller screens */
            }
            .sidebar {
                display: none; /* Hide sidebar on small screens */
            }
            .main-content {
                margin-left: 0; /* Reset the margin */
            }
        }

        /* Custom style for Export button */
        .export-btn {
            background-color: #0F9D58;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        /* Make the table container scrollable */
        .table-container {
            max-height: 80vh;
            overflow-y: auto;
            margin-top: 20px;
        }

        .export-btn-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
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
                    <h1 style="margin: 0; font-size: 1.8rem;">Redemption Reports</h1>
                    <p style="margin: 5px 0 0; font-size: 1rem;">View and export voucher usage reports</p>
                </div>
                <div class="admin-info">
                    <div class="avatar"><?= strtoupper($admin['fullname'][0]) ?></div>
                    <div class="fullname"><?= htmlspecialchars($admin['fullname']) ?></div>
                </div>
            </div>
        </header>

  <!-- Combined filter and export container -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin: 20px 0;">

  <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
    <label for="search">Search:</label>
    <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, ID, or voucher" style="padding: 6px;">

    <label for="filter">Filter:</label>
    <select name="filter" id="filter" onchange="clearDates()">
        <option value="">-- Select Filter --</option>
        <option value="daily" <?= $filter == 'daily' ? 'selected' : '' ?>>Daily</option>
        <option value="weekly" <?= $filter == 'weekly' ? 'selected' : '' ?>>Weekly</option>
        <option value="monthly" <?= $filter == 'monthly' ? 'selected' : '' ?>>Monthly</option>
        <option value="yearly" <?= $filter == 'yearly' ? 'selected' : '' ?>>Yearly</option>
    </select>

    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" onchange="clearFilter()" style="padding: 6px;">

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" onchange="clearFilter()" style="padding: 6px;">

    <button type="submit" style="background-color: #38A169; color: white; padding: 6px 12px; border: none; border-radius: 5px;">
        Apply Filters
    </button>

    <a href="reports.php" style="background: #ccc; padding: 6px 12px; border-radius: 5px; text-decoration: none; color: black;">
        Clear Filters
    </a>
</form>


    </form>

    <!-- Export Button -->
    <form method="post" action="export_reports.php">
        <input type="hidden" name="filter" value="<?= $filter ?>">
        <button type="submit" style="background-color: #0F9D58; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer;">
            Export to Excel
        </button>
    </form>
</div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Voucher Code</th>
                        <th>Date Redeemed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['voucher_code']) ?></td>
                               <td><?= date('Y-m-d H:i', strtotime($row['redeemed_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No data available for this filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<script>
function clearFilter() {	
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    if (start && end) {
        document.getElementById('filter').value = '';
    }
}

function clearDates() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
}
</script>
</body>
</html>
