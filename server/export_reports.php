<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();
validateCsrfToken();
require_once __DIR__ . '/includes/db.php';

$filter = $_POST['filter'] ?? 'daily';
$search = trim($_POST['search'] ?? '');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');
$reportService = new ReportService($pdo);

try {
    $report_data = $reportService->getRedemptionReport($filter, $search, null, null, $start_date, $end_date);
} catch (Exception $e) {
    die("Export failed: " . $e->getMessage());
}

$filename = "voucher_report_{$filter}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$output = fopen('php://output', 'w');

// UTF-8 BOM so Excel opens CSV with proper encoding.
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, ['Student Name', 'Student ID', 'Voucher Code', 'Date Redeemed']);

foreach ($report_data as $row) {
    fputcsv($output, [
        $row['student_name'] ?? '',
        $row['student_id'] ?? '',
        $row['voucher_code'] ?? '',
        $row['redeemed_at'] ?? ''
    ]);
}

fclose($output);
exit;
