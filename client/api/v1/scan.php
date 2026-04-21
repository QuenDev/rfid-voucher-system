<?php
/**
 * RFID Scan API Endpoint v1
 * Official consolidated endpoint for RFID scanning operations
 * 
 * This is the ONLY scan endpoint. server/rfid_scan.php is deprecated and should not be used.
 * 
 * Response Format:
 * {
 *   "status": "success|warning|error",
 *   "message": "Human readable message",
 *   "student": { "name", "id", "picture" } or null,
 *   "voucher": { "code", "minutes" } or null
 * }
 */

require_once '../../../server/includes/db.php';
require_once '../../../server/includes/functions.php';

// CORS and Content-Type Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method Not Allowed. Use POST.'
    ]);
    exit();
}

// Extract and validate RFID
$rfid = trim($_POST['rfid'] ?? '');

if (empty($rfid)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Bad Request: RFID tag is required'
    ]);
    exit();
}

// Validate RFID format (basic alphanumeric check)
if (!preg_match('/^[a-zA-Z0-9\-]{4,50}$/', $rfid)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid RFID format'
    ]);
    exit();
}

try {
    // Initialize services
    $auditService = new AuditService($pdo);
    // Use System Admin (ID: NULL) for automated scans
    $studentService = new StudentService($pdo, $auditService, null);
    $voucherService = new VoucherService($pdo, $auditService, null);

    // 1. Find Student by RFID (fallback: typed Student ID)
    $student = $studentService->getByRFID($rfid);
    if (!$student) {
        $studentByIdStmt = $pdo->prepare("SELECT * FROM users WHERE student_id = ? AND deleted_at IS NULL LIMIT 1");
        $studentByIdStmt->execute([$rfid]);
        $student = $studentByIdStmt->fetch();
    }

    if (!$student) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Student not found for this RFID tag'
        ]);
        exit();
    }

    // 2. Check Rate Limit: max 2 vouchers per 60 minutes and max 2 vouchers per day
    $rateLimitStmt = $pdo->prepare("
        SELECT COUNT(*) as redemption_count 
        FROM student_vouchers 
        WHERE student_id = ? 
        AND redeemed_at >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)
    ");
    $rateLimitStmt->execute([$student['student_id']]);
    $rateLimitResult = $rateLimitStmt->fetch();
    $recentRedemptions = (int)$rateLimitResult['redemption_count'];

    $dailyLimitStmt = $pdo->prepare("
        SELECT COUNT(*) as redemption_count
        FROM student_vouchers
        WHERE student_id = ?
        AND DATE(redeemed_at) = CURDATE()
    ");
    $dailyLimitStmt->execute([$student['student_id']]);
    $dailyRedemptions = (int)$dailyLimitStmt->fetchColumn();

    if ($recentRedemptions >= 2 || $dailyRedemptions >= 2) {
        $message = $recentRedemptions >= 2
            ? "Rate limit exceeded. Maximum 2 vouchers every 60 minutes."
            : "Daily limit reached. Maximum 2 vouchers per day.";
        http_response_code(429);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'student' => [
                'name' => "{$student['first_name']} {$student['last_name']}",
                'id' => $student['student_id'],
                'picture' => $student['picture'] ?? null
            ],
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'remaining_vouchers_in_hour' => max(0, 2 - $recentRedemptions),
            'remaining_vouchers_today' => max(0, 2 - $dailyRedemptions)
        ]);
        exit();
    }

    // 3. Find Available Voucher
    $vouchers = $voucherService->getAll(['status' => 'available'], 1);
    $voucher = !empty($vouchers) ? $vouchers[0] : null;

    // 3. Attempt to redeem voucher if available
    if ($voucher) {
        $redeemSuccess = $voucherService->redeem($voucher['id'], $student['student_id']);
        
        if (!$redeemSuccess) {
            // Redemption failed (possibly race condition - another request got the voucher)
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to redeem voucher. It may have been taken by another transaction.',
                'student' => [
                    'name' => "{$student['first_name']} {$student['last_name']}",
                    'id' => $student['student_id'],
                    'picture' => $student['picture'] ?? null
                ]
            ]);
            exit();
        }
        
        // Success response
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Voucher redeemed successfully',
            'student' => [
                'name' => "{$student['first_name']} {$student['last_name']}",
                'id' => $student['student_id'],
                'picture' => $student['picture'] ?? null
            ],
            'voucher' => [
                'code' => $voucher['voucher_code'],
                'minutes' => intval($voucher['minutes_valid']),
                'department' => $voucher['office_department']
            ]
        ]);
    } else {
        // Student found but no vouchers available
        http_response_code(200);
        echo json_encode([
            'status' => 'warning',
            'message' => 'Student recognized, but no vouchers are currently available.',
            'student' => [
                'name' => "{$student['first_name']} {$student['last_name']}",
                'id' => $student['student_id'],
                'picture' => $student['picture'] ?? null
            ],
            'voucher' => null
        ]);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error. Please contact system administrator.',
        'debug' => IS_PRODUCTION ? null : $e->getMessage()
    ]);
    exit();
}
