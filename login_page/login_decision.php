<?php
require_once '../FunctionAndDB/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize
$_SESSION['failed_attempts'] = $_SESSION['failed_attempts'] ?? 0;
$_SESSION['last_attempt_time'] = $_SESSION['last_attempt_time'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $hashed_password = hash('sha256', $password);
    $currentTime = time();

    // Determine wait time BEFORE attempting login
    $waitMap = [3 => 30, 6 => 60, 9 => 90];
    $waitTime = $waitMap[$_SESSION['failed_attempts']] ?? 0;

    // If still waiting from previous lock
    if ($waitTime > 0 && ($currentTime - $_SESSION['last_attempt_time']) < $waitTime) {
        $_SESSION['next_attempt_time'] = $_SESSION['last_attempt_time'] + $waitTime;
        $_SESSION['error_type'] = 'wait';
        $_SESSION['login_error'] = "Please wait {$waitTime} seconds before next attempt.";
        header("Location: web_login.php");
        exit();
    }

    // Check credentials
    $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        // SUCCESS
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['employee_number'];
        $_SESSION['employee_name'] = $user['employee_name'];

        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;

        unset($_SESSION['next_attempt_time'], $_SESSION['login_error'], $_SESSION['error_type']);
        header("Location: /Inventory-Accounts-System-v1/pages/home_page/home.php");
        exit();

    } else {

        // FAILED LOGIN
        $_SESSION['failed_attempts']++;
        $_SESSION['last_attempt_time'] = $currentTime;

        // Lock after 10th attempt
        if ($_SESSION['failed_attempts'] >= 10) {
            $_SESSION['login_error'] = "Account locked. Master Login Override required.";
            $_SESSION['error_type'] = 'lock';
            $_SESSION['locked_employee_id'] = $username;
            header("Location: /Inventory-Accounts-System-v1/login_page/Master_Login_Override.php");
            exit();
        }

        // Trigger wait thresholds
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
}
?>