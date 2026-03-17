<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Security Guard
requireLogin();

$voucherService = new VoucherService($pdo);
$admin_id = $_SESSION['admin_id'];

// Handle voucher delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_voucher'])) {
    $voucher_id = $_POST['voucher_id'];
    if ($voucherService->delete($voucher_id)) {
        $_SESSION['success_message'] = "Voucher #$voucher_id deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to delete voucher.";
    }
    header("Location: vouchers.php");
    exit();
}

// Handle voucher update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_voucher'])) {
    $voucher_id = $_POST['voucher_id'];
    $voucher_code = $_POST['voucher_code'];
    $office = $_POST['office_department'];
    $minutes = $_POST['minutes_valid'];

    if ($voucherService->update($voucher_id, $voucher_code, $office, $minutes)) {
        $_SESSION['success_message'] = "Voucher #$voucher_id updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update voucher.";
    }
    header("Location: vouchers.php");
    exit();
}

// Filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$search = $filters['search'];
$status_filter = $filters['status'];
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$edit_id = $_GET['edit_id'] ?? null;

// Temporary: Support date filters in service if needed, but for now we'll use GetAll
$voucher_list = $voucherService->getAll($filters);

$pageTitle = "Voucher Management";
include 'includes/header.php';
?>

<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- Filters Bar -->
    <div class="card" style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 0;">
            <form method="GET" style="flex: 1; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div style="position: relative; flex: 1; min-width: 250px;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search code or office..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.75rem;">
                </div>
                
                <select name="status" class="form-control" style="width: auto; min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="Available" <?= $status_filter == 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="Used" <?= $status_filter == 'Used' ? 'selected' : '' ?>>Used</option>
                </select>

                <div style="display: flex; align-items: center; gap: 0.5rem; background: #f7fafc; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid #e2e8f0;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">From:</label>
                    <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="form-control" style="padding: 4px; border: none; background: transparent; width: auto;">
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">To:</label>
                    <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="form-control" style="padding: 4px; border: none; background: transparent; width: auto;">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="vouchers.php" class="btn" style="background: #edf2f7; color: var(--text-main);">Reset</a>
                </div>
            </form>

            <div style="display: flex; gap: 0.75rem;">
                <button onclick="document.getElementById('import-section').classList.toggle('hidden')" class="btn" style="background: #ebf8ff; color: #3182ce;">
                    <i class="fas fa-upload"></i> Import
                </button>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Voucher
                </a>
            </div>
        </div>

        <div id="import-section" class="hidden" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #edf2f7;">
            <div style="background: #f0fdf4; border: 1px dashed var(--accent-color); padding: 1.5rem; border-radius: var(--radius-md);">
                <form action="import_voucher.php" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <label for="excel" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Import Vouchers (.xls, .xlsx)</label>
                        <input type="file" name="excel_file" id="excel" accept=".xls,.xlsx" required class="form-control" style="background: white;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self: flex-end;">Upload & Import</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success_message']) || isset($_SESSION['import_success'])): ?>
        <div style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1rem; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['success_message'] ?? $_SESSION['import_success']; unset($_SESSION['success_message'], $_SESSION['import_success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message']) || isset($_SESSION['import_error'])): ?>
        <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-exclamation-circle"></i>
            <?= $_SESSION['error_message'] ?? $_SESSION['import_error']; unset($_SESSION['error_message'], $_SESSION['import_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Vouchers Table -->
    <div class="card" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Voucher Code</th>
                        <th>Office / Dept</th>
                        <th>Date Issued</th>
                        <th style="text-align: center;">Validity</th>
                        <th style="width: 120px;">Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($voucher_list)): ?>
                        <?php foreach ($voucher_list as $voucher): ?>
                            <?php if ($edit_id == $voucher['id']): ?>
                                <tr style="background: #fffaf0;">
                                    <form method="POST" action="vouchers.php">
                                        <td style="color: var(--text-muted);"><?= $voucher['id'] ?><input type="hidden" name="voucher_id" value="<?= $voucher['id'] ?>"></td>
                                        <td><input type="text" name="voucher_code" class="form-control" value="<?= htmlspecialchars($voucher['voucher_code']) ?>" required style="padding: 4px 8px; font-size: 0.9rem;"></td>
                                        <td><input type="text" name="office_department" class="form-control" value="<?= htmlspecialchars($voucher['office_department']) ?>" required style="padding: 4px 8px; font-size: 0.9rem;"></td>
                                        <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($voucher['date_issued'])) ?></td>
                                        <td style="text-align: center;"><input type="number" name="minutes_valid" class="form-control" value="<?= $voucher['minutes_valid'] ?>" required style="padding: 4px 8px; font-size: 0.9rem; width: 80px; text-align: center;"></td>
                                        <td style="text-align: center;">-</td>
                                        <td style="text-align: right;">
                                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                                <button type="submit" name="update_voucher" class="btn" style="padding: 4px 12px; background: var(--accent-color); color: white; font-size: 0.8rem;">Save</button>
                                                <a href="vouchers.php" class="btn" style="padding: 4px 12px; background: #edf2f7; color: var(--text-main); font-size: 0.8rem;">Cancel</a>
                                            </div>
                                        </td>
                                    </form>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($voucher['id']) ?></td>
                                    <td><code style="background: #ebf8ff; color: #2b6cb0; padding: 4px 8px; border-radius: 4px; font-weight: 600;"><?= htmlspecialchars($voucher['voucher_code']) ?></code></td>
                                    <td><span style="font-size: 0.9rem;"><?= htmlspecialchars($voucher['office_department']) ?></span></td>
                                    <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('M d, Y', strtotime($voucher['date_issued'])) ?></td>
                                    <td style="text-align: center;"><span style="font-size: 0.85rem;"><?= htmlspecialchars($voucher['minutes_valid']) ?> mins</span></td>
                                    <td style="text-align: center;">
                                        <?php if (strtolower($voucher['status']) === 'used'): ?>
                                            <span style="display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: #fff5f5; color: #c53030; border: 1px solid #feb2b2;">USED</span>
                                        <?php else: ?>
                                            <span style="display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">AVAILABLE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                            <a href="vouchers.php?edit_id=<?= $voucher['id'] ?>&<?= http_build_query($_GET) ?>" class="btn" style="padding: 6px; background: #ebf8ff; color: #3182ce;" title="Fast Edit">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <form method="POST" action="vouchers.php" style="display:inline;">
                                                <input type="hidden" name="voucher_id" value="<?= $voucher['id'] ?>">
                                                <button type="submit" name="delete_voucher" class="btn" style="padding: 6px; background: #fff5f5; color: #e53e3e;" onclick="return confirm('Delete this voucher record?')" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                <i class="fas fa-ticket-alt" style="display: block; font-size: 3rem; margin-bottom: 1rem; opacity: 0.1;"></i>
                                No vouchers found matching your criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.hidden { display: none !important; }
code { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
</style>

<?php include 'includes/footer.php'; ?>
