<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];
$studentService = new StudentService($pdo);

// Get the student ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid student ID!");
}

$id = intval($_GET['id']);
$student = $studentService->getById($id);

if (!$student) {
    die("Student not found!");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        'rfid' => trim($_POST['rfid']),
        'student_id' => trim($_POST['student_id']),
        'last_name' => trim($_POST['last_name']),
        'first_name' => trim($_POST['first_name']),
        'middle_name' => trim($_POST['middle_name']),
        'sex' => $_POST['sex'],
        'course' => trim($_POST['course']),
        'year' => intval($_POST['year']),
        'section' => trim($_POST['section'])
    ];

    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $new_filename = uniqid() . "_" . basename($_FILES["picture"]["name"]);
        $target_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
            // Remove old picture if it exists
            if (!empty($student['picture']) && file_exists($upload_dir . $student['picture'])) {
                unlink($upload_dir . $student['picture']);
            }
            $data['picture'] = $new_filename; 
        }
    }

    try {
        if ($studentService->update($id, $data)) {
            $_SESSION['edit_success'] = "Student updated successfully!";
            header("Location: edit_student.php?id=" . $id);
            exit();
        } else {
            $error = "Failed to update student.";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$pageTitle = "Edit Student";
include 'includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem;">
            <a href="students.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-arrow-left"></i> Back to Student Records
            </a>
            <h2 style="margin: 1.5rem 0 0.5rem; color: var(--text-main);">Edit Student Profile</h2>
        </div>

        <?php if (isset($_SESSION['edit_success'])): ?>
            <div style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['edit_success']; unset($_SESSION['edit_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="display: flex; justify-content: center; margin-bottom: 2.5rem;">
                <div style="position: relative; width: 120px; height: 120px;">
                    <?php if (!empty($student['picture'])): ?>
                        <img src="uploads/<?= htmlspecialchars($student['picture']) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid var(--accent-color);">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; border-radius: 50%; background: #f7fafc; display: flex; align-items: center; justify-content: center; border: 4px solid #e2e8f0; color: #cbd5e0;">
                            <i class="fas fa-user" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                    <label for="picture" style="position: absolute; bottom: 0; right: 0; width: 36px; height: 36px; background: var(--accent-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white; transition: transform 0.2s;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" name="picture" id="picture" accept="image/*" style="display: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="form-group">
                    <label>RFID Tag No.</label>
                    <input type="text" name="rfid" class="form-control" value="<?= htmlspecialchars($student['rfid']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" value="<?= htmlspecialchars($student['student_id']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($student['middle_name']) ?>">
                </div>

                <div class="form-group">
                    <label>Gender/Sex</label>
                    <select name="sex" class="form-control" required>
                        <option value="M" <?= $student['sex'] == 'M' ? 'selected' : '' ?>>Male</option>
                        <option value="F" <?= $student['sex'] == 'F' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Course</label>
                    <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($student['course']) ?>" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Year Level</label>
                        <input type="number" name="year" class="form-control" value="<?= htmlspecialchars($student['year']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <input type="text" name="section" class="form-control" value="<?= htmlspecialchars($student['section']) ?>" required>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #edf2f7; display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="students.php" class="btn" style="background: #edf2f7; color: var(--text-main); text-decoration: none; padding: 12px 24px;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 32px;">Update Record</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
