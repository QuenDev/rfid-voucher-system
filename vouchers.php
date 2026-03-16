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

	// Handle voucher delete
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_voucher'])) {
		$voucher_id = $_POST['voucher_id'];
		$stmt = $conn->prepare("DELETE FROM vouchers WHERE id = ?");
		$stmt->bind_param("i", $voucher_id);
		if ($stmt->execute()) {
			$_SESSION['success_message'] = "Voucher #$voucher_id deleted successfully.";
		} else {
			$_SESSION['error_message'] = "Failed to delete voucher.";
		}
		$stmt->close();
		header("Location: vouchers.php");
		exit();
	}

	// Handle voucher update
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_voucher'])) {
		$voucher_id = $_POST['voucher_id'];
		$voucher_code = $_POST['voucher_code'];
		$office = $_POST['office_department'];
		$minutes = $_POST['minutes_valid'];

		$stmt = $conn->prepare("UPDATE vouchers SET voucher_code = ?, office_department = ?, minutes_valid = ? WHERE id = ?");
		$stmt->bind_param("ssii", $voucher_code, $office, $minutes, $voucher_id);
		if ($stmt->execute()) {
			$_SESSION['success_message'] = "Voucher #$voucher_id updated successfully.";
		} else {
			$_SESSION['error_message'] = "Failed to update voucher.";
		}
		$stmt->close();
		header("Location: vouchers.php");
		exit();
	}

	// Filters (if any)
	$search = $_GET['search'] ?? '';
	$status_filter = $_GET['status'] ?? '';
	$from_date = $_GET['from_date'] ?? '';
	$to_date = $_GET['to_date'] ?? '';
	$edit_id = $_GET['edit_id'] ?? null;


	// Base query setup for filters
	$where = "WHERE 1";
	$params = [];
	$types = "";

	// Search filter
	if (!empty($search)) {
		$where .= " AND (voucher_code LIKE ? OR office_department LIKE ?)";
		$params[] = "%$search%";
		$params[] = "%$search%";
		$types .= "ss";
	}

	// Status filter
	if (!empty($status_filter)) {
		$where .= " AND status = ?";
		$params[] = $status_filter;
		$types .= "s";
	}

	// Date range filter
	if (!empty($from_date) && !empty($to_date)) {
		$where .= " AND DATE(date_issued) BETWEEN ? AND ?";
		$params[] = $from_date;
		$params[] = $to_date;
		$types .= "ss";
	} elseif (!empty($from_date) || !empty($to_date)) {
		$_SESSION['error_message'] = "Please select both From and To dates.";
	}

	// Fetch all records query
	$query = "SELECT * FROM vouchers $where ORDER BY date_issued DESC";
	$stmt = $conn->prepare($query);
	if (!empty($params)) {
		$stmt->bind_param($types, ...$params);
	}
	$stmt->execute();
	$result = $stmt->get_result();
	

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
						<h1 style="margin: 0; font-size: 1.8rem;">Voucher Management</h1>
						<p style="margin: 5px 0 0; font-size: 1rem;">Manage and import voucher data</p>
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
				<form action="http://localhost:8080/Voucher_system/import_voucher.php" method="POST" enctype="multipart/form-data">
					<label for="excel">Import Excel File:</label><br>
					<input type="file" name="excel_file" id="excel" accept=".xls,.xlsx" required>
					<br><br>
					<button type="submit">Upload</button>
				</form>
			</div>

			<?php if (isset($_SESSION['import_success'])): ?>
				<p style="color: green; font-weight: bold;"><?= $_SESSION['import_success'] ?></p>
				<?php unset($_SESSION['import_success']); ?>
			<?php endif; ?>
			<?php if (isset($_SESSION['import_error'])): ?>
				<p style="color: red; font-weight: bold;"><?= $_SESSION['import_error'] ?></p>
				<?php unset($_SESSION['import_error']); ?>
			<?php endif; ?>
			<?php if (isset($_SESSION['success_message'])): ?>
				<p style="color: green; font-weight: bold;"><?= $_SESSION['success_message'] ?></p>
				<?php unset($_SESSION['success_message']); ?>
			<?php endif; ?>
			<?php if (isset($_SESSION['error_message'])): ?>
				<p style="color: red; font-weight: bold;"><?= $_SESSION['error_message'] ?></p>
				<?php unset($_SESSION['error_message']); ?>
			<?php endif; ?>
<form method="GET" style="display: flex; justify-content: space-between; flex-wrap: wrap; align-items: center; gap: 10px; margin: 20px 0;">
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <!-- Search Field -->
        <input type="text" name="search" placeholder="Search voucher or office..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" />
        
        <!-- Status Filter -->
        <select name="status">
            <option value="">All Status</option>
            <option value="Available" <?= (isset($_GET['status']) && $_GET['status'] == 'Available') ? 'selected' : '' ?>>Available</option>
            <option value="Used" <?= (isset($_GET['status']) && $_GET['status'] == 'Used') ? 'selected' : '' ?>>Used</option>
        </select>

        <!-- Date Filter (Only visible if both dates are set) -->
        <input type="date" name="from_date" value="<?= isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']) : '' ?>" />
        <input type="date" name="to_date" value="<?= isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']) : '' ?>" />
        
        <!-- Filter Button -->
        <button type="submit" style="background-color: #38A169; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer;">
            Filter
        </button>

        <!-- Clear Filters Button -->
        <a href="vouchers.php" style="text-decoration: none;">
            <button type="button" style="background-color: #718096; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer;">
                Clear Filters
            </button>
        </a>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="add.php" style="text-decoration: none;">
            <button type="button" style="background-color: #0F9D58; color: white; padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer; font-weight: 500;">
                + Add Voucher
            </button>
        </a>
    </div>
</form>


	<div style="max-height:90vh; overflow-y: auto; border: 0px solid #ddd; border-radius: 1px;">
		<table style="width: 100%; table-layout: fixed; border-collapse: collapse;">
			<thead>
				<tr>
					<th>ID</th>
					<th>Voucher Code</th>
					<th>Office</th>
					<th>Date Issued</th>
					<th>Minutes Valid</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ($result && $result->num_rows > 0):
					while ($voucher = $result->fetch_assoc()):
						if ($edit_id == $voucher['id']): ?>
							<tr>
								<form method="POST" action="vouchers.php">
									<td><?= $voucher['id'] ?><input type="hidden" name="voucher_id" value="<?= $voucher['id'] ?>"></td>
									<td><input type="text" name="voucher_code" value="<?= htmlspecialchars($voucher['voucher_code']) ?>" required></td>
									<td><input type="text" name="office_department" value="<?= htmlspecialchars($voucher['office_department']) ?>" required></td>
									<td><?= htmlspecialchars($voucher['date_issued']) ?></td>
									<td><input type="number" name="minutes_valid" value="<?= $voucher['minutes_valid'] ?>" required></td>
									<td><?= htmlspecialchars($voucher['status']) ?></td>
									<td>
									   <button type="submit" name="update_voucher" style="background-color: #38A169; color: white; border: none; padding: 5px 10px;">Save</button>
									   <a href="vouchers.php" style="color: red; margin-left: 10px;">Cancel</a>
									</td>
								</form>
							</tr>
						<?php else: ?>
							<tr>
								<td><?= htmlspecialchars($voucher['id']) ?></td>
								<td><?= htmlspecialchars($voucher['voucher_code']) ?></td>
								<td><?= htmlspecialchars($voucher['office_department']) ?></td>
								<td><?= htmlspecialchars($voucher['date_issued']) ?></td>
								<td><?= htmlspecialchars($voucher['minutes_valid']) ?></td>
								<td>
									<?php
									$status = strtolower($voucher['status']);
									echo $status === 'used'
									   ? '<span style="color: white; background-color: #e53e3e; padding: 4px 8px; border-radius: 5px;">USED</span>'
									   : '<span style="color: white; background-color: #38A169; padding: 4px 8px; border-radius: 5px;">AVAILABLE</span>';
									?>
								</td>
								<td>
									<a href="vouchers.php?edit_id=<?= $voucher['id'] ?>&<?= http_build_query($_GET) ?>" style="text-decoration: none; color: #007bff;">✎ Edit</a>
									<form method="POST" action="vouchers.php" style="display:inline;">
									   <input type="hidden" name="voucher_id" value="<?= $voucher['id'] ?>">
									   <button type="submit" name="delete_voucher" style="color: red; background: none; border: none; cursor: pointer;" onclick="return confirm('Are you sure you want to delete this voucher?')">🗑 Delete</button>
									</form>
								</td>
							</tr>
						<?php endif;
					endwhile;
				else: ?>
					<tr>
						<td colspan="7">No vouchers found.</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
