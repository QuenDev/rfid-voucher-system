<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    try {
        $stmt = $pdo->prepare("SELECT id, password, role, fullname FROM accounts WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION["admin_id"] = $user['id'];
                $_SESSION["role"] = $user['role'];
                $_SESSION["fullname"] = $user['fullname'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid credentials. Please check your username and password.";
            }
        } else {
            $error = "Admin account not found with that username.";
        }
    } catch (Exception $e) {
        $error = "An error occurred during login. Please try again.";
    }
}

$pageTitle = "Admin Login";
$hideLayout = true;
include 'includes/header.php';
?>

<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <img src="isu-logo.png" alt="ISU Logo" class="login-logo" onerror="this.src='fallback.png'">
            <h2 style="font-weight: 700; color: var(--text-main); font-size: 1.75rem;">Voucher System</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Sign in to your account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fff5f5; color: #c53030; padding: 1rem; border-radius: var(--radius-sm); border-left: 4px solid #c53030; margin-bottom: 2rem; font-size: 0.875rem;">
                <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username" class="form-label">Admin ID</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your ID" style="padding-left: 2.75rem;" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" style="padding-left: 2.75rem;" required>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 50px; font-size: 1rem;">
                    Sign In
                </button>
            </div>
        </form>
        
        <div style="margin-top: 2rem; text-align: center;">
            <p style="font-size: 0.8rem; color: var(--text-muted);">&copy; <?php echo date('Y'); ?> Isabela State University. All rights reserved.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>