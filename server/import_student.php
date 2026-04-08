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

        $studentService = new StudentService($pdo);
        $pdo->beginTransaction();

        $importedCount = 0;
        $duplicateCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;  // Skip header row

            $row = array_pad($row, 9, ''); 
            [$student_id, $last_name, $first_name, $middle_name, $sex, $course, $year, $section, $rfid] = $row;

            if (empty($student_id) || empty($last_name) || empty($first_name) || !in_array($sex, ['M', 'F'])) {
                continue; 
            }

            // Check for duplicates
            // We can add a more efficient method to StudentService later if needed
            $existing = $studentService->getByRFID($rfid);
            if (!$existing) {
                // Also check ID
                $stmt = $pdo->prepare("SELECT id FROM users WHERE student_id = ?");
                $stmt->execute([$student_id]);
                $existing = $stmt->fetch();
            }

            if ($existing) {
                $duplicateCount++;
                continue;
            }

            $studentService->create([
                'rfid' => $rfid,
                'student_id' => $student_id,
                'last_name' => $last_name,
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'sex' => $sex,
                'course' => $course,
                'year' => $year,
                'section' => $section,
                'picture' => null
            ]);
            $importedCount++;
        }

        $pdo->commit();
        $_SESSION['import_success'] = " {$importedCount} student(s) imported,  {$duplicateCount} duplicate(s).";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollback();
        $_SESSION['import_error'] = "Import failed: " . $e->getMessage();
    }
}

header("Location: students.php");
exit();
