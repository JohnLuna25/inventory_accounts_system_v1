<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$overrideError = "";
$resetSuccess = false;

// --- Handle POST requests ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Master Login Override ---
    if (isset($_POST['master_username'])) {
        $employee_id_input = trim($_POST['employee_id']);
        $master_username = trim($_POST['master_username']);
        $master_password = $_POST['master_password'];

        // Check if employee exists
        $stmt = $conn->prepare("SELECT employee_number, employee_name FROM sorcom_users WHERE employee_number = ?");
        $stmt->bind_param("s", $employee_id_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $employee = $result->fetch_assoc();

            // Check master credentials
            $stmt2 = $conn->prepare("SELECT * FROM sorcom_master_user WHERE master_username = ?");
            $stmt2->bind_param("s", $master_username);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2->num_rows === 1) {
                $master = $result2->fetch_assoc();
                if ($master_password === $master['master_password']) {
                    // SUCCESS → unlock employee
                    $_SESSION['override_employee_id'] = $employee['employee_number'];
                    $_SESSION['override_employee_name'] = $employee['employee_name'];
                } else {
                    $overrideError = "Invalid Master Password.";
                }
            } else {
                $overrideError = "Master Username not found.";
            }

        } else {
            $overrideError = "Employee ID not found in system.";
        }
    }

    // --- Force Password Change ---
    elseif (isset($_POST['new_password'])) {
        $employee_id = $_SESSION['override_employee_id'] ?? '';
        $employee_name = $_SESSION['override_employee_name'] ?? '';
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($employee_id)) {
            $overrideError = "No employee selected for password reset.";
        } elseif ($new_password !== $confirm_password) {
            $overrideError = "Passwords do not match.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE sorcom_users SET password = ? WHERE employee_number = ?");
            $stmt->bind_param("ss", $hashed, $employee_id);
            if ($stmt->execute()) {
                $resetSuccess = true;
                unset($_SESSION['override_employee_id']);
                unset($_SESSION['override_employee_name']);
            } else {
                $overrideError = "Failed to reset password. Try again.";
            }
        }
    }
}

// --- Pre-fill Employee ID if available ---
$employee_id = $_SESSION['override_employee_id'] ?? '';
$employee_name = $_SESSION['override_employee_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Master Login Override</title>
    <style>
        body {
            font-family: Arial;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f0f0f0;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 20px;
            width: 350px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            background: blue;
            color: white;
            cursor: pointer;
            border: none;
            font-weight: bold;
        }

        .error {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="form-container">

        <?php if ($resetSuccess): ?>
            <div class="success">Password changed successfully! Redirecting to home...</div>
            <script>
                setTimeout(() => {
                    window.location.href = '/Inventory-Accounts-System-v1/pages/home_page/home.php';
                }, 2000);
            </script>

        <?php elseif (!isset($_SESSION['override_employee_id'])): ?>
            <h2 style="text-align:center;">Master Login Override</h2>
            <?php if ($overrideError)
                echo "<div class='error'>{$overrideError}</div>"; ?>
            <form method="post" autocomplete="off">
                <label>Employee ID</label>
                <input type="text" name="employee_id" required>
                <label>Master Username</label>
                <input type="text" name="master_username" required autocomplete="off">
                <label>Master Password</label>
                <input type="password" name="master_password" required autocomplete="new-password">
                <input type="submit" value="Unlock Account">
            </form>

        <?php else: ?>
            <h2 style="text-align:center;">Password Change Required</h2>
            <p style="text-align:center;">You must update your password to continue using the system.</p>
            <?php if ($overrideError)
                echo "<div class='error'>{$overrideError}</div>"; ?>
            <form method="post" autocomplete="off">
                <label>Employee ID</label>
                <input type="text" name="employee_id" value="<?= htmlspecialchars($employee_id) ?>" readonly>
                <label>Employee Fullname</label>
                <input type="text" name="employee_name" value="<?= htmlspecialchars($employee_name) ?>" readonly>
                <label>New Password</label>
                <input type="password" name="new_password" required autocomplete="new-password">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required autocomplete="new-password">
                <input type="submit" value="Proceed">
            </form>
        <?php endif; ?>

    </div>
</body>

</html>