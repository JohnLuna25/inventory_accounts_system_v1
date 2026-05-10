<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$createError = "";
$createSuccess = false;
$showVerification = false;

// Only process if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_number = trim($_POST['employee_number'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $employee_email = trim($_POST['employee_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'Accountant';

    if ($password !== $confirm_password) {
        $createError = "Passwords do not match.";
    } else {
        if ($role === 'Accountant') {
            // Accountant: Create directly
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO sorcom_users (employee_number, employee_name, employee_email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $employee_number, $fullname, $employee_email, $hashed_password, $role);

            if ($stmt->execute()) {
                $createSuccess = true;
            } else {
                $createError = "Failed to create account.";
            }
            $stmt->close();
        } else {
            // Plant Manager: Store in session and redirect to verification
            $_SESSION['pending_manager'] = [
                'master_id' => $employee_number,
                'master_name' => $fullname,
                'master_email' => $employee_email,
                'hashed_password' => password_hash($password, PASSWORD_BCRYPT)
            ];
            header("Location: manager_verification.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Create Account - SORCOM</title>
    <?php include 'ca_style.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .info-box {
            background: #e8f0fe;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-box p {
            margin: 5px 0;
            color: #333;
        }

        .info-box strong {
            color: blue;
        }

        .error {
            color: red;
            margin: 10px 0;
            padding: 8px;
            background: #ffe0e0;
            border-radius: 5px;
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
            margin: auto;
        }

        .password-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            padding: 10px;
            padding-right: 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #808080;
            font-size: 18px;
            z-index: 10;
        }
    </style>

    <?php if ($createSuccess): ?>
        <script>
            setTimeout(function () {
                window.location.href = "/Inventory-Accounts-System-v1/login_page/web_login.php";
            }, 3000);
        </script>
    <?php endif; ?>
</head>

<body>

    <?php if ($createSuccess): ?>
        <div class="success-box">
            Account created successfully!<br>Redirecting to login...
        </div>

    <?php elseif ($showVerification): ?>
        <!-- VERIFICATION FORM FOR PLANT MANAGER -->
        <form class="form-container" action="" method="post">
            <h2 style="color:blue; text-align:center;">Manager Verification Required</h2>
            <p style="text-align:center; margin-bottom:20px;">An existing Plant Manager must verify this new account.</p>

            <div class="info-box">
                <p><strong>New Manager Details:</strong></p>
                <p>ID: <?php echo htmlspecialchars($_SESSION['temp_employee_number']); ?></p>
                <p>Name: <?php echo htmlspecialchars($_SESSION['temp_fullname']); ?></p>
                <p>Email: <?php echo htmlspecialchars($_SESSION['temp_email']); ?></p>
            </div>

            <?php if ($verificationError): ?>
                <div class="error"><?php echo htmlspecialchars($verificationError); ?></div>
            <?php endif; ?>

            <label for="verification_password">Enter Existing Manager Password:</label>
            <div class="password-wrapper">
                <input type="password" id="verification_password" name="verification_password" required autocomplete="off">
                <i class="toggle-password fa-regular fa-eye-slash" id="toggleVerificationPassword"></i>
            </div>

            <button type="submit" name="verify_manager"
                style="width:100%; padding:12px; background:blue; color:white; border:none; border-radius:5px; margin-top:15px; cursor:pointer;">Verify
                & Create Account</button>
            <a href="create_account.php"
                style="display:block; text-align:center; margin-top:15px; color:blue; text-decoration:none;">← Cancel</a>
        </form>

    <?php else: ?>
        <!-- ORIGINAL CREATE ACCOUNT FORM -->
        <form class="form-container" action="" method="post">
            <!-- TRAP AUTO-FILL (Hidden fake fields) -->
            <input type="text" name="fakeusername" style="display:none" value="">
            <input type="password" name="fakepassword" style="display:none" value="">
            <input type="text" name="fakeemail" style="display:none" value="">

            <h2 style="color:blue; text-align:center;">Create Account</h2>

            <?php if ($createError): ?>
                <div class="error"><?= htmlspecialchars($createError) ?></div>
            <?php endif; ?>

            <label for="employee_number">ID Number <span class="required-field">*</span></label>
            <input type="text" id="employee_number" name="employee_number" autocomplete="new-employee-number" required>

            <label for="fullname">Full Name <span class="required-field">*</span></label>
            <input type="text" id="fullname" name="fullname" autocomplete="new-fullname" required>

            <label for="employee_email">Email Address <span class="required-field">*</span></label>
            <input type="text" id="employee_email" name="employee_email" placeholder="example@gmail.com"
                autocomplete="new-email" required>

            <!-- Radio Buttons for Role Selection -->
            <div class="role-selection">
                <span class="role-label">Account Type <span class="required-field">*</span></span>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="role" value="Accountant" checked> Accountant
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="role" value="master"> Plant Manager
                    </label>
                </div>
            </div>

            <label for="password">Password <span class="required-field">*</span></label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" autocomplete="new-password" required>
                <i class="toggle-password fa-regular fa-eye-slash" id="togglePassword"></i>
            </div>

            <label for="confirm_password">Confirm Password <span class="required-field">*</span></label>
            <div class="password-wrapper">
                <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
                <i class="toggle-password fa-regular fa-eye-slash" id="toggleConfirmPassword"></i>
            </div>

            <input type="submit" value="Create Account">
        </form>
    <?php endif; ?>

    <script>
        // Clear autofill
        window.addEventListener('DOMContentLoaded', function () {
            var inputs = document.querySelectorAll('input');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type !== 'hidden') {
                    inputs[i].readOnly = true;
                }
            }
            setTimeout(function () {
                for (var i = 0; i < inputs.length; i++) {
                    if (inputs[i].type !== 'hidden') {
                        inputs[i].readOnly = false;
                    }
                }
            }, 50);
        });

        // Password toggle for main form
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('mousedown', function () {
                passwordInput.setAttribute('type', 'text');
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            });
            togglePassword.addEventListener('mouseup', function () {
                passwordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            });
            togglePassword.addEventListener('mouseleave', function () {
                passwordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            });
        }

        // Confirm password toggle
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');
        if (toggleConfirmPassword && confirmPasswordInput) {
            toggleConfirmPassword.addEventListener('mousedown', function () {
                confirmPasswordInput.setAttribute('type', 'text');
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            });
            toggleConfirmPassword.addEventListener('mouseup', function () {
                confirmPasswordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            });
            toggleConfirmPassword.addEventListener('mouseleave', function () {
                confirmPasswordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            });
        }

        // Verification password toggle
        const toggleVerificationPassword = document.getElementById('toggleVerificationPassword');
        const verificationPasswordInput = document.getElementById('verification_password');
        if (toggleVerificationPassword && verificationPasswordInput) {
            toggleVerificationPassword.addEventListener('mousedown', function () {
                verificationPasswordInput.setAttribute('type', 'text');
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            });
            toggleVerificationPassword.addEventListener('mouseup', function () {
                verificationPasswordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            });
            toggleVerificationPassword.addEventListener('mouseleave', function () {
                verificationPasswordInput.setAttribute('type', 'password');
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            });
        }
    </script>

</body>

</html>