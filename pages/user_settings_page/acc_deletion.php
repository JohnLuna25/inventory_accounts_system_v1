<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
require_once '../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check if user is logged in
if (!isset($_SESSION['employee_name'])) {
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

$employee_name = $_SESSION['employee_name'];
$user_type = $_SESSION['user_type'] ?? 'regular';
$error = "";
$showVerification = false;
$showRetypeForm = false;
$showConfirmModal = false;
$showSuccessModal = false;

// Determine which table to use
if ($user_type === 'master') {
    $table = 'sorcom_master_user';
    $id_column = 'master_id';
    $password_column = 'master_password';
    $name_column = 'master_name';
    $email_column = 'master_email';
    $session_id = $_SESSION['username'] ?? '';
} else {
    $table = 'sorcom_users';
    $id_column = 'employee_number';
    $password_column = 'password';
    $name_column = 'employee_name';
    $email_column = 'employee_email';
    $session_id = $_SESSION['username'] ?? '';
}

// Fetch current user data
$stmt = $conn->prepare("SELECT $name_column, $email_column FROM $table WHERE $id_column = ?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if account exists (if not, user was already deleted)
if ($result->num_rows === 0) {
    session_destroy();
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

$user_data = $result->fetch_assoc();
$user_email = $user_data[$email_column];
$user_fullname = $user_data[$name_column];
$stmt->close();

// Function to generate verification code (8 chars: 4 letters + 4 numbers, jumbled)
function generateVerificationCode()
{
    $letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $code = '';

    for ($i = 0; $i < 4; $i++) {
        $code .= $letters[random_int(0, strlen($letters) - 1)];
    }
    for ($i = 0; $i < 4; $i++) {
        $code .= $numbers[random_int(0, strlen($numbers) - 1)];
    }

    $code = str_shuffle($code);
    return $code;
}

// Function to send email
function sendVerificationEmail($to_email, $code)
{
    require_once '../../login_page/src/PHPMailer.php';
    require_once '../../login_page/src/SMTP.php';
    require_once '../../login_page/src/Exception.php';

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
        $mail->Subject = 'Account Deletion Verification - SORCOM';
        $mail->Body = "
            <h2>Account Deletion Request</h2>
            <p>Your verification code is: <strong style='font-size: 24px; color: red;'>{$code}</strong></p>
            <p>This code expires in <strong>10 minutes</strong>.</p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

// Handle verification code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $entered_code = trim($_POST['verification_code']);

    if (
        isset($_SESSION['delete_verification_code']) && isset($_SESSION['delete_code_expiry']) &&
        $entered_code == $_SESSION['delete_verification_code'] && time() < $_SESSION['delete_code_expiry']
    ) {
        $showRetypeForm = true;
        $showVerification = false;
    } else {
        $error = "Invalid or expired verification code. Please try again.";
    }
}

// Handle retype form submission (first confirmation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_retype'])) {
    $entered_name = trim($_POST['confirm_name']);
    $entered_password = $_POST['confirm_password'];

    // Verify name and password
    $stmt = $conn->prepare("SELECT $password_column FROM $table WHERE $id_column = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_check = $result->fetch_assoc();
    $stmt->close();

    if ($entered_name !== $user_fullname) {
        $error = "Name does not match our records.";
    } elseif (!password_verify($entered_password, $user_check[$password_column])) {
        $error = "Password is incorrect.";
    } else {
        $showConfirmModal = true;
        $showRetypeForm = false;
    }
}

// Handle final deletion - HARD DELETE from database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // HARD DELETE - permanently remove from database
    $stmt = $conn->prepare("DELETE FROM $table WHERE $id_column = ?");
    $stmt->bind_param("s", $session_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        session_destroy();
        $showSuccessModal = true;
    } else {
        $error = "Failed to delete account. Please try again.";
        $showConfirmModal = false;
        $showRetypeForm = true;
    }
    $stmt->close();
}

// Handle initial request - send verification code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_code'])) {
    $code = generateVerificationCode();
    $_SESSION['delete_verification_code'] = $code;
    $_SESSION['delete_code_expiry'] = time() + 600; // 10 minutes

    if (sendVerificationEmail($user_email, $code)) {
        $showVerification = true;
    } else {
        $error = "Failed to send verification email. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - SORCOM</title>
    <link rel="stylesheet" href="home.css">
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url("/Inventory-Accounts-System-v1/assets/images/SBP.png") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .form-container {
            max-width: 500px;
            margin: 80px auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .form-title {
            text-align: center;
            color: #2e3590;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
            font-family: 'Lexend Giga', sans-serif;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-family: 'Nunito', sans-serif;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        input:focus {
            outline: none;
            border-color: #2e3590;
        }

        .btn-submit,
        .btn-send {
            display: block;
            width: 100%;
            padding: 14px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.25s ease;
            border-left: 3px solid #ff4444;
            border-right: 3px solid transparent;
            text-align: center;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 15px;
            border: none;
        }

        .btn-submit:hover,
        .btn-send:hover {
            background: rgb(255, 49, 70) !important;
            color: white !important;
            transform: translateX(5px) !important;
            border-left-color: white !important;
            border-right-color: white !important;
        }

        .btn-back {
            display: block;
            width: 100%;
            padding: 14px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.25s ease;
            text-align: center;
        }

        .btn-back:hover {
            background-color: #5a6268;
            transform: translateX(5px);
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-msg {
            background-color: #cce5ff;
            color: #004085;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .warning-msg {
            background-color: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        hr {
            margin: 25px 0;
            border-color: #ddd;
        }

        .password-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 40px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 35%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #808080;
            font-size: 18px;
            z-index: 10;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .modal-card {
            background: white;
            border-radius: 30px;
            padding: 40px;
            text-align: center;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-card i {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .modal-card h3 {
            font-family: 'Lexend Giga', sans-serif;
            font-size: 22px;
            margin-bottom: 15px;
        }

        .modal-card p {
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            margin-bottom: 25px;
            color: #555;
        }

        .modal-card .btn-yes {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .modal-card .btn-no {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .modal-card .btn-yes:hover {
            background-color: #b02a37;
            transform: scale(1.02);
        }

        .modal-card .btn-no:hover {
            background-color: #5a6268;
            transform: scale(1.02);
        }

        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #2e3590;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .success-card {
            background: white;
            border-radius: 30px;
            padding: 40px 50px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .success-card i {
            font-size: 70px;
            color: #2e3590;
            margin-bottom: 20px;
        }

        .success-card h3 {
            color: #155724;
            font-family: 'Lexend Giga', sans-serif;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .success-card p {
            color: #2d6a2d;
            font-family: 'Nunito', sans-serif;
            font-size: 16px;
            margin-bottom: 25px;
        }

        .success-card .btn-done {
            background-color: #2e3590;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .success-card .btn-done:hover {
            background-color: #1a1f5e;
            transform: scale(1.02);
        }
    </style>
</head>

<body>

    <!-- First Confirmation Modal -->
    <?php if ($showConfirmModal): ?>
        <div class="modal-overlay">
            <div class="modal-card">
                <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                <h3>⚠️ Warning</h3>
                <p>Your account will be permanently deleted. This action cannot be undone.</p>
                <button class="btn-no" onclick="goBack()">No</button>
                <button class="btn-yes" onclick="showSecondConfirmation()">Yes</button>
            </div>
        </div>

        <script>
            function goBack() {
                window.location.href = "acc_deletion.php";
            }

            function showSecondConfirmation() {
                document.querySelector('.modal-overlay').innerHTML = `
            <div class="modal-card">
                <i class="fas fa-trash-alt" style="color: #dc3545;"></i>
                <h3>⚠️ Final Confirmation</h3>
                <p>Are you absolutely sure you want to permanently delete your account? This action cannot be undone.</p>
                <button class="btn-no" onclick="goBack()">No</button>
                <button class="btn-yes" onclick="deleteAccount()">Yes, Delete My Account</button>
            </div>
        `;
            }

            function deleteAccount() {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'confirm_delete';
                input.value = '1';
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        </script>
    </body>

    </html>
    <?php exit(); ?>
<?php endif; ?>

<!-- Success Modal -->
<?php if ($showSuccessModal): ?>
    <div class="success-modal">
        <div class="success-card">
            <i class="fas fa-check-circle"></i>
            <h3>Account Deleted</h3>
            <p>Your account has been permanently deleted from the system.</p>
            <button class="btn-done" onclick="redirectToLogin()">Done</button>
        </div>
    </div>

    <script>
        function redirectToLogin() {
            window.location.href = "/Inventory-Accounts-System-v1/login_page/web_login.php";
        }
    </script>
    </body>

    </html>
    <?php exit(); ?>
<?php endif; ?>

<div class="form-container">
    <?php if ($showRetypeForm): ?>
        <h2 class="form-title">
            <i class="fas fa-shield-alt"></i> Confirm Your Identity
        </h2>

        <div class="warning-msg">
            <i class="fas fa-exclamation-triangle"></i> This action is permanent and cannot be undone.
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="confirm_name">Full Name</label>
            <input type="text" id="confirm_name" name="confirm_name" placeholder="Enter your full name" required>

            <label for="confirm_password">Password</label>
            <div class="password-wrapper">
                <input type="password" id="confirm_password" name="confirm_password" required>
                <i class="toggle-password fa-regular fa-eye-slash" id="togglePassword"></i>
            </div>

            <button type="submit" name="confirm_retype" class="btn-submit">
                <i class="fas fa-trash-alt"></i> Permanently Delete Account
            </button>
        </form>

        <hr>

        <a href="user_settings.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Cancel
        </a>

    <?php elseif ($showVerification): ?>
        <h2 class="form-title">
            <i class="fas fa-envelope"></i> Verify Your Email
        </h2>
        <div class="info-msg">
            A verification code has been sent to: <strong><?php echo htmlspecialchars($user_email); ?></strong>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="verification_code">Enter Verification Code</label>
            <input type="text" id="verification_code" name="verification_code" placeholder="Enter 8-character code" required
                autocomplete="off">
            <button type="submit" name="verify_code" class="btn-submit">
                <i class="fas fa-check-circle"></i> Verify Code
            </button>
        </form>

        <hr>

        <a href="user_settings.php" class="btn-back">
            <i class="fas fa-times"></i> Cancel
        </a>

    <?php else: ?>
        <h2 class="form-title">
            <i class="fas fa-trash-alt"></i> Delete Account
        </h2>

        <div class="warning-msg">
            <i class="fas fa-exclamation-triangle"></i> Warning: This action is permanent and cannot be undone.
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="info-msg">
            <i class="fas fa-info-circle"></i> A verification code will be sent to your email:
            <strong><?php echo htmlspecialchars($user_email); ?></strong>
        </div>

        <form method="POST">
            <button type="submit" name="send_code" class="btn-send">
                <i class="fas fa-paper-plane"></i> Send Verification Code
            </button>
        </form>

        <hr>

        <a href="user_settings.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Settings
        </a>
    <?php endif; ?>
</div>

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('confirm_password');
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
</script>

</body>

</html>