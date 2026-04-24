<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

// Initialize variables
$createError = "";
$createSuccess = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number']);
    $fullname = trim($_POST['fullname']);
    $employee_email = trim($_POST['employee_email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($employee_number) || empty($fullname) || empty($employee_email) || empty($password) || empty($confirm_password)) {
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
            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_email = ?");
            $stmt->bind_param("s", $employee_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $createError = "Email already registered!";
            } else {
                // Automatically assign role = 'Accountant'
                $role = 'Accountant';

                $stmt = $conn->prepare(
                    "INSERT INTO sorcom_users (employee_number, employee_name, employee_email, password, role) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param("sssss", $employee_number, $fullname, $employee_email, $hashed_password, $role);

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
            <!-- TRAP AUTO-FILL (Hidden fake fields) -->
            <input type="text" name="fakeusername" style="display:none" value="">
            <input type="password" name="fakepassword" style="display:none" value="">
            <input type="text" name="fakeemail" style="display:none" value="">

            <h2 style="color:blue; text-align:center;">Create Employee Account</h2>

            <?php if ($createError): ?>
                <div class="message error"><?= htmlspecialchars($createError) ?></div>
            <?php endif; ?>

            <label for="employee_number">Employee Number <span class="required-field">*</span></label>
            <input type="text" id="employee_number" name="employee_number" autocomplete="new-employee-number" required>

            <label for="fullname">Full Name <span class="required-field">*</span></label>
            <input type="text" id="fullname" name="fullname" autocomplete="new-fullname" required>

            <label for="employee_email">Email Address <span class="required-field">*</span></label>
            <input type="text" id="employee_email" name="employee_email" placeholder="example@gmail.com"
                autocomplete="new-email" required>

            <label for="password">Password <span class="required-field">*</span></label>
            <input type="password" id="password" name="password" autocomplete="new-password" required>

            <label for="confirm_password">Confirm Password <span class="required-field">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>

            <input type="submit" value="Create Account">
        </form>
    <?php endif; ?>

    <script>
        // Kill autofill by temporarily setting readonly
        window.addEventListener('DOMContentLoaded', function () {
            var inputs = document.querySelectorAll('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].readOnly = true;
            }

            setTimeout(function () {
                for (var i = 0; i < inputs.length; i++) {
                    inputs[i].readOnly = false;
                }
            }, 50);

            setTimeout(function () {
                var fullname = document.getElementById('fullname');
                if (fullname && fullname.value !== '') {
                    fullname.value = '';
                }

                var email = document.getElementById('employee_email');
                if (email && email.value !== '') {
                    email.value = '';
                }
            }, 100);
        });

        document.querySelector('form')?.addEventListener('submit', function (e) {
            var fullname = document.getElementById('fullname');
            if (fullname && fullname.value === 'John Villaluna') {
                fullname.value = '';
            }
        });
    </script>

</body>

</html>