<?php
/**
 * Configuration file for RFID Student Voucher System
 * Contains database credentials and global settings
 */

// Load .env
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
        $_ENV[trim($name)] = trim($value);
    }
}

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_NAME', getenv('DB_NAME') ?: 'university_voucher_system');

// App Settings
define('APP_NAME', 'RFID Student Voucher System');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Error reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
