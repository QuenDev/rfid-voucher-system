<?php
session_start();
require 'config.php';
require 'vendor/autoload.php'; // PhpSpreadsheet autoload

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($fileTmpPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $voucherService = new VoucherService($pdo);
            $inserted = 0;
            $skipped = 0;

            for ($i = 1; $i < count($rows); $i++) {
                $voucher_code = trim($rows[$i][0]);
                $office = trim($rows[$i][1]);
                $minutes_valid = intval($rows[$i][2]);

                if ($voucher_code && $office && $minutes_valid > 0) {
                    try {
                        if ($voucherService->create([
                            'voucher_code' => $voucher_code,
                            'office_department' => $office,
                            'minutes_valid' => $minutes_valid
                        ])) {
                            $inserted++;
                        }
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) { // Duplicate entry
                            $skipped++;
                        } else {
                            throw $e;
                        }
                    }
                }
            }

            $_SESSION['import_success'] = "Successfully imported $inserted vouchers.";
        } catch (Exception $e) {
            $_SESSION['import_error'] = "Import failed: " . $e->getMessage();
        }
    } else {
        $_SESSION['import_error'] = "Please upload a valid Excel file.";
    }

    header("Location: vouchers.php");
    exit();
} else {
    echo "Invalid request method.";
}
