<?php
session_start();

// Show errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Only allow managers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    echo "<h2>Access denied. Only managers can add another manager.</h2>";
    exit();
}

$createError = "";
$createSuccess = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $fullname = trim($_POST['fullname']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($employee_number) || empty($fullname) || empty($password) || empty($confirm_password)) {
        $createError = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $createError = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if employee number exists
        $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ?");
        $stmt->bind_param("s", $employee_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $createError = "Employee number already exists!";
        } else {
            $role = 'Manager'; // auto-assign role

            $stmt = $conn->prepare(
                "INSERT INTO sorcom_users (employee_number, employee_name, password, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $employee_number, $fullname, $hashed_password, $role);

            if ($stmt->execute()) {
                $createSuccess = true;
            } else {
                $createError = "Failed to create manager account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Manager - SORCOM</title>
    <link rel="stylesheet" href="add_manager.css">
</head>

<body>
    <div class="container">

        <?php if ($createSuccess): ?>
            <div class="success-box">
                Manager account created successfully!<br>Redirecting to home...
            </div>
            <script>
                setTimeout(function () {
                    window.location.href = "/Inventory-Accounts-System-v1/pages/home_page/home.php";
                }, 3000);
            </script>
        <?php else: ?>
            <form class="add-manager-form" action="" method="post">
                <h2>Add New Manager</h2>

                <?php if ($createError): ?>
                    <div class="message error"><?= htmlspecialchars($createError) ?></div>
                <?php endif; ?>

                <label for="employee_number">Employee ID:</label>
                <input type="text" id="employee_number" name="employee_number" required autocomplete="off">

                <label for="fullname">Full Name:</label>
                <input type="text" id="fullname" name="fullname" required autocomplete="off">

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required autocomplete="off">

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="off">

                <!-- Buttons -->
                <div class="form-buttons">
                    <input type="submit" value="Create New Manager Account">
                    <button type="button"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/pages/home_page/home.php'">
                        Go Back
                    </button>
                </div>

                <!-- Auto-assigned role info (hidden) -->
                <input type="hidden" name="role" value="Manager">
            </form>
        <?php endif; ?>

    </div>
</body>

</html>