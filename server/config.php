<?php
/**
 * Configuration file for RFID Student Voucher System
 * Contains database credentials and global settings
 */

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, '"\''); // Handle quotes
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

// Application Environment
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('IS_PRODUCTION', APP_ENV === 'production');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'university_voucher_system');

// App Settings
define('APP_NAME', getenv('APP_NAME') ?: 'RFID Student Voucher System');
define('UPLOAD_DIR', __DIR__ . '/../client/uploads/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error reporting - Disable in production
if (IS_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
