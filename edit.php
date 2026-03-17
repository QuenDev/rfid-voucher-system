<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Check if voucher ID is provided
if (!isset($_GET['voucher_id']) || !is_numeric($_GET['voucher_id'])) {
    $_SESSION['edit_error'] = "Invalid voucher ID.";
    header("Location: vouchers.php");
    exit();
}

$id = intval($_GET['voucher_id']);
$voucherService = new VoucherService($pdo);
$voucher = $voucherService->getById($id);

if (!$voucher) {
    $_SESSION['edit_error'] = "Voucher not found.";
    header("Location: vouchers.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'voucher_code' => trim($_POST['voucher_code']),
        'office_department' => trim($_POST['office_department']),
        'minutes_valid' => intval($_POST['minutes_valid'])
    ];

    if (empty($data['voucher_code']) || empty($data['office_department']) || $data['minutes_valid'] < 1) {
        $error = "Please fill in all fields correctly.";
    } else {
        try {
            if ($voucherService->update($id, $data)) {
                $_SESSION['voucher_success'] = "Voucher updated successfully.";
                header("Location: vouchers.php");
                exit();
            } else {
                $error = "Failed to update voucher.";
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '1062') !== false) {
                $error = "Voucher code already exists.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}

$pageTitle = "Edit Voucher";
include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem;">
            <a href="vouchers.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Vouchers
            </a>
            <h2 style="margin: 1.5rem 0 0.5rem; color: var(--text-main);">Edit Voucher</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Update the details for voucher code <span style="font-weight: 600; color: var(--accent-color);"><?= htmlspecialchars($voucher['voucher_code']) ?></span></p>
        </div>

        <!-- Messages -->
        <?php if (isset($error)): ?>
            <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="form-group">
                <label for="voucher_code">Voucher Code</label>
                <div style="position: relative;">
                    <i class="fas fa-ticket-alt" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="voucher_code" id="voucher_code" class="form-control" value="<?= htmlspecialchars($voucher['voucher_code']) ?>" style="padding-left: 2.75rem;" required>
                </div>
            </div>

            <div class="form-group">
                <label for="office_department">Office / Department</label>
                <input type="text" name="office_department" id="office_department" class="form-control" value="<?= htmlspecialchars($voucher['office_department']) ?>" required>
            </div>

            <div class="form-group">
                <label for="minutes_valid">Validity (Minutes)</label>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <input type="number" name="minutes_valid" id="minutes_valid" class="form-control" min="1" value="<?= htmlspecialchars($voucher['minutes_valid']) ?>" required style="flex: 1;">
                    <span style="color: var(--text-muted); font-size: 0.9rem; white-space: nowrap;">mins</span>
                </div>
            </div>

            <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #edf2f7; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 12px; font-weight: 600;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
