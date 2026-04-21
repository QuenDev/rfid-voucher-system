<?php
/**
 * Authentication and Session Management
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if admin is logged in, redirects if not
 */
function requireLogin() {
    if (!isset($_SESSION['admin_id'])) {
        if (strpos($_SERVER['PHP_SELF'], '/client/') !== false || strpos($_SERVER['PHP_SELF'], '\\client\\') !== false) {
            header("Location: login.php");
        } else {
            header("Location: ../client/login.php");
        }
        exit();
    }
}

/**
 * Checks if user has a specific role
 */
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        die("Access Denied: You do not have the required permissions.");
    }
}

/**
 * Logout user and destroy session
 */
function logout() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    // Redirect to login page (handled by client directory)
    if (strpos($_SERVER['PHP_SELF'], '/client/') !== false) {
        header("Location: login.php");
    } else {
        header("Location: ../client/login.php");
    }
    exit();
}

/**
 * CSRF Protection Helpers
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function getCsrfField() {
    return '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
}

function validateCsrfToken($token = null) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
            die("Security error: CSRF validation failed. Please try again.");
        }
    }
    return true;
}
