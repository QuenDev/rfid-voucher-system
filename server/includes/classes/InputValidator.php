<?php
/**
 * InputValidator
 * Centralized input validation and sanitization
 * Provides consistent validation across the application
 */

class InputValidator {
    private $errors = [];

    /**
     * Validate student data
     */
    public static function validateStudent(array $data): array {
        $errors = [];
        
        // RFID validation (optional, must be alphanumeric if provided)
        if (!empty($data['rfid']) && !preg_match('/^[a-zA-Z0-9]{4,20}$/', $data['rfid'])) {
            $errors['rfid'] = 'RFID must be 4-20 alphanumeric characters';
        }
        
        // Student ID validation (required, unique)
        if (empty($data['student_id'])) {
            $errors['student_id'] = 'Student ID is required';
        } elseif (!preg_match('/^[0-9]{2}-[0-9]{5}$/', $data['student_id'])) {
            $errors['student_id'] = 'Student ID must be in format YY-XXXXX';
        }
        
        // Name validation
        if (empty($data['last_name']) || strlen(trim($data['last_name'])) < 2) {
            $errors['last_name'] = 'Last name is required (min 2 chars)';
        }
        
        if (empty($data['first_name']) || strlen(trim($data['first_name'])) < 2) {
            $errors['first_name'] = 'First name is required (min 2 chars)';
        }
        
        // Sex validation
        if (empty($data['sex']) || !in_array($data['sex'], ['M', 'F'])) {
            $errors['sex'] = 'Sex must be M or F';
        }
        
        // Course validation (optional)
        if (!empty($data['course']) && strlen($data['course']) > 100) {
            $errors['course'] = 'Course name too long (max 100 chars)';
        }
        
        // Year validation (optional)
        if (!empty($data['year']) && (!is_numeric($data['year']) || $data['year'] < 1 || $data['year'] > 6)) {
            $errors['year'] = 'Year must be between 1 and 6';
        }
        
        return $errors;
    }

    /**
     * Validate voucher data
     */
    public static function validateVoucher(array $data): array {
        $errors = [];
        
        // Voucher code validation
        if (empty($data['voucher_code'])) {
            $errors['voucher_code'] = 'Voucher code is required';
        } elseif (!preg_match('/^[A-Z0-9\-]{8,50}$/', $data['voucher_code'])) {
            $errors['voucher_code'] = 'Voucher code must be 8-50 alphanumeric characters';
        }
        
        // Office/Department validation
        if (empty($data['office_department'])) {
            $errors['office_department'] = 'Office/Department is required';
        } elseif (strlen($data['office_department']) > 100) {
            $errors['office_department'] = 'Office/Department too long (max 100 chars)';
        }
        
        // Minutes validity validation
        if (empty($data['minutes_valid']) || !is_numeric($data['minutes_valid'])) {
            $errors['minutes_valid'] = 'Minutes valid is required and must be numeric';
        } elseif ((int)$data['minutes_valid'] < 1 || (int)$data['minutes_valid'] > 1440) {
            $errors['minutes_valid'] = 'Minutes valid must be between 1 and 1440 (24 hours)';
        }
        
        return $errors;
    }

    /**
     * Validate account/admin data
     */
    public static function validateAccount(array $data, bool $isUpdate = false): array {
        $errors = [];
        
        // Username validation
        if (!$isUpdate || !empty($data['username'])) {
            if (empty($data['username'])) {
                $errors['username'] = 'Username is required';
            } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
                $errors['username'] = 'Username must be 3-50 characters';
            } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $data['username'])) {
                $errors['username'] = 'Username can only contain letters, numbers, dots, dashes, and underscores';
            }
        }
        
        // Full name validation
        if (!$isUpdate || !empty($data['fullname'])) {
            if (empty($data['fullname']) || strlen(trim($data['fullname'])) < 2) {
                $errors['fullname'] = 'Full name is required (min 2 chars)';
            } elseif (strlen($data['fullname']) > 100) {
                $errors['fullname'] = 'Full name too long (max 100 chars)';
            }
        }
        
        // Office validation (optional)
        if (!empty($data['office']) && strlen($data['office']) > 100) {
            $errors['office'] = 'Office too long (max 100 chars)';
        }
        
        // Password validation (required on create, optional on update)
        if (!$isUpdate && empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            } elseif (strlen($data['password']) > 255) {
                $errors['password'] = 'Password too long (max 255 chars)';
            }
        }
        
        // Role validation
        if (empty($data['role']) || !in_array($data['role'], ['admin', 'staff'])) {
            $errors['role'] = 'Role must be admin or staff';
        }
        
        return $errors;
    }

    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, string $type = 'image'): array {
        $errors = [];
        
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return $errors; // Optional upload
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['file'] = 'File upload error: ' . $file['error'];
            return $errors;
        }
        
        // File size validation (5MB max)
        $maxSize = 5242880; // 5MB
        if ($file['size'] > $maxSize) {
            $errors['file'] = 'File too large (max 5MB)';
        }
        
        // Image validation
        if ($type === 'image') {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                $errors['file'] = 'Invalid image extension. Only JPG, PNG, and WebP allowed';
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime, $allowedMimes)) {
                $errors['file'] = 'Invalid image type. Must be JPEG, PNG, or WebP';
            }
        }
        
        return $errors;
    }

    /**
     * Sanitize string input
     */
    public static function sanitizeString(string $input, int $maxLength = 255): string {
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        if (strlen($input) > $maxLength) {
            $input = substr($input, 0, $maxLength);
        }
        
        return $input;
    }

    /**
     * Sanitize for database (prepare for PDO)
     */
    public static function sanitizeForDB(string $input): string {
        return trim($input);
    }

    /**
     * Escape for HTML output
     */
    public static function escapeHtml($value): string {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
