<?php
session_start();
require_once __DIR__ . "/includes/auth.php";
requireLogin();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . '/includes/simple_spreadsheet_reader.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $rows = loadSpreadsheetRows($file, $_FILES['excel_file']['name'] ?? '');

        $admin_id = $_SESSION['admin_id'] ?? null;
        $auditService = new AuditService($pdo);
        $studentService = new StudentService($pdo, $auditService, $admin_id);
        $pdo->beginTransaction();

        $importedCount = 0;
        $duplicateCount = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;  // Skip header row

            $row = array_pad($row, 9, ''); 
            [$student_id, $last_name, $first_name, $middle_name, $sex, $course, $year, $section, $rfid] = $row;

            $student_id = trim((string)$student_id);
            $last_name = trim((string)$last_name);
            $first_name = trim((string)$first_name);
            $middle_name = trim((string)$middle_name);
            $sex = strtoupper(trim((string)$sex));
            $course = trim((string)$course);
            $year = trim((string)$year);
            $section = trim((string)$section);
            $rfid = trim((string)$rfid);

            if (empty($student_id) || empty($last_name) || empty($first_name) || !in_array($sex, ['M', 'F'])) {
                continue; 
            }

            // Check for duplicates
            $existing = false;

            // Only check RFID duplicate if RFID is actually provided.
            if ($rfid !== '') {
                $existing = $studentService->getByRFID($rfid);
            }

            if (!$existing) {
                // Check active student_id duplicate.
                $stmt = $pdo->prepare("SELECT id FROM users WHERE student_id = ? AND deleted_at IS NULL");
                $stmt->execute([$student_id]);
                $existing = $stmt->fetch();
            }

            if ($existing) {
                $duplicateCount++;
                continue;
            }

            $studentService->create([
                'rfid' => $rfid !== '' ? $rfid : null,
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

header("Location: ../client/students.php");
exit();
