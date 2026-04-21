<?php
/**
 * StudentService
 * Handles all business logic related to students/users
 */

class StudentService {
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
     * Get all students with pagination and search
     */
    public function getAll($search = '', $limit = null, $offset = null) {
        $sql = "SELECT * FROM users WHERE deleted_at IS NULL";
        $params = [];
        if ($search) {
            $sql .= " AND (last_name LIKE ? OR first_name LIKE ? OR student_id LIKE ? OR rfid LIKE ?)";
            $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
        }
        $sql .= " ORDER BY last_name ASC";
        
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
     * Get total count of students matching search
     */
    public function getTotalCount($search = '') {
        $sql = "SELECT COUNT(*) FROM users";
        $params = [];
        if ($search) {
            $sql .= " WHERE (last_name LIKE ? OR first_name LIKE ? OR student_id LIKE ? OR rfid LIKE ?) AND deleted_at IS NULL";
            $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
        } else {
            $sql .= " WHERE deleted_at IS NULL";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Get student by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get student by RFID
     */
    public function getByRFID($rfid) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE rfid = ? AND deleted_at IS NULL");
        $stmt->execute([$rfid]);
        return $stmt->fetch();
    }

    /**
     * Update student details
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET 
                rfid = ?, 
                student_id = ?, 
                last_name = ?, 
                first_name = ?, 
                middle_name = ?, 
                sex = ?, 
                course = ?, 
                year = ?, 
                section = ?, 
                picture = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['rfid'],
            $data['student_id'],
            $data['last_name'],
            $data['first_name'],
            $data['middle_name'],
            $data['sex'],
            $data['course'],
            $data['year'],
            $data['section'],
            $data['picture'] ?? null,
            $id
        ]);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'UPDATE_STUDENT', 'student', $id);
        }
        return $result;
    }

    /**
     * Create new student
     */
    public function create($data) {
        $sql = "INSERT INTO users (rfid, student_id, last_name, first_name, middle_name, sex, course, year, section, picture) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['rfid'] ?? null,
            $data['student_id'],
            $data['last_name'],
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['sex'],
            $data['course'] ?? null,
            $data['year'] ?? null,
            $data['section'] ?? null,
            $data['picture'] ?? null
        ]);

        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'CREATE_STUDENT', 'student', $data['student_id'], "Name: {$data['first_name']} {$data['last_name']}");
        }
        return $this->db->lastInsertId();
    }

    /**
     * Delete student
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result && $this->auditService) {
            $this->auditService->log($this->adminId, 'DELETE_STUDENT', 'student', $id, "Deleted student ID: $id");
        }
        return $result;
    }
}
