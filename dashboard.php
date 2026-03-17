<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];
$reportService = new ReportService($pdo);

try {
    $stats = $reportService->getDashboardStats();
    $students = $stats['total_students'];
    $availableVouchers = $stats['available_vouchers'];
    $usedVouchers = $stats['used_vouchers'];
    $totalVouchers = $availableVouchers + $usedVouchers;
    $progressPercent = ($totalVouchers > 0) ? ($usedVouchers / $totalVouchers) * 100 : 0;
    
    $dailyRedemptions = $reportService->getRedemptionReport('daily');
} catch (Exception $e) {
    $error = "Error loading dashboard: " . $e->getMessage();
    $dailyRedemptions = [];
}

$pageTitle = "System Dashboard";
include 'includes/header.php';
?>

<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-info">
            <h3>Registered Students</h3>
            <div class="stat-value"><?= number_format($students) ?></div>
        </div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(49, 130, 206, 0.1); color: #3182ce;">
            <i class="fas fa-ticket-alt"></i>
        </div>
        <div class="stat-info">
            <h3>Total Vouchers</h3>
            <div class="stat-value"><?= number_format($totalVouchers) ?></div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(237, 137, 54, 0.1); color: #ed8936;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3>Used Vouchers</h3>
            <div class="stat-value"><?= number_format($usedVouchers) ?></div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="stat-icon" style="background: rgba(128, 90, 213, 0.1); color: #805ad5;">
            <i class="fas fa-percent"></i>
        </div>
        <div class="stat-info" style="width: 100%;">
            <h3>Redemption Rate</h3>
            <div class="stat-value"><?= round($progressPercent, 1) ?>%</div>
            <div style="width: 100%; height: 6px; background: #edf2f7; border-radius: 3px; margin-top: 8px; overflow: hidden;">
                <div style="width: <?= $progressPercent ?>%; height: 100%; background: var(--accent-color); transition: width 1s ease-out;"></div>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 1.25rem; color: var(--text-main);">Today's Voucher Redemptions</h2>
        <span style="font-size: 0.875rem; color: var(--text-muted); background: #f7fafc; padding: 4px 12px; border-radius: 20px;">
            <i class="far fa-calendar-alt" style="margin-right: 6px;"></i>
            <?= date('M d, Y') ?>
        </span>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Voucher Code</th>
                    <th>Redeemed At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dailyRedemptions)): ?>
                    <?php foreach($dailyRedemptions as $row): ?>
                        <tr>
                            <td style="font-weight: 500;"><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><span style="background: #ebf8ff; color: #2b6cb0; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; font-family: monospace;"><?= htmlspecialchars($row['student_id']) ?></span></td>
                            <td><code><?= htmlspecialchars($row['voucher_code']) ?></code></td>
                            <td style="color: var(--text-muted); font-size: 0.9rem;">
                                <i class="far fa-clock" style="margin-right: 4px;"></i>
                                <?= htmlspecialchars(date('h:i A', strtotime($row['redeemed_at']))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            <i class="fas fa-inbox" style="display: block; font-size: 2rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                            No voucher redemptions recorded for today.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
