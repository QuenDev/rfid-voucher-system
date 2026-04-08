<?php
/**
 * StudentService
 * Handles all business logic related to students/users
 */

class StudentService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all students with optional search
     */
    public function getAll($search = '') {
        if ($search) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE last_name LIKE :search OR first_name LIKE :search OR student_id LIKE :search OR rfid LIKE :search ORDER BY last_name ASC");
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM users ORDER BY last_name ASC");
        }
        return $stmt->fetchAll();
    }

    /**
     * Get student by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get student by RFID
     */
    public function getByRFID($rfid) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE rfid = ?");
        $stmt->execute([$rfid]);
        return $stmt->fetch();
    }

    /**
     * Update student details
     */
    public function update($id, $data) {
        $sql = "UPDATE users SET 
                rfid = :rfid, 
                student_id = :student_id, 
                last_name = :last_name, 
                first_name = :first_name, 
                middle_name = :middle_name, 
                sex = :sex, 
                course = :course, 
                year = :year, 
                section = :section, 
                picture = :picture 
                WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Create new student
     */
    public function create($data) {
        $sql = "INSERT INTO users (rfid, student_id, last_name, first_name, middle_name, sex, course, year, section, picture) 
                VALUES (:rfid, :student_id, :last_name, :first_name, :middle_name, :sex, :course, :year, :section, :picture)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    /**
     * Delete student
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
