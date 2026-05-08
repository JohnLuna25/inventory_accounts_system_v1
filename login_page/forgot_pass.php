<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';
require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';
$resend_cooldown = 20; // seconds
$code_expiry = 180; // 3 minutes

// Email sending function using SMTP
function sendResetCode($to_email, $code)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sorcom.system.00@gmail.com';
        $mail->Password = 'oozk paix ycgw rtmr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('sorcom.system.00@gmail.com', 'SORCOM System');
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = 'SORCOM Password Reset Code';
        $mail->Body = "
            <h2>Password Reset Request</h2>
            <p>Your 6-digit verification code is: <strong style='font-size: 20px; color: blue;'>{$code}</strong></p>
            <p>This code expires in <strong>3 minutes</strong>.</p>
            <p>If you did not request this, please ignore this email.</p>
        ";
        $mail->AltBody = "Your password reset code is: {$code}\n\nThis code expires in 3 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

// Handle Resend Code
if (isset($_GET['resend']) && $_GET['resend'] == 1) {
    if (isset($_SESSION['last_resend_time']) && (time() - $_SESSION['last_resend_time']) < $resend_cooldown) {
        $remaining = $resend_cooldown - (time() - $_SESSION['last_resend_time']);
        $error = "Please wait {$remaining} seconds before resending the code.";
    } elseif (isset($_SESSION['reset_employee'])) {
        $new_code = rand(100000, 999999);
        $_SESSION['reset_code'] = $new_code;
        $_SESSION['reset_expires'] = time() + $code_expiry;
        $_SESSION['last_resend_time'] = time();

        if (sendResetCode($_SESSION['reset_email'], $new_code)) {
            $success = "New code sent to your email!";
        } else {
            $success = "DEMO MODE: New code is <strong>{$new_code}</strong><br>(Email failed to send. For demo purposes, code is shown here.)";
        }
        $step = 2;
    } else {
        $error = "Session expired. Please start over.";
        $step = 1;
    }
}

// STEP 1: Verify Identity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_identity'])) {
    $full_name = trim($_POST['full_name']);
    $employee_number = trim($_POST['employee_number']);
    $employee_email = trim($_POST['employee_email']);

    if (empty($full_name) || empty($employee_number) || empty($employee_email)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("SELECT employee_number, employee_name, employee_email FROM sorcom_users WHERE employee_number = ? AND employee_name = ? AND employee_email = ?");
        $stmt->bind_param("sss", $employee_number, $full_name, $employee_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $code = rand(100000, 999999);

            $_SESSION['reset_employee'] = $user['employee_number'];
            $_SESSION['reset_fullname'] = $user['employee_name'];
            $_SESSION['reset_email'] = $user['employee_email'];
            $_SESSION['reset_code'] = $code;
            $_SESSION['reset_expires'] = time() + $code_expiry;
            $_SESSION['reset_attempts'] = 0;
            $_SESSION['last_resend_time'] = time();

            if (sendResetCode($user['employee_email'], $code)) {
                $success = "✅ Code sent to your email! <br>Code expires in 3 minutes.";
            } else {
                $success = "DEMO MODE: Your verification code is <strong>{$code}</strong><br>(Email failed to send. For demo purposes, code is shown here.)";
            }
            $step = 2;
        } else {
            $error = "Information does not match our records. Please check and try again.";
        }
    }
}

// STEP 2: Verify Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $entered_code = trim($_POST['verification_code']);

    if (isset($_SESSION['reset_attempts']) && $_SESSION['reset_attempts'] >= 3) {
        if (isset($_SESSION['reset_lock_time']) && time() < $_SESSION['reset_lock_time']) {
            $remaining = $_SESSION['reset_lock_time'] - time();
            $error = "Too many failed attempts. Please wait {$remaining} seconds.";
        } else {
            $_SESSION['reset_attempts'] = 0;
            unset($_SESSION['reset_lock_time']);
        }
    }

    if (empty($error)) {
        if (isset($_SESSION['reset_code']) && isset($_SESSION['reset_expires']) && time() < $_SESSION['reset_expires']) {
            if ($entered_code == $_SESSION['reset_code']) {
                $_SESSION['reset_verified'] = true;
                $step = 3;
            } else {
                $_SESSION['reset_attempts'] = ($_SESSION['reset_attempts'] ?? 0) + 1;

                if ($_SESSION['reset_attempts'] >= 3) {
                    $_SESSION['reset_lock_time'] = time() + 30;
                    $error = "Too many failed attempts. Please wait 30 seconds.";
                } else {
                    $remaining_attempts = 3 - $_SESSION['reset_attempts'];
                    $error = "Invalid code. {$remaining_attempts} attempt(s) remaining.";
                }
            }
        } else {
            $error = "Code expired. Please request a new code.";
            $step = 1;
            unset($_SESSION['reset_code'], $_SESSION['reset_expires']);
        }
    }
}

// STEP 3: Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
        header("Location: forgot_pass.php");
        exit;
    }

    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Password fields cannot be empty.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE sorcom_users SET password = ? WHERE employee_number = ?");
        $stmt->bind_param("ss", $hashed, $_SESSION['reset_employee']);

        if ($stmt->execute()) {
            unset(
                $_SESSION['reset_employee'],
                $_SESSION['reset_fullname'],
                $_SESSION['reset_email'],
                $_SESSION['reset_code'],
                $_SESSION['reset_expires'],
                $_SESSION['reset_verified'],
                $_SESSION['reset_attempts'],
                $_SESSION['reset_lock_time'],
                $_SESSION['last_resend_time']
            );

            $success = "Password changed successfully! Redirecting to login...";
            header("refresh:3;url=web_login.php");
            $step = 4;
        } else {
            $error = "Failed to reset password. Please try again.";
        }
    }
}

$resend_remaining = 0;
if (isset($_SESSION['last_resend_time'])) {
    $resend_remaining = $resend_cooldown - (time() - $_SESSION['last_resend_time']);
    if ($resend_remaining < 0)
        $resend_remaining = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password - SORCOM</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background-color: blue;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .recovery-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 450px;
            max-width: 100%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        h2 {
            color: blue;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 14px;
        }

        button {
            background-color: blue;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0000cc;
        }

        button:disabled {
            background-color: #999;
            cursor: not-allowed;
        }

        .error {
            color: red;
            margin: 10px 0;
            padding: 8px;
            background: #ffe0e0;
            border-radius: 5px;
        }

        .success {
            color: green;
            margin: 10px 0;
            padding: 8px;
            background: #e0ffe0;
            border-radius: 5px;
        }

        .back-link {
            margin-top: 15px;
            display: block;
            color: blue;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .resend-btn {
            background-color: #6c757d;
            margin-top: 5px;
        }

        .resend-btn:hover {
            background-color: #5a6268;
        }

        .password-wrapper {
            position: relative;
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        .password-wrapper input {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
            border-radius: 8px;
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

        /* Make asterisk in placeholder red */
        input[placeholder*="*"]::-webkit-input-placeholder {
            color: red;
            opacity: 0.7;
        }

        input[placeholder*="*"]::-moz-placeholder {
            color: red;
            opacity: 0.7;
        }

        input[placeholder*="*"]:-ms-input-placeholder {
            color: red;
            opacity: 0.7;
        }

        input[placeholder*="*"]:-moz-placeholder {
            color: red;
            opacity: 0.7;
        }
    </style>

</head>

<body>
    <div class="recovery-container">
        <?php if ($step == 4): ?>
            <h2>✓ Password Reset Complete!</h2>
            <div class="success"><?= $success ?></div>
            <a href="web_login.php" class="back-link">← Go to Login</a>

        <?php elseif ($step == 3): ?>
            <h2>Reset Password</h2>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="password-wrapper">
                    <input type="password" name="new_password" id="new_password" placeholder="New Password *"
                        autocomplete="new-password" required>
                    <i class="toggle-password fa-regular fa-eye-slash" id="toggleNewPassword"></i>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password *"
                        autocomplete="new-password" required>
                    <i class="toggle-password fa-regular fa-eye-slash" id="toggleConfirmPassword"></i>
                </div>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
            <a href="forgot_pass.php" class="back-link">← Cancel</a>

        <?php elseif ($step == 2): ?>
            <h2>Verify Code</h2>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?= $success ?></div><?php endif; ?>
            <p>Enter the 6-digit code sent to your email</p>
            <form method="POST" autocomplete="off">
                <input type="text" name="verification_code" placeholder="Enter 6-digit code *" maxlength="6"
                    autocomplete="off" required>
                <button type="submit" name="verify_code">Verify Code</button>
            </form>
            <form method="GET" style="margin-top: 10px;">
                <input type="hidden" name="resend" value="1">
                <button type="submit" class="resend-btn" <?= $resend_remaining > 0 ? 'disabled' : '' ?>>
                    <?= $resend_remaining > 0 ? "Resend available in {$resend_remaining}s" : "Resend Code" ?>
                </button>
            </form>
            <a href="forgot_pass.php" class="back-link">← Start Over</a>

        <?php else: ?>
            <h2>Forgot Password?</h2>
            <p>Enter your account details to reset your password</p>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <form method="POST" autocomplete="off">
                <!-- TRAP FIELDS - Hidden off-screen -->
                <input type="text" name="fakeusername" style="position:absolute; top:-9999px; left:-9999px;" value="">
                <input type="password" name="fakepassword" style="position:absolute; top:-9999px; left:-9999px;" value="">
                <input type="email" name="fake_email" style="display:none" value="">
                <input type="text" name="fake_name" style="display:none" value="">

                <input type="text" name="full_name" placeholder="Full Name *" autocomplete="off" required
                    onfocus="this.removeAttribute('readonly')" readonly>

                <input type="text" name="employee_number" placeholder="Employee Number *" autocomplete="off" required
                    onfocus="this.removeAttribute('readonly')" readonly>

                <input type="text" name="employee_email" placeholder="Email Address *" autocomplete="off" required
                    onfocus="this.removeAttribute('readonly')" readonly>

                <button type="submit" name="verify_identity">Verify Identity</button>
            </form>
            <a href="web_login.php" class="back-link">← Back to Login</a>
        <?php endif; ?>
    </div>

    <script>
        // Clear any autofilled values immediately
        window.addEventListener('DOMContentLoaded', function () {
            var inputs = document.querySelectorAll('input');
            for (var i = 0; i < inputs.length; i++) {
                if (inputs[i].type !== 'hidden' && inputs[i].style.position !== 'absolute' && inputs[i].style.display !== 'none') {
                    if (inputs[i].value !== '') {
                        inputs[i].value = '';
                    }
                }
            }
        });
    </script>

    <script>
        // New Password toggle
        const toggleNewPassword = document.getElementById('toggleNewPassword');
        const newPasswordInput = document.getElementById('new_password');

        toggleNewPassword.addEventListener('mousedown', function () {
            newPasswordInput.setAttribute('type', 'text');
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        });

        toggleNewPassword.addEventListener('mouseup', function () {
            newPasswordInput.setAttribute('type', 'password');
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        });

        toggleNewPassword.addEventListener('mouseleave', function () {
            newPasswordInput.setAttribute('type', 'password');
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        });

        // Confirm Password toggle
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');

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
    </script>

</body>

</html>