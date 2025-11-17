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
    $waitMap = [3 => 30, 6 => 60, 9 => 90]; // attempt number => wait seconds

    // --- Check wait timer ---
    if (isset($_SESSION['next_attempt_time']) && $currentTime < $_SESSION['next_attempt_time']) {
        $remaining = $_SESSION['next_attempt_time'] - $currentTime;
        $_SESSION['error_type'] = 'wait';
        $_SESSION['login_error'] = "Please wait {$remaining} seconds before next attempt.";
        header("Location: web_login.php");
        exit();
    }

    // --- Check credentials (fetch user by employee_number ONLY) ---
    $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // -------------------------
    // VALID USER RECORD FOUND
    // -------------------------
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // --- Verify password using bcrypt ---
        if (password_verify($password, $user['password'])) {

            // SUCCESSFUL LOGIN
            $_SESSION['username'] = $user['employee_number'];
            $_SESSION['employee_name'] = $user['employee_name'];

            // Reset counters
            $_SESSION['failed_attempts'] = 0;
            $_SESSION['last_attempt_time'] = 0;
            unset($_SESSION['next_attempt_time'], $_SESSION['login_error'], $_SESSION['error_type'], $_SESSION['locked_employee_id']);

            header("Location: /Inventory-Accounts-System-v1/pages/home_page/home.php");
            exit();
        }
    }

    // -------------------------
    // FAILED LOGIN
    // -------------------------
    $_SESSION['failed_attempts']++;
    $_SESSION['last_attempt_time'] = $currentTime;

    // --- Lock account on 10th failed attempt ---
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