<?php
/**
 * Global Utility Functions
 */

require_once __DIR__ . '/db.php';

/**
 * Format database results into an array
 */
function fetchAll($stmt) {
    if (!$stmt) return [];
    return $stmt->fetchAll();
}

/**
 * Validates RFID tag format (example: 8-10 hex chars)
 */
function isValidRFID($rfid) {
    return preg_match('/^[a-fA-F0-9]{8,15}$/', $rfid);
}

/**
 * Log admin actions to database (if admin_logs exists)
 */
function logAdminAction($admin_id, $action) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, timestamp) VALUES (?, ?, NOW())");
    return $stmt->execute([$admin_id, $action]);
}
