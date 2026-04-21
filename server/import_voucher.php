<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/simple_spreadsheet_reader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];

        try {
            $rows = loadSpreadsheetRows($fileTmpPath, $_FILES['excel_file']['name'] ?? '');

            $admin_id = $_SESSION['admin_id'] ?? null;
            $auditService = new AuditService($pdo);
            $voucherService = new VoucherService($pdo, $auditService, $admin_id);
            $inserted = 0;
            $skipped = 0;
            $invalid = 0;
            $processed = 0;
            $headerSkipped = false;

            foreach ($rows as $row) {
                $row = array_pad($row, 3, '');
                $voucher_code = trim((string)$row[0]);
                $office = trim((string)$row[1]);
                $minutes_valid_raw = trim((string)$row[2]);

                if (!$headerSkipped) {
                    $headerProbe = strtolower($voucher_code . ' ' . $office . ' ' . $minutes_valid_raw);
                    if (strpos($headerProbe, 'voucher') !== false || strpos($headerProbe, 'office') !== false || strpos($headerProbe, 'minute') !== false) {
                        $headerSkipped = true;
                        continue;
                    }
                    $headerSkipped = true;
                }

                if ($voucher_code === '' && $office === '' && $minutes_valid_raw === '') {
                    continue;
                }

                $processed++;
                $minutes_valid = (int)$minutes_valid_raw;

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
                } else {
                    $invalid++;
                }
            }

            if ($inserted > 0) {
                $_SESSION['import_success'] = "Imported $inserted voucher(s). Skipped duplicates: $skipped. Invalid rows: $invalid.";
            } elseif ($processed === 0) {
                $_SESSION['import_error'] = "No data rows found. Check your file columns: Voucher Code, Office/Department, Minutes.";
            } else {
                $_SESSION['import_error'] = "No new vouchers imported. Duplicates: $skipped. Invalid rows: $invalid.";
            }
        } catch (Exception $e) {
            $_SESSION['import_error'] = "Import failed: " . $e->getMessage();
        }
    } else {
        $_SESSION['import_error'] = "Please upload a valid Excel file.";
    }

    header("Location: ../client/vouchers.php");
    exit();
} else {
    echo "Invalid request method.";
}
