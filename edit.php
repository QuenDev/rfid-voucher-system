<?php
session_start();
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Check if voucher ID is provided
if (!isset($_GET['voucher_id']) || !is_numeric($_GET['voucher_id'])) {
    $_SESSION['edit_error'] = "Invalid voucher ID.";
    header("Location: vouchers.php");
    exit();
}

$voucher_id = intval($_GET['voucher_id']);

// Fetch voucher details
$stmt = $conn->prepare("SELECT * FROM vouchers WHERE id = ?");
$stmt->bind_param("i", $voucher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['edit_error'] = "Voucher not found.";
    header("Location: vouchers.php");
    exit();
}

$voucher = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voucher_code = trim($_POST['voucher_code']);
    $office_department = trim($_POST['office_department']);
    $minutes_valid = intval($_POST['minutes_valid']);

    if (empty($voucher_code) || empty($office_department) || $minutes_valid < 1) {
        $_SESSION['edit_error'] = "Please fill in all fields correctly.";
    } else {
        try {
            $update_stmt = $conn->prepare("UPDATE vouchers SET voucher_code = ?, office_department = ?, minutes_valid = ? WHERE id = ?");
            $update_stmt->bind_param("ssii", $voucher_code, $office_department, $minutes_valid, $voucher_id);
            $update_stmt->execute();

            $_SESSION['edit_success'] = "Voucher updated successfully.";
            header("Location: vouchers.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                $_SESSION['edit_error'] = "Voucher code already exists.";
            } else {
                $_SESSION['edit_error'] = "Error updating voucher: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Voucher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 40px;
        }
        .edit-form {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-top: 0;
            color: #2f855a;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn {
            background: #38a169;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn:hover {
            background: #2f855a;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="edit-form">
    <h2>Edit Voucher</h2>

    <?php if (isset($_SESSION['edit_error'])): ?>
        <p class="error"><?= $_SESSION['edit_error']; unset($_SESSION['edit_error']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="voucher_code">Voucher Code:</label><br>
        <input type="text" name="voucher_code" value="<?= htmlspecialchars($voucher['voucher_code']) ?>" required><br>

        <label for="office_department">Office/Department:</label><br>
        <input type="text" name="office_department" value="<?= htmlspecialchars($voucher['office_department']) ?>" required><br>

        <label for="minutes_valid">Minutes Valid:</label><br>
        <input type="number" name="minutes_valid" value="<?= htmlspecialchars($voucher['minutes_valid']) ?>" min="1" required><br>

        <button class="btn" type="submit">💾 Save Changes</button>
    </form>
</div>

</body>
</html>
