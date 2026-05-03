<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

// Initialize variables
$createError = "";
$createSuccess = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $master_id = trim($_POST['master_id']);
    $master_name = trim($_POST['master_name']);
    $master_email = trim($_POST['master_email']);
    $master_password = $_POST['master_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($master_id) || empty($master_name) || empty($master_email) || empty($master_password) || empty($confirm_password)) {
        $createError = "All fields are required.";
    } elseif ($master_password !== $confirm_password) {
        $createError = "Passwords do not match.";
    } else {
        // Hash the password securely
        $hashed_password = password_hash($master_password, PASSWORD_BCRYPT);

        // Check if master_id already exists
        $stmt = $conn->prepare("SELECT * FROM sorcom_master_user WHERE master_id = ?");
        $stmt->bind_param("s", $master_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $createError = "Manager ID already exists!";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM sorcom_master_user WHERE master_email = ?");
            $stmt->bind_param("s", $master_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $createError = "Email already registered!";
            } else {
                // Insert into sorcom_master_user table
                $stmt = $conn->prepare(
                    "INSERT INTO sorcom_master_user (master_id, master_name, master_email, master_password) VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("ssss", $master_id, $master_name, $master_email, $hashed_password);

                if ($stmt->execute()) {
                    $createSuccess = true;
                } else {
                    $createError = "Failed to create Plant Manager account. Please try again.";
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
    <title>Create Plant Manager Account - SORCOM</title>
    <?php include 'mca_style.php'; ?>

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
            Plant Manager account created successfully!<br>Redirecting to login...
        </div>
    <?php else: ?>
        <form class="form-container" action="" method="post">
            <!-- TRAP AUTO-FILL (Hidden fake fields) -->
            <input type="text" name="fakeusername" style="display:none" value="">
            <input type="password" name="fakepassword" style="display:none" value="">
            <input type="text" name="fakeemail" style="display:none" value="">

            <h2 style="color:blue; text-align:center;">Create Plant Manager Account</h2>

            <?php if ($createError): ?>
                <div class="message error"><?= htmlspecialchars($createError) ?></div>
            <?php endif; ?>

            <label for="master_id">Manager ID Number <span class="required-field">*</span></label>
            <input type="text" id="master_id" name="master_id" autocomplete="new-master-id" required>

            <label for="master_name">Full Name <span class="required-field">*</span></label>
            <input type="text" id="master_name" name="master_name" autocomplete="new-name" required>

            <label for="master_email">Email Address <span class="required-field">*</span></label>
            <input type="text" id="master_email" name="master_email" placeholder="example@gmail.com"
                autocomplete="new-email" required>

            <label for="master_password">Password <span class="required-field">*</span></label>
            <input type="password" id="master_password" name="master_password" autocomplete="new-password" required>

            <label for="confirm_password">Confirm Password <span class="required-field">*</span></label>
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>

            <input type="submit" value="Create Plant Manager Account">
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
                var fullname = document.getElementById('master_name');
                if (fullname && fullname.value !== '') {
                    fullname.value = '';
                }

                var email = document.getElementById('master_email');
                if (email && email.value !== '') {
                    email.value = '';
                }
            }, 100);
        });
    </script>

</body>

</html>