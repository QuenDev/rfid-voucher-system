<?php
include 'config.php';

require 'vendor/autoload.php'; // If you're using PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filter = $_POST['filter'] ?? 'daily';

$whereClause = "";
switch ($filter) {
    case 'weekly':
        $whereClause = "WHERE YEARWEEK(sv.redeemed_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'monthly':
        $whereClause = "WHERE MONTH(sv.redeemed_at) = MONTH(CURDATE()) AND YEAR(sv.redeemed_at) = YEAR(CURDATE())";
        break;
    case 'yearly':
        $whereClause = "WHERE YEAR(sv.redeemed_at) = YEAR(CURDATE())";
        break;
    default:
        $whereClause = "WHERE DATE(sv.redeemed_at) = CURDATE()";
        break;
}


$sql = "
SELECT 
  CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS student_name,
  u.student_id,
  v.voucher_code,
  sv.redeemed_at
FROM student_vouchers sv
JOIN users u ON sv.student_id = u.student_id
JOIN vouchers v ON sv.voucher_id = v.id
$whereClause
ORDER BY sv.redeemed_at DESC
";

$result = $conn->query($sql);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['Student Name', 'Student ID', 'Voucher Code', 'Date Redeemed'], NULL, 'A1');

$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A{$rowNum}", $row['student_name']);
    $sheet->setCellValue("B{$rowNum}", $row['student_id']);
    $sheet->setCellValue("C{$rowNum}", $row['voucher_code']);
    $sheet->setCellValue("D{$rowNum}", $row['redeemed_at']); // Use redeemed_at, not date_redeemed
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
