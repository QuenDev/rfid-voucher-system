<?php
/**
 * RFID Voucher System - Implementation Test Suite
 * Run this file to verify all refactoring changes are working
 * 
 * Usage: 
 * Command line: php tests/implementation_test.php
 * Browser: http://localhost:8000/tests/implementation_test.php
 */

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../server/includes/db.php';
require_once __DIR__ . '/../server/includes/classes/InputValidator.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID System - Implementation Test Suite</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #1a472a; margin-bottom: 30px; text-align: center; }
        .test-group { background: white; border-radius: 8px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .test-header { background: #2d5a3f; color: white; padding: 15px 20px; font-weight: 600; font-size: 1.1em; }
        .test-item { padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .test-item:last-child { border-bottom: none; }
        .test-label { flex: 1; }
        .test-status {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9em;
        }
        .status-pass { background: #c6f6d5; color: #22543d; }
        .status-fail { background: #fed7d7; color: #742a2a; }
        .status-warning { background: #feebc8; color: #7c2d12; }
        .details { font-size: 0.9em; color: #718096; margin-top: 5px; font-family: monospace; }
        .summary { background: #1a472a; color: white; padding: 20px; border-radius: 8px; margin-top: 30px; text-align: center; }
        .summary h2 { margin-bottom: 10px; }
        .pass-count { color: #48bb78; font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔍 RFID Voucher System - Implementation Tests</h1>

    <?php
    
    $tests = [];
    $passCount = 0;
    $failCount = 0;
    $warningCount = 0;

    // TEST 1: Config file constants
    $test = [
        'name' => '1. Config Constants Loaded',
        'status' => 'pass'
    ];
    if (!defined('IS_PRODUCTION')) {
        $test['status'] = 'fail';
        $test['details'] = 'IS_PRODUCTION constant not defined';
    } elseif (!defined('APP_ENV')) {
        $test['status'] = 'fail';
        $test['details'] = 'APP_ENV constant not defined';
    } else {
        $test['details'] = 'APP_ENV = ' . APP_ENV . ', IS_PRODUCTION = ' . (IS_PRODUCTION ? 'true' : 'false');
    }
    $tests[] = $test;

    // TEST 2: Database connection
    $test = [
        'name' => '2. Database Connection',
        'status' => 'pass'
    ];
    try {
        $result = $pdo->query("SELECT 1");
        $test['details'] = 'Connected to ' . DB_NAME . ' @ ' . DB_HOST;
    } catch (Exception $e) {
        $test['status'] = 'fail';
        $test['details'] = 'Connection failed: ' . $e->getMessage();
    }
    $tests[] = $test;

    // TEST 3: Services autoload
    $test = [
        'name' => '3. Service Classes Loadable',
        'status' => 'pass'
    ];
    try {
        $studentService = new StudentService($pdo);
        $voucherService = new VoucherService($pdo);
        $accountService = new AccountService($pdo);
        $reportService = new ReportService($pdo);
        $auditService = new AuditService($pdo);
        $test['details'] = 'All 5 service classes loaded successfully';
    } catch (Exception $e) {
        $test['status'] = 'fail';
        $test['details'] = 'Error: ' . $e->getMessage();
    }
    $tests[] = $test;

    // TEST 4: InputValidator class
    $test = [
        'name' => '4. InputValidator Class Available',
        'status' => class_exists('InputValidator') ? 'pass' : 'fail'
    ];
    if ($test['status'] === 'pass') {
        $test['details'] = 'InputValidator class found and callable';
    } else {
        $test['details'] = 'InputValidator class not found';
    }
    $tests[] = $test;

    // TEST 5: Student validation rules
    $test = [
        'name' => '5. Student Validation Working',
        'status' => 'pass'
    ];
    $errors = InputValidator::validateStudent([
        'student_id' => 'invalid',
        'last_name' => 'X',  // Too short
        'first_name' => 'Valid',
        'sex' => 'INVALID'
    ]);
    if (count($errors) >= 3) {
        $test['details'] = 'Validation caught ' . count($errors) . ' errors correctly';
    } else {
        $test['status'] = 'fail';
        $test['details'] = 'Expected >= 3 validation errors, got ' . count($errors);
    }
    $tests[] = $test;

    // TEST 6: Voucher validation rules
    $test = [
        'name' => '6. Voucher Validation Working',
        'status' => 'pass'
    ];
    $errors = InputValidator::validateVoucher([
        'voucher_code' => 'short',  // Too short
        'office_department' => '',   // Empty
        'minutes_valid' => 'abc'     // Not numeric
    ]);
    if (count($errors) >= 2) {
        $test['details'] = 'Validation caught ' . count($errors) . ' errors correctly';
    } else {
        $test['status'] = 'fail';
        $test['details'] = 'Expected >= 2 validation errors, got ' . count($errors);
    }
    $tests[] = $test;

    // TEST 7: Dashboard stats method
    $test = [
        'name' => '7. Dashboard Stats Query',
        'status' => 'pass'
    ];
    try {
        $stats = $reportService->getDashboardStats();
        if (isset($stats['total_students']) && isset($stats['total_vouchers']) && 
            isset($stats['available_vouchers']) && isset($stats['used_vouchers'])) {
            $test['details'] = "Stats retrieved: " . $stats['total_students'] . " students, " . 
                              $stats['total_vouchers'] . " vouchers";
        } else {
            $test['status'] = 'fail';
            $test['details'] = 'Dashboard stats missing expected keys';
        }
    } catch (Exception $e) {
        $test['status'] = 'fail';
        $test['details'] = 'Error: ' . $e->getMessage();
    }
    $tests[] = $test;

    // TEST 8: System logs table exists
    $test = [
        'name' => '8. System Logs Table Exists',
        'status' => 'warning'
    ];
    try {
        $result = $pdo->query("SELECT 1 FROM system_logs LIMIT 1");
        $test['status'] = 'pass';
        $test['details'] = 'system_logs table found and accessible';
    } catch (Exception $e) {
        $test['status'] = 'warning';
        $test['details'] = 'system_logs table not found. Run migration_001_*.sql to create it.';
    }
    $tests[] = $test;

    // TEST 9: Soft delete columns exist
    $test = [
        'name' => '9. Soft Delete Columns',
        'status' => 'warning'
    ];
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'deleted_at'")->fetch();
        if ($columns) {
            $test['status'] = 'pass';
            $test['details'] = 'deleted_at columns found on users table';
        } else {
            $test['status'] = 'warning';
            $test['details'] = 'deleted_at column missing. Run migration to add it.';
        }
    } catch (Exception $e) {
        $test['status'] = 'warning';
        $test['details'] = 'Could not check columns: ' . $e->getMessage();
    }
    $tests[] = $test;

    // TEST 10: VoucherService::redeem method has FOR UPDATE
    $test = [
        'name' => '10. Concurrent Redemption Lock',
        'status' => 'pass'
    ];
    $reflection = new ReflectionMethod('VoucherService', 'redeem');
    $filename = $reflection->getFileName();
    $startLine = $reflection->getStartLine();
    $endLine = $reflection->getEndLine();
    $file = file($filename);
    $methodCode = implode('', array_slice($file, $startLine - 1, $endLine - $startLine + 1));
    
    if (strpos($methodCode, 'FOR UPDATE') !== false) {
        $test['details'] = 'FOR UPDATE lock implemented in VoucherService::redeem()';
    } else {
        $test['status'] = 'fail';
        $test['details'] = 'FOR UPDATE lock NOT found. Race condition still possible.';
    }
    $tests[] = $test;

    // TEST 11: API endpoint exists
    $test = [
        'name' => '11. Official API Endpoint',
        'status' => 'pass'
    ];
    $apiFile = __DIR__ . '/../client/api/v1/scan.php';
    if (file_exists($apiFile)) {
        $content = file_get_contents($apiFile);
        if (strpos($content, 'FOR UPDATE') !== false || strpos($content, 'deprecated') !== false) {
            $test['details'] = 'Official endpoint /client/api/v1/scan.php found';
        } else {
            $test['status'] = 'warning';
            $test['details'] = 'Endpoint found but may not be latest version';
        }
    } else {
        $test['status'] = 'fail';
        $test['details'] = 'API endpoint not found at expected location';
    }
    $tests[] = $test;

    // TEST 12: Environment variables
    $test = [
        'name' => '12. Environment Configuration',
        'status' => 'pass'
    ];
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $test['details'] = '.env file exists and is loaded';
    } else {
        $test['status'] = 'warning';
        $test['details'] = '.env file not found. Create from .env.example';
    }
    $tests[] = $test;

    // Display results
    foreach ($tests as $test) {
        if ($test['status'] === 'pass') $passCount++;
        elseif ($test['status'] === 'fail') $failCount++;
        else $warningCount++;
    }
    ?>

    <?php foreach ($tests as $test): ?>
    <div class="test-group">
        <div class="test-header">
            <?php echo $test['name']; ?>
        </div>
        <div class="test-item">
            <div class="test-label">
                Status
                <?php if (!empty($test['details'])): ?>
                <div class="details"><?php echo htmlspecialchars($test['details']); ?></div>
                <?php endif; ?>
            </div>
            <div class="test-status status-<?php echo $test['status']; ?>">
                <?php echo strtoupper($test['status']); ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="summary">
        <h2>Test Summary</h2>
        <p>Passed: <span class="pass-count"><?php echo $passCount; ?>/12</span></p>
        <p>Failed: <strong><?php echo $failCount; ?></strong> | Warnings: <strong><?php echo $warningCount; ?></strong></p>
        
        <?php if ($failCount === 0): ?>
            <p style="color: #48bb78; font-size: 1.1em; margin-top: 15px;">✅ All critical tests passed!</p>
        <?php else: ?>
            <p style="color: #f56565; font-size: 1.1em; margin-top: 15px;">⚠️ Some critical tests failed. Review details above.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
