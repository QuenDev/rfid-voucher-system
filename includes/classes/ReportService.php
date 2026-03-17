<?php
/**
 * ReportService
 * Handles data aggregation and export logic
 */

class ReportService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get redeemed vouchers report data
     */
    public function getRedemptionReport($filter = 'daily') {
        $whereClause = "";
        switch ($filter) {
            case 'weekly':
                $whereClause = "WHERE YEARWEEK(sv.redeemed_at, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'monthly':
                $whereClause = "WHERE MONTH(sv.redeemed_at) = MONTH(CURDATE()) AND YEAR(sv.redeemed_at) = YEAR(CURDATE())";
                break;
            case 'yearly':
                $whereClause = "WHERE YEAR(sv.redeemed_at) = YEAR(CURDATE())";
                break;
            default:
                $whereClause = "WHERE DATE(sv.redeemed_at) = CURDATE()";
                break;
        }

        $sql = "
            SELECT 
                CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS student_name,
                u.student_id,
                v.voucher_code,
                sv.redeemed_at
            FROM student_vouchers sv
            JOIN users u ON sv.student_id = u.id
            JOIN vouchers v ON sv.voucher_id = v.id
            $whereClause
            ORDER BY sv.redeemed_at DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get basic statistics for dashboard
     */
    public function getDashboardStats() {
        return [
            'total_students' => $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'available_vouchers' => $this->db->query("SELECT COUNT(*) FROM vouchers WHERE status = 'available'")->fetchColumn(),
            'used_vouchers' => $this->db->query("SELECT COUNT(*) FROM vouchers WHERE status = 'used'")->fetchColumn(),
            'today_redemptions' => $this->db->query("SELECT COUNT(*) FROM student_vouchers WHERE DATE(redeemed_at) = CURDATE()")->fetchColumn()
        ];
    }
}
