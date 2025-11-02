<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$createError = "";
$createSuccess = false; // boolean to track success

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);

    if (empty($employee_number) || empty($password) || empty($fullname)) {
        $createError = "All fields are required.";
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: blue;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: lightgray;
            padding: 50px;
            border-radius: 40px;
            width: 340px;
            box-sizing: border-box;
            text-align: left;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: blue;
            color: yellow;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
        }

        .error {
            color: red;
        }

        .success-box {
            background-color: #BFC3AA;
            width: 70%;
            height: 30%;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 40px;
            font-size: 30px;
            font-weight: bold;
            color: blue;
            text-align: center;
        }
    </style>
    <?php if ($createSuccess): ?>
        <script>
            // Redirect after 3 seconds
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

            <input type="submit" value="Create Account">
        </form>
    <?php endif; ?>

</body>

</html>