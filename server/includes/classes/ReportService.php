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
     * Get redeemed vouchers report data with search and pagination
     */
    public function getRedemptionReport($filter = 'daily', $search = '', $limit = null, $offset = null, $startDate = '', $endDate = '') {
        $whereClause = "";
        $params = [];
        if (!empty($startDate) || !empty($endDate)) {
            if (empty($startDate)) {
                $startDate = $endDate;
            }
            if (empty($endDate)) {
                $endDate = $startDate;
            }
            $whereClause = "WHERE DATE(sv.redeemed_at) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        } else {
            switch ($filter) {
                case 'weekly':
                    $whereClause = "WHERE sv.redeemed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'monthly':
                    $whereClause = "WHERE sv.redeemed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'yearly':
                    $whereClause = "WHERE YEAR(sv.redeemed_at) = YEAR(CURDATE())";
                    break;
                default:
                    $whereClause = "WHERE DATE(sv.redeemed_at) = CURDATE()";
                    break;
            }
        }

        if ($search) {
            $whereClause .= " AND (CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) LIKE :search OR u.student_id LIKE :search OR v.voucher_code LIKE :search)";
            $params['search'] = "%$search%";
        }

        $sql = "
            SELECT 
                CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS student_name,
                u.student_id,
                v.voucher_code,
                v.office_department,
                sv.redeemed_at
            FROM student_vouchers sv
            JOIN users u ON sv.student_id = u.student_id
            JOIN vouchers v ON sv.voucher_id = v.id
            $whereClause
            ORDER BY sv.redeemed_at DESC
        ";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get total count of redemptions for pagination
     */
    public function getRedemptionCount($filter = 'daily', $search = '', $startDate = '', $endDate = '') {
        $whereClause = "";
        $params = [];
        if (!empty($startDate) || !empty($endDate)) {
            if (empty($startDate)) {
                $startDate = $endDate;
            }
            if (empty($endDate)) {
                $endDate = $startDate;
            }
            $whereClause = "WHERE DATE(sv.redeemed_at) BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        } else {
            switch ($filter) {
                case 'weekly':
                    $whereClause = "WHERE sv.redeemed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'monthly':
                    $whereClause = "WHERE sv.redeemed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'yearly':
                    $whereClause = "WHERE YEAR(sv.redeemed_at) = YEAR(CURDATE())";
                    break;
                default:
                    $whereClause = "WHERE DATE(sv.redeemed_at) = CURDATE()";
                    break;
            }
        }

        if ($search) {
            $whereClause .= " AND (CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) LIKE :search OR u.student_id LIKE :search OR v.voucher_code LIKE :search)";
            $params['search'] = "%$search%";
        }

        $sql = "
            SELECT COUNT(*)
            FROM student_vouchers sv
            JOIN users u ON sv.student_id = u.student_id
            JOIN vouchers v ON sv.voucher_id = v.id
            $whereClause
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get dashboard stats summary
     * Optimized to use single query instead of 4 separate queries
     */
    public function getDashboardStats() {
        // Single optimized query for all stats
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM users WHERE deleted_at IS NULL) as total_students,
                (SELECT COUNT(*) FROM vouchers WHERE deleted_at IS NULL) as total_vouchers,
                (SELECT COUNT(*) FROM vouchers WHERE status = 'used' AND deleted_at IS NULL) as vouchers_used,
                (SELECT COUNT(*) FROM vouchers WHERE status = 'available' AND deleted_at IS NULL) as vouchers_available
            LIMIT 1
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return [
            'total_students' => (int)$result['total_students'],
            'total_vouchers' => (int)$result['total_vouchers'],
            'used_vouchers' => (int)$result['vouchers_used'],
            'available_vouchers' => (int)$result['vouchers_available']
        ];
    }
}
