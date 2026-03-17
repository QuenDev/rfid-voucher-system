<?php
/**
 * RFID Scan API Endpoint
 * Handles RFID input and returns student/voucher data
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$rfid = trim($_POST['rfid'] ?? '');

if (empty($rfid)) {
    echo json_encode(['status' => 'error', 'message' => 'RFID tag is missing']);
    exit();
}

try {
    $studentService = new StudentService($pdo);
    $voucherService = new VoucherService($pdo);

    // 1. Find Student by RFID
    $student = $studentService->getByRFID($rfid);

    if (!$student) {
        echo json_encode(['status' => 'error', 'message' => 'Student not found for this RFID tag']);
        exit();
    }

    // 2. Find Available Voucher
    $vouchers = $voucherService->getAll(['status' => 'available']);
    $voucher = !empty($vouchers) ? $vouchers[0] : null;

    echo json_encode([
        'status' => 'success',
        'student' => [
            'last_name' => $student['last_name'],
            'first_name' => $student['first_name'],
            'student_id' => $student['student_id'],
            'picture' => $student['picture']
        ],
        'voucher' => $voucher ? ['voucher_code' => $voucher['voucher_code']] : null
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Internal server error: ' . $e->getMessage()]);
}
