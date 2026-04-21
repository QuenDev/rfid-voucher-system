<?php
/**
 * AuditService
 * Handles system activity logging for accountability
 */

class AuditService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Log a system event
     * 
     * @param int|null $admin_id The ID of the admin performing the action
     * @param string $action The action performed (e.g., 'CREATE_STUDENT')
     * @param string|null $target_type The type of entity affected ('student', 'voucher', etc.)
     * @param string|null $target_id The ID of the primary entity affected
     * @param string|null $details Additional JSON data or description
     */
    public function log($admin_id, $action, $target_type = null, $target_id = null, $details = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO system_logs (admin_id, action, target_type, target_id, details)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $admin_id,
                $action,
                $target_type,
                $target_id,
                is_array($details) ? json_encode($details) : $details
            ]);
        } catch (PDOException $e) {
            // Silently fail logging to prevent blocking main business logic
            // In production, you might want to log this to a file
            return false;
        }
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT l.*, a.fullname as admin_name 
            FROM system_logs l
            LEFT JOIN accounts a ON l.admin_id = a.id
            ORDER BY l.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
