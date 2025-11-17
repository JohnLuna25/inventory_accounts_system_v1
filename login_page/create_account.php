<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$createError = "";
$createSuccess = false; // boolean to track success
$confirm_password = $_POST['confirm_password'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);

    if (empty($employee_number) || empty($password) || empty($fullname)) {
        $createError = "All fields are required.";
    } elseif ($password !== $confirm_password) {  // <-- check passwords match
        $createError = "Passwords do not match.";
    } else {
        $hashed_password = hash('sha256', $password);

        $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ?");
        $stmt->bind_param("s", $employee_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $createError = "Employee number already exists!";
        } else {
            $stmt = $conn->prepare("INSERT INTO sorcom_users (employee_number, employee_name, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $employee_number, $fullname, $hashed_password);
            if ($stmt->execute()) {
                $createSuccess = true; // mark success
            } else {
                $createError = "Failed to create account. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Account - Sorcom</title>

    <?php include 'ca_style.php'; ?>

    <?php if ($createSuccess): ?>
        <script>


            setTimeout(function () {
                window.location.href = "http://localhost//Inventory-Accounts-System-v1/login_page/web_login.php";
            }, 3000);
        </script>
    <?php endif; ?>
</head>

<body>

    <?php if ($createSuccess): ?>
        <div class="success-box">
            Account created successfully!<br>Redirecting to login...
        </div>
    <?php else: ?>
        <form class="form-container" action="" method="post">
            <h2 style="color:blue; text-align:center;">Create Account</h2>

            <?php if ($createError): ?>
                <div class="message error"><?= htmlspecialchars($createError) ?></div>
            <?php endif; ?>

            <label for="employee_number">Employee Number:</label>
            <input type="text" id="employee_number" name="employee_number" required autocomplete="off">

            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required autocomplete="off">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required autocomplete="off">

            <!-- Confirm Password Field -->
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required autocomplete="off">

            <input type="submit" value="Create Account">
        </form>
    <?php endif; ?>

</body>

</html>