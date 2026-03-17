<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Get account ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: accounts.php");
    exit();
}

$id = intval($_GET['id']);
$accountService = new AccountService($pdo);
$account = $accountService->getById($id);

if (!$account) {
    header("Location: accounts.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $office = trim($_POST['office']);
    $role = trim($_POST['role']);
    $new_password = trim($_POST['password']);

    $data = [
        'fullname' => $fullname,
        'username' => $username,
        'office' => $office,
        'role' => $role
    ];

    if (!empty($new_password)) {
        $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    try {
        if ($accountService->update($id, $data)) {
            // Update session if editing self
            if ($id == $admin_id) {
                $_SESSION['fullname'] = $fullname;
                $_SESSION['role'] = $role;
            }
            $_SESSION['edit_success'] = "Account updated successfully!";
        } else {
            $_SESSION['edit_error'] = "Failed to update account.";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), '1062') !== false) {
            $_SESSION['edit_error'] = "Username already exists. Please choose a different one.";
        } else {
            $_SESSION['edit_error'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: edit_account.php?id=$id");
    exit();
}

$pageTitle = "Edit Account";
include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem;">
            <a href="accounts.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Accounts
            </a>
            <h2 style="margin: 1.5rem 0 0.5rem; color: var(--text-main);">Edit Account Settings</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Update credentials and department info for <?= htmlspecialchars($account['fullname']) ?>.</p>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['edit_success'])): ?>
            <div style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['edit_success']; unset($_SESSION['edit_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['edit_error'])): ?>
            <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['edit_error']; unset($_SESSION['edit_error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" name="fullname" id="fullname" class="form-control" value="<?= htmlspecialchars($account['fullname']) ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($account['username']) ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Change Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Leave blank to keep current">
                <small style="color: var(--text-muted); font-size: 0.75rem;">Only fill this if you want to reset the password.</small>
            </div>

            <div class="form-group">
                <label for="office">Office / Department</label>
                <input type="text" name="office" id="office" class="form-control" value="<?= htmlspecialchars($account['office']) ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Account Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="admin" <?= $account['role'] === 'admin' ? 'selected' : '' ?>>Admin (Full Access)</option>
                    <option value="staff" <?= $account['role'] === 'staff' ? 'selected' : '' ?>>Staff (Limited Access)</option>
                </select>
            </div>

            <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #edf2f7; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 12px;">Save Changes</button>
                <a href="accounts.php" class="btn" style="flex: 1; background: #edf2f7; color: var(--text-main); text-align: center; text-decoration: none; padding: 12px;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
