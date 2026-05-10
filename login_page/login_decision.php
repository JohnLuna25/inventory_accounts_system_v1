<?php
require_once '../FunctionAndDB/db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Initialize session variables ---
$_SESSION['failed_attempts'] = $_SESSION['failed_attempts'] ?? 0;
$_SESSION['last_attempt_time'] = $_SESSION['last_attempt_time'] ?? 0;

// --- POST request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $currentTime = time();

    // --- Check if account is locked (after 9 failed attempts) ---
    if (isset($_SESSION['account_locked']) && $_SESSION['account_locked'] === true) {
        $_SESSION['error_type'] = 'lock';
        $_SESSION['login_error'] = "Account locked. Only Forgot Password is available.";
        header("Location: web_login.php");
        exit();
    }

    // --- Check wait timer (15 seconds lock after every 3 attempts) ---
    if (isset($_SESSION['next_attempt_time']) && $currentTime < $_SESSION['next_attempt_time']) {
        $remaining = $_SESSION['next_attempt_time'] - $currentTime;
        $_SESSION['error_type'] = 'wait';
        $_SESSION['login_error'] = "Please wait {$remaining} seconds before next attempt.";
        header("Location: web_login.php");
        exit();
    }

    $user_found = false;
    $user_data = null;
    $user_table = null;

    // --- STEP 1: Check sorcom_users (Accountants) ---
    $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $user_found = true;
            $user_data = $user;
            $user_table = 'sorcom_users';
        }
    }
    $stmt->close();

    // --- STEP 2: Check sorcom_master_user (Plant Managers) ---
    if (!$user_found) {
        $stmt = $conn->prepare("SELECT * FROM sorcom_master_user WHERE master_id = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['master_password'])) {
                $user_found = true;
                $user_data = $user;
                $user_table = 'sorcom_master_user';
            }
        }
        $stmt->close();
    }

    // -------------------------
    // VALID USER RECORD FOUND
    // -------------------------
    if ($user_found) {
        // Set common session variables
        $_SESSION['username'] = $username;

        if ($user_table === 'sorcom_users') {
            $_SESSION['employee_name'] = $user_data['employee_name'];
            $_SESSION['role'] = $user_data['role'];
            $_SESSION['user_type'] = 'regular';
        } else {
            $_SESSION['employee_name'] = $user_data['master_name'];
            $_SESSION['role'] = 'Plant Manager';
            $_SESSION['user_type'] = 'master';
        }

        // Reset all counters and locks
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        unset($_SESSION['next_attempt_time'], $_SESSION['login_error'], $_SESSION['error_type'], $_SESSION['account_locked']);

        header("Location: /Inventory-Accounts-System-v1/pages/home_page/home.php");
        exit();
    }

    // -------------------------
    // FAILED LOGIN
    // -------------------------
    $_SESSION['failed_attempts']++;
    $_SESSION['last_attempt_time'] = $currentTime;

    // --- Lock account after 9th failed attempt ---
    if ($_SESSION['failed_attempts'] >= 9) {
        $_SESSION['account_locked'] = true;
        $_SESSION['error_type'] = 'lock';
        $_SESSION['login_error'] = "Account locked. Only Forgot Password is available.";
        header("Location: web_login.php");
        exit();
    }

    // --- Apply 15-second lock after every 3 attempts (3rd, 6th) ---
    // Attempts 3 and 6 trigger a 15-second lock
    if ($_SESSION['failed_attempts'] == 3 || $_SESSION['failed_attempts'] == 6) {
        $_SESSION['next_attempt_time'] = $currentTime + 15;
        $_SESSION['error_type'] = 'wait';
        $_SESSION['login_error'] = "Please wait 15 seconds before next attempt.";
    } else {
        $_SESSION['error_type'] = 'invalid';
        $_SESSION['login_error'] = "Invalid username or password.";
    }

    header("Location: web_login.php");
    exit();
}
?>