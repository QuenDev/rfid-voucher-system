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
    $accountService = new AccountService($pdo, $auditService, $admin_id);

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password_input = trim($_POST['password']);
    $office = trim($_POST['office']);
    $role = trim($_POST['role']);

    // Automatically generate password if not provided
    if (empty($password_input)) {
        $password_input = bin2hex(random_bytes(4)); // 8-character hex string
        $_SESSION['generated_password'] = $password_input;
    }

    $password = password_hash($password_input, PASSWORD_DEFAULT);

    try {
        $result = $accountService->create([
            'fullname' => $fullname,
            'username' => $username,
            'password' => $password,
            'office' => $office,
            'role' => $role
        ]);

        if ($result) {
            $_SESSION['add_success'] = "Account successfully created!";
        } else {
            $_SESSION['add_error'] = "Failed to create account.";
        }
    } catch (Exception $e) {
        if (strpos($e->getMessage(), '1062') !== false) {
            $_SESSION['add_error'] = "Username already exists. Please choose a different one.";
        } else {
            $_SESSION['add_error'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: add_account.php");
    exit();
}

$pageTitle = "Create New Account";
include 'includes/header.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem;">
            <a href="accounts.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Accounts
            </a>
            <h2 style="margin: 1.5rem 0 0.5rem; color: var(--text-main);">Create New Account</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Set up a new administrative or staff member.</p>
        </div>

        <?php include 'includes/alerts.php'; ?>

        <form method="POST" action="add_account.php" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php echo getCsrfField(); ?>
            <div class="form-group">
                <label for="fullname">Full Name</label>
                <input type="text" name="fullname" id="fullname" class="form-control" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="e.g. jdoe" required>
            </div>

            <div class="form-group">
                <label for="office">Office / Department</label>
                <input type="text" name="office" id="office" class="form-control" placeholder="e.g. Registrar's Office" required>
            </div>

            <div class="form-group">
                <label for="role">Account Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="admin">Admin (Full Access)</option>
                    <option value="staff">Staff (Limited Access)</option>
                </select>
            </div>

            <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid #edf2f7; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 12px;">Create Account</button>
                <a href="accounts.php" class="btn" style="flex: 1; background: #edf2f7; color: var(--text-main); text-align: center; text-decoration: none; padding: 12px;">Cancel</a>
            </div>
            <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
                <i class="fas fa-info-circle"></i> Note: A secure password will be generated automatically.
            </p>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
