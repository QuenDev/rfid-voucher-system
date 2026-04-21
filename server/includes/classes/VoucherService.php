<?php
/**
 * VoucherService
 * Handles all business logic related to vouchers
 */

class VoucherService {
    /** @var \PDO */
    private $db;
    /** @var AuditService|null */
    private $auditService;
    /** @var int|null */
    private $adminId;

    public function __construct(\PDO $db, ?AuditService $auditService = null, $adminId = null) {
        $this->db = $db;
        $this->auditService = $auditService;
        $this->adminId = $adminId;
    }

    /**
     * Get all vouchers with filtering and pagination
     */
    public function getAll($filters = [], $limit = null, $offset = null) {
        $sql = "SELECT * FROM vouchers WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $status = strtolower(trim((string)$filters['status']));
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (voucher_code LIKE ? OR office_department LIKE ?)";
            $searchVal = "%" . $filters['search'] . "%";
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(date_issued) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(date_issued) <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY date_issued DESC";

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
     * Get total count for filtering
     */
    public function getTotalCount($filters = []) {
        $sql = "SELECT COUNT(*) FROM vouchers WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['status'])) {
            $status = strtolower(trim((string)$filters['status']));
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (voucher_code LIKE ? OR office_department LIKE ?)";
            $searchVal = "%" . $filters['search'] . "%";
            $params[] = $searchVal;
            $params[] = $searchVal;
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(date_issued) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(date_issued) <= ?";
            $params[] = $filters['to_date'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get voucher by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a new voucher
     */
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO vouchers (voucher_code, office_department, minutes_valid, date_issued, status) VALUES (?, ?, ?, NOW(), 'available')");
        $result = $stmt->execute([$data['voucher_code'], $data['office_department'], $data['minutes_valid']]);
        
        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'CREATE_VOUCHER', 'voucher', $this->db->lastInsertId(), "Code: {$data['voucher_code']}");
        }
        return $result;
    }

    /**
     * Update voucher
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE vouchers SET voucher_code = ?, office_department = ?, minutes_valid = ? WHERE id = ?");
        $result = $stmt->execute([$data['voucher_code'], $data['office_department'], $data['minutes_valid'], $id]);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'UPDATE_VOUCHER', 'voucher', $id);
        }
        return $result;
    }

    /**
     * Delete voucher
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE vouchers SET deleted_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'DELETE_VOUCHER', 'voucher', $id);
        }
        return $result;
    }

    /**
     * Redeem voucher for a student with pessimistic locking
     * Prevents race conditions when multiple requests try to redeem same voucher
     */
    public function redeem($voucher_id, $student_id) {
        try {
            $this->db->beginTransaction();
            
            // Lock the voucher row to prevent concurrent redemption
            // FOR UPDATE ensures no other transaction can modify this row
            $stmt = $this->db->prepare("SELECT id FROM vouchers WHERE id = ? FOR UPDATE");
            $stmt->execute([$voucher_id]);
            $voucher = $stmt->fetch();
            
            if (!$voucher) {
                $this->db->rollBack();
                return false;
            }
            
            $stmt = $this->db->prepare("UPDATE vouchers SET status = 'used' WHERE id = ?");
            $stmt->execute([$voucher_id]);
            
            // Use student_id as string since that's how it's stored in the actual schema
            $stmt = $this->db->prepare("INSERT INTO student_vouchers (student_id, voucher_id, redeemed_at) VALUES (?, ?, NOW())");
            $stmt->execute([$student_id, $voucher_id]);
            
            $this->db->commit();

            if ($this->auditService) {
                $this->auditService->log($this->adminId, 'REDEEM_VOUCHER', 'voucher', $voucher_id, "Student: $student_id");
            }
            return true;
        } catch (\Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }
}
