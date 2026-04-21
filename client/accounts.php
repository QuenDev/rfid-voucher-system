<?php
require_once '../server/includes/auth.php';
require_once '../server/includes/db.php';
require_once '../server/includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'] ?? null;
$auditService = new AuditService($pdo);
$accountService = new AccountService($pdo, $auditService, $admin_id);

// Get filter and search inputs
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 15;
$offset = ($page - 1) * $limit;

try {
    $total_records = $accountService->getTotalCount($search);
    $total_pages = ceil($total_records / $limit);
    $accounts = $accountService->getAll($search, $limit, $offset);
} catch (Exception $e) {
    $error = "Error fetching accounts: " . $e->getMessage();
    $accounts = [];
    $total_pages = 0;
}

// Handle delete
if (isset($_POST['confirm_delete'])) {
    validateCsrfToken();
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

    <?php include 'includes/alerts.php'; ?>

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
                                                <?php echo getCsrfField(); ?>
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

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; background: white; padding: 1rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.9rem; color: var(--text-muted);">
            Showing <?= count($accounts) ?> of <?= $total_records ?> accounts
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="btn" style="background: #edf2f7; color: var(--text-main); font-size: 0.85rem;"><i class="fas fa-chevron-left"></i> Previous</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn" style="background: <?= ($i == $page) ? 'var(--accent-color)' : '#edf2f7' ?>; color: <?= ($i == $page) ? 'white' : 'var(--text-main)' ?>; font-size: 0.85rem; padding: 6px 12px;"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="btn" style="background: #edf2f7; color: var(--text-main); font-size: 0.85rem;">Next <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
html>