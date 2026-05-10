<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$VERIFICATION_CODE = "@SoRcOm2024";

if (!isset($_SESSION['pending_manager'])) {
    header("Location: create_account.php");
    exit();
}

$verificationError = "";
$verificationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = $_POST['verification_code'] ?? '';

    if (empty($entered_code)) {
        $verificationError = "Please enter the verification code.";
    } elseif ($entered_code !== $VERIFICATION_CODE) {
        $verificationError = "Invalid verification code. Please try again.";
    } else {
        $pending = $_SESSION['pending_manager'];

        $stmt = $conn->prepare(
            "INSERT INTO sorcom_master_user (master_id, master_name, master_email, master_password) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssss",
            $pending['master_id'],
            $pending['master_name'],
            $pending['master_email'],
            $pending['hashed_password']
        );

        if ($stmt->execute()) {
            unset($_SESSION['pending_manager']);
            $verificationSuccess = true;
        } else {
            $verificationError = "Failed to create account: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manager Verification - SORCOM</title>
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

        .verification-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            width: 450px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        h2 {
            color: blue;
            margin-bottom: 20px;
        }

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

        .password-wrapper {
            position: relative;
            display: block;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
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
            background: white;
        }

        .toggle-password:hover {
            color: #555555;
        }
    </style>
</head>

<body>

    <?php if ($verificationSuccess): ?>
        <div class="verification-container">
            <div class="success">
                <i class="fas fa-check-circle" style="font-size: 50px; color: green;"></i>
                <h2>Plant Manager Account Created!</h2>
                <p>Redirecting to login...</p>
            </div>
        </div>
        <script>
            setTimeout(function () {
                window.location.href = "web_login.php";
            }, 3000);
        </script>
    <?php else: ?>
        <div class="verification-container">
            <h2><i class="fas fa-shield-alt"></i> Manager Verification</h2>
            <p style="color: #666; margin-bottom: 20px;">Enter the verification code to approve this new account.</p>

            <div class="info-box">
                <p><strong>New Manager Details:</strong></p>
                <p>ID: <?php echo htmlspecialchars($_SESSION['pending_manager']['master_id']); ?></p>
                <p>Name: <?php echo htmlspecialchars($_SESSION['pending_manager']['master_name']); ?></p>
                <p>Email: <?php echo htmlspecialchars($_SESSION['pending_manager']['master_email']); ?></p>
            </div>

            <?php if ($verificationError): ?>
                <div class="error"><?php echo htmlspecialchars($verificationError); ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="verification_code">Verification Code:</label>
                <div class="password-wrapper">
                    <input type="password" id="verification_code" name="verification_code" required autocomplete="off">
                    <i class="toggle-password fa-regular fa-eye-slash" id="toggleCode"></i>
                </div>
                <button type="submit">Verify & Create Account</button>
            </form>

            <a href="create_account.php" class="back-link">← Cancel</a>
        </div>
    <?php endif; ?>

</body>

<script>
    const toggleCode = document.getElementById('toggleCode');
    const codeInput = document.getElementById('verification_code');

    if (toggleCode && codeInput) {
        toggleCode.addEventListener('mousedown', function () {
            codeInput.setAttribute('type', 'text');
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        });
        toggleCode.addEventListener('mouseup', function () {
            codeInput.setAttribute('type', 'password');
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        });
        toggleCode.addEventListener('mouseleave', function () {
            codeInput.setAttribute('type', 'password');
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        });
    }
</script>

</html>