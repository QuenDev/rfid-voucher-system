<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="isu-logo.png" alt="ISU Logo" class="sidebar-logo" onerror="this.src='fallback.png'">
        <h2 style="font-size: 1.1rem; color: white;">ISU Voucher System</h2>
    </div>
    <ul class="nav-links">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="vouchers.php" class="nav-link <?php echo $current_page == 'vouchers.php' ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i>
                <span>Voucher Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="students.php" class="nav-link <?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Student Records</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="accounts.php" class="nav-link <?php echo $current_page == 'accounts.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i>
                <span>Manage Accounts</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="reports.php" class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Reports</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-footer" style="padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <a href="../server/logout.php" class="nav-link" style="color: #feb2b2;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
