<?php
require_once '../server/includes/auth.php';
require_once '../server/includes/db.php';
require_once '../server/includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $studentService = new StudentService($pdo);
    
    $data = [
        'rfid' => trim($_POST['rfid']),
        'student_id' => trim($_POST['student_id']),
        'last_name' => trim($_POST['last_name']),
        'first_name' => trim($_POST['first_name']),
        'middle_name' => trim($_POST['middle_name']),
        'sex' => $_POST['sex'],
        'course' => trim($_POST['course']),
        'year' => intval($_POST['year']),
        'section' => trim($_POST['section']),
        'picture' => ""
    ];

    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = basename($_FILES['picture']['name']);
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = uniqid() . "." . $ext;

        $uploadPath = $uploadDir . $newFilename;
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $uploadPath)) {
            $data['picture'] = $newFilename;
        }
    }	

    try {
        if ($studentService->create($data)) {
            $_SESSION['add_success'] = "Student successfully added!";
        } else {
            $_SESSION['add_error'] = "Failed to add student.";
        }
    } catch (Exception $e) {
        $_SESSION['add_error'] = "Error: " . $e->getMessage();
    }

    header("Location: add_student.php");
    exit();
}

$pageTitle = "Add New Student";
include 'includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card" style="padding: 2.5rem;">
        <div style="margin-bottom: 2rem;">
            <a href="students.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Student Records
            </a>
            <h2 style="margin: 1.5rem 0 0.5rem; color: var(--text-main);">Add New Student</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Enter student details to register them in the system.</p>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['add_success'])): ?>
            <div style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['add_success']; unset($_SESSION['add_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['add_error'])): ?>
            <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 1.25rem; border-radius: var(--radius-sm); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['add_error']; unset($_SESSION['add_error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_student.php" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <!-- Left Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="rfid">RFID Tag No.</label>
                        <input type="text" name="rfid" id="rfid" class="form-control" placeholder="Scan or type RFID" required>
                    </div>

                    <div class="form-group">
                        <label for="student_id">University Student ID</label>
                        <input type="text" name="student_id" id="student_id" class="form-control" placeholder="XX-XXXXX" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="sex">Gender</label>
                            <select name="sex" id="sex" class="form-control" required>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div class="form-group">
                        <label for="course">Course / Program</label>
                        <input type="text" name="course" id="course" class="form-control" placeholder="e.g. BSIT" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="year">Year Level</label>
                            <input type="number" name="year" id="year" class="form-control" min="1" max="5" required>
                        </div>
                        <div class="form-group">
                            <label for="section">Section</label>
                            <input type="text" name="section" id="section" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="picture">Profile Photo</label>
                        <div style="border: 2px dashed #e2e8f0; border-radius: var(--radius-sm); padding: 1.5rem; text-align: center; background: #f8fafc; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--accent-color)'; this.style.background='#f0fff4'" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#f8fafc'">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 0.75rem; display: block;"></i>
                            <input type="file" name="picture" id="picture" accept="image/*" style="font-size: 0.9rem;">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">Recommended: Square JPG or PNG</p>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #edf2f7; display: flex; gap: 1.5rem; justify-content: flex-end;">
                <a href="students.php" class="btn" style="background: #edf2f7; color: var(--text-main); text-align: center; text-decoration: none; padding: 12px 30px;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-weight: 600;">Register Student</button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
