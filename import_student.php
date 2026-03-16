<?php
session_start();
require "config.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $conn->begin_transaction();

        $importedCount = 0;
        $duplicateCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;  // Skip header row

            $row = array_pad($row, 9, ''); // Ensure 9 columns
            [$student_id, $last_name, $first_name, $middle_name, $sex, $course, $year, $section, $rfid] = $row;

            // Validate required fields
            if (empty($student_id) || empty($last_name) || empty($first_name) || !in_array($sex, ['M', 'F'])) {
                continue; // Skip invalid rows
            }

            // Check for duplicates
            $check = $conn->prepare("SELECT id FROM users WHERE student_id = ? OR rfid = ?");
            $check->bind_param("ss", $student_id, $rfid);
            $check->execute();
            $checkResult = $check->get_result();

            if ($checkResult->num_rows > 0) {
                $duplicateCount++;
                continue; // Skip duplicate
            }

            $picture = null; // Placeholder for picture

            // Insert student
            $stmt = $conn->prepare("INSERT INTO users (
                rfid, student_id, last_name, first_name, middle_name, sex, course, year, section, picture
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("ssssssssss", $rfid, $student_id, $last_name, $first_name, $middle_name, $sex, $course, $year, $section, $picture);
            $stmt->execute();
            $importedCount++;
        }

        $conn->commit();
        $_SESSION['import_success'] = " {$importedCount} student(s) imported,  {$duplicateCount} duplicate(s).";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['import_error'] = "Import failed: " . $e->getMessage();
    }
}

header("Location: students.php");
exit();
