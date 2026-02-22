<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

// Initialize variables
$createError = "";
$createSuccess = false; // Track if account creation succeeded

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $fullname = trim($_POST['fullname']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($employee_number) || empty($fullname) || empty($password) || empty($confirm_password)) {
        $createError = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $createError = "Passwords do not match.";
    } else {
        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if employee number already exists
        $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ?");
        $stmt->bind_param("s", $employee_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $createError = "Employee number already exists!";
        } else {
            // Automatically assign role = 'Accountant'
            $role = 'Accountant';

            $stmt = $conn->prepare(
                "INSERT INTO sorcom_users (employee_number, employee_name, password, role) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $employee_number, $fullname, $hashed_password, $role);

            if ($stmt->execute()) {
                $createSuccess = true;

                // Reset login attempt session counters
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['last_attempt_time'] = 0;
            } else {
                $createError = "Failed to create account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Employee Account - SORCOM</title>
    <?php include 'ca_style.php'; ?>

    <?php if ($createSuccess): ?>
        <script>
            // Redirect to login after 3 seconds
            setTimeout(function () {
                window.location.href = "/Inventory-Accounts-System-v1/login_page/web_login.php";
            }, 3000);
        </script>
    <?php endif; ?>
</head>

<body>

    <?php if ($createSuccess): ?>
        <div class="success-box">
            Employee account created successfully!<br>Redirecting to login...
        </div>
    <?php else: ?>
        <form class="form-container" action="" method="post">
            <h2 style="color:blue; text-align:center;">Create Employee Account</h2>

            <?php if ($createError): ?>
                <div class="message error"><?= htmlspecialchars($createError) ?></div>
            <?php endif; ?>

            <label for="employee_number">Employee Number:</label>
            <input type="text" id="employee_number" name="employee_number" required autocomplete="off">

            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required autocomplete="off">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="off">

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="off">

            <input type="submit" value="Create Account">
        </form>
    <?php endif; ?>

</body>

</html>