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

    // --- Increment failed attempts only after login fails ---
    $waitMap = [3 => 30, 6 => 60, 9 => 90];

    // --- Check wait timer ---
    if (isset($_SESSION['next_attempt_time']) && $currentTime < $_SESSION['next_attempt_time']) {
        $remaining = $_SESSION['next_attempt_time'] - $currentTime;
        $_SESSION['error_type'] = 'wait';
        $_SESSION['login_error'] = "Please wait {$remaining} seconds before next attempt.";
        header("Location: web_login.php");
        exit();
    }

    $user_found = false;
    $user_data = null;
    $user_table = null; // 'sorcom_users' or 'sorcom_master_user'

    // --- STEP 1: Check sorcom_users (Accountants, Managers) ---
    $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password using bcrypt
        if (password_verify($password, $user['password'])) {
            $user_found = true;
            $user_data = $user;
            $user_table = 'sorcom_users';
        }
    }
    $stmt->close();

    // --- STEP 2: If not found in sorcom_users, check sorcom_master_user (Plant Managers) ---
    if (!$user_found) {
        $stmt = $conn->prepare("SELECT * FROM sorcom_master_user WHERE master_id = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password using bcrypt
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
            $_SESSION['role'] = $user_data['role']; // 'Accountant' or 'Manager'
            $_SESSION['user_type'] = 'regular';
        } else {
            // Plant Manager from sorcom_master_user
            $_SESSION['employee_name'] = $user_data['master_name'];
            $_SESSION['role'] = 'Plant Manager';
            $_SESSION['user_type'] = 'master';
        }

        // Reset counters
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
        unset($_SESSION['next_attempt_time'], $_SESSION['login_error'], $_SESSION['error_type'], $_SESSION['locked_employee_id']);

        header("Location: /Inventory-Accounts-System-v1/pages/home_page/home.php");
        exit();
    }

    // -------------------------
    // FAILED LOGIN
    // -------------------------
    $_SESSION['failed_attempts']++;
    $_SESSION['last_attempt_time'] = $currentTime;

    // --- Lock account on 10th failed attempt (only for regular users, not master) ---
    if ($_SESSION['failed_attempts'] >= 10) {
        $_SESSION['locked_employee_id'] = $username;
        $_SESSION['error_type'] = 'lock';
        $_SESSION['login_error'] = "Account locked. Master Login Override required.";
        header("Location: /Inventory-Accounts-System-v1/login_page/Master_Login_Override.php");
        exit();
    }

    // --- Apply wait thresholds ---
    if (isset($waitMap[$_SESSION['failed_attempts']])) {
        $waitTime = $waitMap[$_SESSION['failed_attempts']];
        $_SESSION['next_attempt_time'] = $currentTime + $waitTime;
        $_SESSION['error_type'] = 'wait';
        $_SESSION['login_error'] = "Please wait {$waitTime} seconds before next attempt.";
    } else {
        $_SESSION['error_type'] = 'invalid';
        $_SESSION['login_error'] = "Invalid username or password.";
    }

    header("Location: web_login.php");
    exit();
}
?>