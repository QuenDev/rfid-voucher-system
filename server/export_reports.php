require_once 'includes/db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filter = $_POST['filter'] ?? 'daily';
$reportService = new ReportService($pdo);

try {
    $report_data = $reportService->getRedemptionReport($filter);
} catch (Exception $e) {
    die("Export failed: " . $e->getMessage());
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['Student Name', 'Student ID', 'Voucher Code', 'Date Redeemed'], NULL, 'A1');

$rowNum = 2;
foreach ($report_data as $row) {
    $sheet->setCellValue("A{$rowNum}", $row['student_name']);
    $sheet->setCellValue("B{$rowNum}", $row['student_id']);
    $sheet->setCellValue("C{$rowNum}", $row['voucher_code']);
    $sheet->setCellValue("D{$rowNum}", $row['redeemed_at']); 
    $rowNum++;
}

$filename = "voucher_report_{$filter}_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
