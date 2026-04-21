<?php
/**
 * AccountService
 * Handles administrator and staff account management
 */

class AccountService {
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
     * Get all accounts with pagination and search
     */
    public function getAll($search = '', $limit = null, $offset = null) {
        $sql = "SELECT * FROM accounts WHERE deleted_at IS NULL";
        $params = [];
        if ($search) {
            $sql .= " AND (fullname LIKE ? OR username LIKE ? OR office LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $sql .= " ORDER BY fullname ASC";

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
     * Get total count of accounts
     */
    public function getTotalCount($search = '') {
        $sql = "SELECT COUNT(*) FROM accounts WHERE deleted_at IS NULL";
        $params = [];
        if ($search) {
            $sql .= " AND (fullname LIKE ? OR username LIKE ? OR office LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%"];
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get account by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new account
     */
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO accounts (username, fullname, office, password, role) VALUES (?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            $data['username'],
            $data['fullname'],
            $data['office'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role']
        ]);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'CREATE_ACCOUNT', 'account', $this->db->lastInsertId(), "User: {$data['username']}");
        }
        return $result;
    }

    /**
     * Update account
     */
    public function update($id, $data) {
        $sql = "UPDATE accounts SET username = ?, fullname = ?, office = ?, role = ?";
        $params = [$data['username'], $data['fullname'], $data['office'], $data['role']];
        
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'UPDATE_ACCOUNT', 'account', $id);
        }
        return $result;
    }

    /**
     * Soft delete an account
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE accounts SET deleted_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'DELETE_ACCOUNT', 'account', $id);
        }
        return $result;
    }
}
