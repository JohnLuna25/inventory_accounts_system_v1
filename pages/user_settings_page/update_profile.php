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
$success = "";
$error = "";
$showVerification = false;
$new_email_temp = "";

// Determine which table to use
if ($user_type === 'master') {
    $table = 'sorcom_master_user';
    $id_column = 'master_id';
    $name_column = 'master_name';
    $email_column = 'master_email';
    $session_id = $_SESSION['username'] ?? '';
} else {
    $table = 'sorcom_users';
    $id_column = 'employee_number';
    $name_column = 'employee_name';
    $email_column = 'employee_email';
    $session_id = $_SESSION['username'] ?? '';
}

// Fetch current user data
$stmt = $conn->prepare("SELECT $name_column, $email_column, last_email_change FROM $table WHERE $id_column = ?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Function to validate TLD
function isValidEmailDomain($email)
{
    $disallowed_tlds = ['co']; // Add more if needed
    $domain = substr(strrchr($email, "@"), 1);
    $tld = substr(strrchr($domain, "."), 1);

    if (in_array(strtolower($tld), $disallowed_tlds)) {
        return false;
    }
    return true;
}

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

    // Jumble the code
    $code = str_shuffle($code);
    return $code;
}

// Function to send email using PHPMailer
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
        $mail->Subject = 'Email Verification Code - SORCOM';
        $mail->Body = "
            <h2>Email Verification</h2>
            <p>Your verification code is: <strong style='font-size: 24px; color: blue;'>{$code}</strong></p>
            <p>This code expires in <strong>10 minutes</strong>.</p>
            <p>If you did not request this change, please ignore this email.</p>
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
    $new_email = $_SESSION['temp_new_email'];
    $new_name = $_SESSION['temp_new_name'];

    if ($entered_code == $_SESSION['verification_code'] && time() < $_SESSION['code_expiry']) {
        // Update profile
        $stmt = $conn->prepare("UPDATE $table SET $name_column = ?, $email_column = ?, last_email_change = NOW() WHERE $id_column = ?");
        $stmt->bind_param("sss", $new_name, $new_email, $session_id);

        if ($stmt->execute()) {
            $_SESSION['employee_name'] = $new_name;
            unset($_SESSION['verification_code'], $_SESSION['code_expiry'], $_SESSION['temp_new_email'], $_SESSION['temp_new_name']);
            $_SESSION['profile_update_success'] = "Profile updated successfully!";
            header("Location: user_settings.php");
            exit();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Invalid or expired verification code. Please try again.";
    }
}

// Handle initial form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['fullname']);
    $new_email = trim($_POST['email']);

    if (empty($new_name) || empty($new_email)) {
        $error = "All fields are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!isValidEmailDomain($new_email)) {
        $error = "Invalid email domain. Please use a complete TLD (e.g., .com, .com.ph, .org).";
    } else {
        // Check if email is being changed
        $email_changed = ($new_email !== $user_data[$email_column]);

        if ($email_changed) {
            // Check 7-day cooldown
            // Check 7-day cooldown
            $last_change = $user_data['last_email_change'];
            if ($email_changed) {
                if ($last_change && $last_change != '0000-00-00 00:00:00' && $last_change != 'NULL') {
                    $last_change_timestamp = strtotime($last_change);
                    if ($last_change_timestamp !== false) {
                        $days_since = (time() - $last_change_timestamp) / (60 * 60 * 24);
                        if ($days_since < 7) {
                            $remaining = 7 - floor($days_since);
                            $error = "Email can only be changed once every 7 days. Please wait {$remaining} more day(s).";
                        }
                    }
                }
                // If last_change is NULL or invalid, allow the change
            }
        }

        if (empty($error)) {
            // Check if email already exists for another user
            $stmt = $conn->prepare("SELECT $id_column FROM $table WHERE $email_column = ? AND $id_column != ?");
            $stmt->bind_param("ss", $new_email, $session_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email already in use by another account.";
            } else {
                if ($email_changed) {
                    // Send verification code
                    $code = generateVerificationCode();
                    $_SESSION['verification_code'] = $code;
                    $_SESSION['code_expiry'] = time() + 600; // 10 minutes
                    $_SESSION['temp_new_email'] = $new_email;
                    $_SESSION['temp_new_name'] = $new_name;

                    if (sendVerificationEmail($new_email, $code)) {
                        $showVerification = true;
                        $new_email_temp = $new_email;
                    } else {
                        $error = "Failed to send verification email. Please try again.";
                    }
                } else {
                    // No email change, just update name
                    $stmt = $conn->prepare("UPDATE $table SET $name_column = ? WHERE $id_column = ?");
                    $stmt->bind_param("ss", $new_name, $session_id);

                    if ($stmt->execute()) {
                        $_SESSION['employee_name'] = $new_name;
                        $_SESSION['profile_update_success'] = "Profile updated successfully!";
                        header("Location: user_settings.php");
                        exit();
                    } else {
                        $error = "Failed to update profile. Please try again.";
                    }
                    $stmt->close();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Update - SORCOM</title>
    <link rel="stylesheet" href="home.css">
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

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
            margin-top: 120px;
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

        .btn-save,
        .btn-verify {
            display: block;
            width: 100%;
            padding: 14px 20px;
            background-color: #2e3590;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.25s ease;
            border-left: 3px solid transparent;
            border-right: 3px solid transparent;
            text-align: center;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 15px;
            border: none;
        }

        .btn-save:hover,
        .btn-verify:hover {
            background: #f1b33f !important;
            color: #303030 !important;
            transform: translateX(5px) !important;
            border-left-color: #ffffff !important;
            border-right-color: #ffffff !important;
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

        .success-msg {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
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

        hr {
            margin: 25px 0;
            border-color: #ddd;
        }
    </style>
</head>

<body>
    <?php showHeader() ?>

    <div class="form-container">
        <?php if ($showVerification): ?>
            <h2 class="form-title">
                <i class="fas fa-envelope"></i> Verify Your Email
            </h2>
            <div class="info-msg">
                A verification code has been sent to: <strong><?php echo htmlspecialchars($new_email_temp); ?></strong>
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="verification_code">Enter Verification Code</label>
                <input type="text" id="verification_code" name="verification_code" placeholder="Enter 8-digit code" required
                    autocomplete="off">
                <button type="submit" name="verify_code" class="btn-verify">
                    <i class="fas fa-check-circle"></i> Verify & Update Profile
                </button>
            </form>

            <hr>

            <a href="user_settings.php" class="btn-back">
                <i class="fas fa-times"></i> Cancel
            </a>

        <?php else: ?>
            <h2 class="form-title">
                <i class="fas fa-user-edit"></i> Update Profile
            </h2>

            <?php if ($success): ?>
                <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="fullname">Full Name</label>
                <input type="text" id="fullname" name="fullname"
                    value="<?php echo htmlspecialchars($user_data[$name_column] ?? ''); ?>" required>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                    value="<?php echo htmlspecialchars($user_data[$email_column] ?? ''); ?>" required>
                <small style="color: #666; display: block; margin-top: 10px; margin-bottom: 15px;">
                    <i class="fas fa-info-circle"></i> Email can only be changed once every 7 days
                </small>

                <button type="submit" name="update_profile" class="btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>

            <hr>

            <a href="user_settings.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Settings
            </a>
        <?php endif; ?>
    </div>

</body>

</html>