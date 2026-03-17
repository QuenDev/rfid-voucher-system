<?php
/**
 * Configuration file for RFID Student Voucher System
 * Contains database credentials and global settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'university_voucher_system');

// App Settings
define('APP_NAME', 'RFID Student Voucher System');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Error reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
