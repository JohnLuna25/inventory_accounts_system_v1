<?php
session_start();
require_once '../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';


if (!isset($_SESSION['employee_name'])) {
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

$employee_name = $_SESSION['employee_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorcom User Settings</title>
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
            background:
                linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url("/Inventory-Accounts-System-v1/assets/images/SBP.png") no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', 'Nunito', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .settings-container {
            max-width: 500px;
            margin: 80px auto;
            margin-top: 120px;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .settings-title {
            text-align: center;
            color: #2e3590;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
            font-family: 'Lexend Giga', sans-serif;
        }

        .welcome-text {
            text-align: center;
            color: #555;
            margin-bottom: 30px;
            font-size: 20px;
            font-family: 'Nunito', sans-serif;
        }

        .settings-btn {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }

        .btn-update,
        .btn-password {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.25s ease;
            border-left: 3px solid transparent;
            border-right: 3px solid transparent;
            background-color: #2e3590;
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-update:hover,
        .btn-password:hover {
            background: #f1b33f !important;
            color: #303030 !important;
            transform: translateX(5px) !important;
            border-left-color: #ffffff !important;
            border-right-color: #ffffff !important;
        }

        .btn-delete {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.25s ease;
            border-left: 3px solid #fd2e2e;
            border-right: 3px solid transparent;
            background-color: #e0273a;
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-delete:hover {
            background: rgb(255, 49, 70) !important;
            color: white !important;
            transform: translateX(5px) !important;
            border-left-color: white !important;
            border-right-color: white !important;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            margin-top: 10px;
        }

        .btn-back:hover {
            background-color: #5a6268;
            color: white;
            transform: translateY(-2px);
        }

        hr {
            margin: 25px 0;
            border-color: #ddd;
        }
    </style>

</head>

<body>
    <?php showHeader() ?>

    <div class="settings-container">
        <h2 class="settings-title">
            <i class="fas fa-cog"></i> User Settings
        </h2>
        <p class="welcome-text">Welcome, <?php echo htmlspecialchars($employee_name); ?>!</p>

        <a href="update_profile.php" class="settings-btn btn-update">
            <i class="fas fa-user-edit"></i> Update Profile
        </a>

        <a href="change_pass.php" class="settings-btn btn-password">
            <i class="fas fa-key"></i> Change Password
        </a>

        <a href="acc_deletion.php" class="settings-btn btn-delete">
            <i class="fas fa-trash-alt"></i> Delete Account
        </a>

        <hr>

        <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php" class="settings-btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

</body>

</html>