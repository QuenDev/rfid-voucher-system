<?php
require_once '../server/includes/auth.php';
require_once '../server/includes/db.php';
require_once '../server/includes/functions.php';

// Security Guard
requireLogin();

$admin_id = $_SESSION['admin_id'] ?? null;
$auditService = new AuditService($pdo);
$studentService = new StudentService($pdo, $auditService, $admin_id);

// Handle student delete
if (isset($_POST['confirm_delete'])) {
    validateCsrfToken();
    $student_id = $_POST['student_id'];
    if ($studentService->delete($student_id)) {
        $_SESSION['delete_success'] = "Student has been deleted successfully!";
    } else {
        $_SESSION['delete_error'] = "Failed to delete student.";
    }
    header("Location: students.php" . (isset($_GET['page']) ? "?page=" . $_GET['page'] : ""));
    exit();
}

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 15;
$offset = ($page - 1) * $limit;

try {
    $total_records = $studentService->getTotalCount($search);
    $total_pages = ceil($total_records / $limit);
    $student_list = $studentService->getAll($search, $limit, $offset);
} catch (Exception $e) {
    $_SESSION['import_error'] = "Database error: " . $e->getMessage();
    $student_list = [];
    $total_pages = 0;
}

$pageTitle = "Student Records";
include 'includes/header.php';

$appBase = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
?>

<div style="display: flex; flex-direction: column; gap: 2rem;">
    <!-- Actions Bar -->
    <div class="card" style="padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <!-- Search -->
            <form method="GET" style="flex: 1; min-width: 300px; display: flex; gap: 0.75rem;">
                <div style="position: relative; flex: 1;">
                    <i class="fas fa-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, ID, RFID, or course..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" style="padding-left: 2.75rem;">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if (!empty($_GET['search'])): ?>
                    <a href="students.php" class="btn" style="background: #edf2f7; color: var(--text-main);">Clear</a>
                <?php endif; ?>
            </form>

            <!-- Quick Actions -->
            <div style="display: flex; gap: 0.75rem;">
                <button onclick="document.getElementById('import-section').classList.toggle('hidden')" class="btn" style="background: #ebf8ff; color: #3182ce;">
                    <i class="fas fa-file-import"></i> Import Excel
                </button>
                <a href="add_student.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
        </div>

        <!-- Hidden Import Section -->
        <div id="import-section" class="<?php echo (isset($_SESSION['import_error']) || isset($_SESSION['import_success'])) ? '' : 'hidden'; ?>" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #edf2f7;">
            <div style="background: #f0fdf4; border: 1px dashed var(--accent-color); padding: 1.5rem; border-radius: var(--radius-md);">
                <form action="<?= htmlspecialchars($appBase . '/server/import_student.php') ?>" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <label for="excel" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Select Student File (.xlsx, .csv)</label>
                        <input type="file" name="excel_file" id="excel" accept=".xlsx,.csv,.xls" required class="form-control" style="background: white;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self: flex-end;">Upload & Import</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/alerts.php'; ?>

    <!-- Students Table -->
    <div class="card" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Student Info</th>
                        <th>RFID Tag</th>
                        <th>Course & Year</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($student_list)): ?>
                        <?php foreach ($student_list as $student): ?>
                            <tr>
                                <td style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($student['id']) ?></td>
                                <td>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></span>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($student['student_id'] ?: 'No ID') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <code style="background: #f7fafc; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;"><?= htmlspecialchars($student['rfid'] ?: 'Unassigned') ?></code>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem;">
                                        <?= htmlspecialchars($student['course']) ?> - <?= htmlspecialchars($student['year']) ?><?= htmlspecialchars($student['section']) ?>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <a href="view_student.php?id=<?= $student['id'] ?>" class="btn" style="padding: 6px; background: #f7fafc; color: var(--text-muted);" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn" style="padding: 6px; background: #ebf8ff; color: #3182ce;" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="students.php" style="display:inline;">
                                            <?php echo getCsrfField(); ?>
                                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                            <button type="submit" name="confirm_delete" class="btn" style="padding: 6px; background: #fff5f5; color: #e53e3e;" onclick="return confirm('Are you sure you want to delete this student?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                <i class="fas fa-user-slash" style="display: block; font-size: 3rem; margin-bottom: 1rem; opacity: 0.2;"></i>
                                No student records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; background: white; padding: 1rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
        <div style="font-size: 0.9rem; color: var(--text-muted);">
            Showing <?= count($student_list) ?> of <?= $total_records ?> students
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="btn" style="background: #edf2f7; color: var(--text-main); font-size: 0.85rem;"><i class="fas fa-chevron-left"></i> Previous</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn" style="background: <?= ($i == $page) ? 'var(--accent-color)' : '#edf2f7' ?>; color: <?= ($i == $page) ? 'white' : 'var(--text-main)' ?>; font-size: 0.85rem; padding: 6px 12px;"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="btn" style="background: #edf2f7; color: var(--text-main); font-size: 0.85rem;">Next <i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>


<?php include 'includes/footer.php'; ?>
