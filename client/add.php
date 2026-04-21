<?php
require_once '../server/includes/auth.php';
require_once '../server/includes/db.php';
require_once '../server/includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    validateCsrfToken();
    $admin_id = $_SESSION['admin_id'] ?? null;
    $auditService = new AuditService($pdo);
    $voucherService = new VoucherService($pdo, $auditService, $admin_id);
    
    $data = [
        'voucher_code' => trim($_POST['voucher_code']),
        'office_department' => trim($_POST['office_department']),
        'minutes_valid' => intval($_POST['minutes_valid'])
    ];

    try {
        if ($voucherService->create($data)) {
            $_SESSION['add_success'] = "Voucher successfully created!";
        } else {
            $_SESSION['add_error'] = "Failed to create voucher.";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), '1062') !== false) {
            $_SESSION['add_error'] = "Voucher code already exists. Please try a different one.";
        } else {
            $_SESSION['add_error'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: add.php");
    exit();
}

$pageTitle = "Manual Voucher Creation";
include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem;">
            <a href="vouchers.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> All Vouchers
            </a>
            <h2 style="margin: 1.5rem 0 0.5rem; color: var(--text-main);">Create Voucher</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Issue a single voucher manually for a specific purpose or office.</p>
        </div>

        <?php include 'includes/alerts.php'; ?>

        <form method="POST" action="add.php" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php echo getCsrfField(); ?>
            <div class="form-group">
                <label for="voucher_code">Voucher Code</label>
                <div style="position: relative;">
                    <i class="fas fa-ticket-alt" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="voucher_code" id="voucher_code" class="form-control" placeholder="e.g. SUMMER2024" style="padding-left: 2.75rem;" required>
                </div>
            </div>

            <div class="form-group">
                <label for="office_department">Office / Department</label>
                <input type="text" name="office_department" id="office_department" class="form-control" placeholder="e.g. Library" required>
            </div>

            <div class="form-group">
                <label for="minutes_valid">Validity (Minutes)</label>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <input type="number" name="minutes_valid" id="minutes_valid" class="form-control" min="1" value="60" required style="flex: 1;">
                    <span style="color: var(--text-muted); font-size: 0.9rem; white-space: nowrap;">mins</span>
                </div>
            </div>

            <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #edf2f7; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 12px; font-weight: 600;">Create Voucher</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
