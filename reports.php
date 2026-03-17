<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];
$reportService = new ReportService($pdo);

// Get filter and search inputs
$filter = $_GET['filter'] ?? 'daily';
$search = $_GET['search'] ?? '';

try {
    $report_data = $reportService->getRedemptionReport($filter);
    
    // Manual search filtering
    if ($search) {
        $report_data = array_filter($report_data, function($row) use ($search) {
            return strpos(strtolower($row['student_name']), strtolower($search)) !== false || 
                   strpos(strtolower($row['student_id']), strtolower($search)) !== false ||
                   strpos(strtolower($row['voucher_code']), strtolower($search)) !== false;
        });
    }
} catch (Exception $e) {
    $error = "Error generating report: " . $e->getMessage();
    $report_data = [];
}

$pageTitle = "Redemption Reports";
include 'includes/header.php';
?>

<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- Filters & Actions -->
    <div class="card" style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <form method="GET" style="flex: 1; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div style="position: relative; flex: 1; min-width: 200px;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search student or code..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.75rem;">
                </div>

                <select name="filter" id="filter" class="form-control" style="width: auto; min-width: 140px;" onchange="if(this.value!='') { document.getElementById('start_date').value=''; document.getElementById('end_date').value=''; }">
                    <option value="">Quick Presets</option>
                    <option value="daily" <?= $filter == 'daily' ? 'selected' : '' ?>>Today</option>
                    <option value="weekly" <?= $filter == 'weekly' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="monthly" <?= $filter == 'monthly' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="yearly" <?= $filter == 'yearly' ? 'selected' : '' ?>>This Year</option>
                </select>

                <div style="display: flex; align-items: center; gap: 0.5rem; background: #f7fafc; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">From:</label>
                    <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" class="form-control" style="padding: 4px; border: none; background: transparent; width: auto;" onchange="if(this.value!='') document.getElementById('filter').value=''">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">To:</label>
                    <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" class="form-control" style="padding: 4px; border: none; background: transparent; width: auto;" onchange="if(this.value!='') document.getElementById('filter').value=''">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Generate</button>
                    <a href="reports.php" class="btn" style="background: #edf2f7; color: var(--text-main);">Reset</a>
                </div>
            </form>

            <form method="POST" action="export_reports.php">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                <button type="submit" class="btn" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </button>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 250px;">Student Name</th>
                        <th style="width: 150px;">Student ID</th>
                        <th style="width: 180px;">Voucher Code</th>
                        <th style="text-align: right;">Date Redeemed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($report_data)): ?>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($row['student_name']) ?></td>
                                <td style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><code style="background: #ebf8ff; color: #2b6cb0; padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= htmlspecialchars($row['voucher_code']) ?></code></td>
                                <td style="text-align: right; color: var(--text-muted); font-size: 0.9rem;">
                                    <span title="<?= $row['redeemed_at'] ?>"><?= date('M d, Y • h:i A', strtotime($row['redeemed_at'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                <i class="fas fa-file-invoice" style="display: block; font-size: 3rem; margin-bottom: 1rem; opacity: 0.1;"></i>
                                No redemption records found for the selected criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
code { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
</style>

<?php include 'includes/footer.php'; ?>
