<?php
/**
 * VoucherService
 * Handles all business logic related to vouchers
 */

class VoucherService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all vouchers with filtering
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM vouchers WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (voucher_code LIKE :search OR office_department LIKE :search)";
            $params['search'] = "%" . $filters['search'] . "%";
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get voucher by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a new voucher
     */
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO vouchers (voucher_code, office_department, minutes_valid, created_at, status) VALUES (?, ?, ?, NOW(), 'available')");
        return $stmt->execute([$data['voucher_code'], $data['office_department'], $data['minutes_valid']]);
    }

    /**
     * Update voucher
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE vouchers SET voucher_code = ?, office_department = ?, minutes_valid = ? WHERE id = ?");
        return $stmt->execute([$data['voucher_code'], $data['office_department'], $data['minutes_valid'], $id]);
    }

    /**
     * Delete voucher
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM vouchers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Redeem voucher for a student
     */
    public function redeem($voucher_id, $student_id) {
        $this->db->beginTransaction();
        try {
            // Update voucher status
            $stmt = $this->db->prepare("UPDATE vouchers SET status = 'used' WHERE id = ?");
            $stmt->execute([$voucher_id]);

            // Link to student
            $stmt = $this->db->prepare("INSERT INTO student_vouchers (student_id, voucher_id, redeemed_at) VALUES (?, ?, NOW())");
            $stmt->execute([$student_id, $voucher_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
