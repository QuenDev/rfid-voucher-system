<?php
/**
 * AccountService
 * Handles administrator and staff account management
 */

class AccountService {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all accounts
     */
    public function getAll($search = '') {
        if ($search) {
            $stmt = $this->db->prepare("SELECT * FROM accounts WHERE fullname LIKE :search OR username LIKE :search OR office LIKE :search ORDER BY fullname ASC");
            $stmt->execute(['search' => "%$search%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM accounts ORDER BY fullname ASC");
        }
        return $stmt->fetchAll();
    }

    /**
     * Get account by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new account
     */
    public function create($data) {
        $sql = "INSERT INTO accounts (fullname, username, password, office, role) 
                VALUES (:fullname, :username, :password, :office, :role)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Update account
     */
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }
        
        $sql = "UPDATE accounts SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete account
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
