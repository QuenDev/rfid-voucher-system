<?php
require_once '../server/includes/auth.php';
require_once '../server/includes/db.php';
require_once '../server/includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: students.php");
    exit();
}

$student_id = intval($_GET['id']);

// Fetch the student details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: students.php");
    exit();
}

$pageTitle = "Student Details";
include 'includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card" style="padding: 0; overflow: hidden;">
        <!-- Card Header with Back Button -->
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center; background: #fafbfc;">
            <a href="students.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; transition: color 0.2s;" onmouseover="this.style.color='var(--accent-color)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> All Students
            </a>
            <div style="display: flex; gap: 0.75rem;">
                <a href="edit_student.php?id=<?= $student_id ?>" class="btn" style="background: #ebf8ff; color: #3182ce; padding: 8px 16px; font-size: 0.85rem;">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>

        <!-- Student Profile Content -->
        <div style="padding: 3rem 2.5rem;">
            <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 3rem;">
                <div style="width: 140px; height: 140px; border-radius: 50%; border: 4px solid var(--accent-color); padding: 4px; background: white; box-shadow: var(--shadow-md); margin-bottom: 1.5rem;">
                    <?php if (!empty($student['picture']) && file_exists('uploads/' . $student['picture'])): ?>
                        <img src="uploads/<?= htmlspecialchars($student['picture']) ?>" alt="Student" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-circle" style="font-size: 5rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h2 style="margin: 0; color: var(--text-main); font-size: 1.75rem;"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h2>
                <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                    <span style="background: #f0fff4; color: #2f855a; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #c6f6d5;">
                        <?= htmlspecialchars($student['course']) ?> - <?= htmlspecialchars($student['year']) ?>
                    </span>
                    <span style="background: #fffaf0; color: #9c4221; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #feebc8;">
                        Section <?= htmlspecialchars($student['section']) ?>
                    </span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; border-top: 1px solid #f1f5f9; padding-top: 2rem;">
                <div>
                    <h5 style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">Primary Identification</h5>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">RFID Tag Number</span>
                            <span style="font-weight: 600; color: var(--text-main); font-family: 'JetBrains Mono', monospace; letter-spacing: 0.02em;"><?= htmlspecialchars($student['rfid'] ?: 'N/A') ?></span>
                        </div>
                        <div>
                            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">University Student ID</span>
                            <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($student['student_id'] ?: 'N/A') ?></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h5 style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">Personal & Academic</h5>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Full Legal Name</span>
                            <span style="font-weight: 600; color: var(--text-main);">
                                <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?>
                            </span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Gender</span>
                                <span style="font-weight: 600; color: var(--text-main);"><?= $student['sex'] === 'F' ? 'Female' : ($student['sex'] === 'M' ? 'Male' : 'N/A') ?></span>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.25rem;">Middle Initial</span>
                                <span style="font-weight: 600; color: var(--text-main);"><?= $student['middle_name'] ? htmlspecialchars($student['middle_name'][0]) . '.' : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
