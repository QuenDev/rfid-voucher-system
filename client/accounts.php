<?php
require_once '../server/includes/auth.php';
require_once '../server/includes/db.php';
require_once '../server/includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];
$accountService = new AccountService($pdo);

$search = $_GET['search'] ?? '';
try {
    $accounts = $accountService->getAll($search);
} catch (Exception $e) {
    $error = "Error fetching accounts: " . $e->getMessage();
    $accounts = [];
}

// Handle delete
if (isset($_POST['confirm_delete'])) {
    $account_id = $_POST['account_id'];
    
    // Prevent deleting self
    if ($account_id == $admin_id) {
        $_SESSION['delete_error'] = "You cannot delete your own account.";
    } else {
        if ($accountService->delete($account_id)) {
            $_SESSION['delete_success'] = "Account deleted successfully!";
        } else {
            $_SESSION['delete_error'] = "Failed to delete account.";
        }
    }
    header("Location: accounts.php");
    exit();
}

$pageTitle = "Manage Accounts";
include 'includes/header.php';
?>

<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- Search & Actions -->
    <div class="card" style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <form method="GET" style="flex: 1; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                <div style="position: relative; flex: 1; min-width: 250px;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search accounts by name, username, or office..." value="<?= htmlspecialchars($search) ?>" style="padding-left: 2.75rem;">
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <?php if (!empty($search)): ?>
                        <a href="accounts.php" class="btn" style="background: #edf2f7; color: var(--text-main);">Clear</a>
                    <?php endif; ?>
                </div>
            </form>
            <a href="add_account.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> New Account
            </a>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['delete_success'])): ?>
        <div style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1rem; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-check-circle"></i>
            <?= $_SESSION['delete_success']; unset($_SESSION['delete_success']); ?>
        </div>
    <?php elseif (isset($_SESSION['delete_error'])): ?>
        <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1rem; border-radius: var(--radius-sm); display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-exclamation-circle"></i>
            <?= $_SESSION['delete_error']; unset($_SESSION['delete_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Accounts Table -->
    <div class="card" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>User Information</th>
                        <th>Office / Dept</th>
                        <th>Role</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($accounts)): ?>
                        <?php foreach ($accounts as $row): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-size: 0.9rem;">#<?= htmlspecialchars($row['id']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #edf2f7; color: var(--accent-color); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                            <?= strtoupper($row['fullname'][0]) ?>
                                        </div>
                                        <div style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($row['fullname']) ?></span>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">@<?= htmlspecialchars($row['username']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span style="font-size: 0.9rem;"><?= htmlspecialchars($row['office']) ?></span></td>
                                <td>
                                    <span style="display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; 
                                        <?= strtolower($row['role']) == 'admin' ? 'background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8;' : 'background: #f7fafc; color: #4a5568; border: 1px solid #e2e8f0;' ?>">
                                        <?= strtoupper($row['role']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <a href="edit_account.php?id=<?= $row['id'] ?>" class="btn" style="padding: 6px; background: #ebf8ff; color: #3182ce;" title="Edit Account">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <?php if ($row['id'] != $admin_id): ?>
                                            <form method="POST" action="accounts.php" style="display:inline;">
                                                <input type="hidden" name="account_id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="confirm_delete" class="btn" style="padding: 6px; background: #fff5f5; color: #e53e3e;" onclick="return confirm('Permanently delete this account?')" title="Delete Account">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                <i class="fas fa-users-slash" style="display: block; font-size: 3rem; margin-bottom: 1rem; opacity: 0.1;"></i>
                                No accounts found matching your search.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
html>