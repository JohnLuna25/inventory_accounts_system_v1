<?php
session_start();
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$employee_name = isset($_SESSION['employee_name']) ? htmlspecialchars($_SESSION['employee_name']) : 'User';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Employee';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory and Accounts Management System</title>

    <!-- Bootstrap CSS -->
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional custom styles -->
    <?php include 'home_style.php'; ?>
</head>

<body style="margin:0; font-family: Arial, sans-serif; 
             background-image: url('/Inventory-Accounts-System-v1/assets/images/SBP.png');
             background-size:cover; 
             background-position:center; 
             background-repeat:no-repeat; 
             height:100vh;">

    <header class="position-fixed w-100 bg-primary" style="top:0; z-index:1040; height:110px; color: #ffff00;">
        <div class="container-fluid h-100 d-flex align-items-center justify-content-between position-relative">

            <!-- Left: Logo -->
            <div class="d-flex align-items-center">
                <img src="/Inventory-Accounts-System-v1/assets/images/SRMCP.jpg" alt="SORCOM Logo" height="60">
            </div>

            <!-- Right: Clock -->
            <div class="d-flex align-items-center">
                <p id="clock" class="mb-0" style="font-weight: bold; letter-spacing: 0.1em;"></p>
            </div>

            <!-- Center: Title + Subtitle (absolute, won't affect flex) -->
            <div class="position-absolute top-50 start-50 translate-middle text-center">
                <h1 class="h4 mb-2" style="font-weight: bold;">SORCOM READY-MIX CONCRETE</h1>
                <p class="mb-0" style="font-size: 1rem;">Inventory and Accounts Management System</p>
            </div>

        </div>
    </header>

    <!-- Main content -->
    <main id="main-content" class="container" style="padding-top:130px;">

        <!-- Welcome message above buttons -->
        <div class="text-center mb-4">
            <span class="fw-bold" style="color: #ffff00; 
         font-size: 1.5rem; 
         -webkit-text-stroke: 0.6px black; 
         text-stroke: 0.5px black;
         letter-spacing: 0.1em;">
                Welcome, <?php echo $employee_name; ?>!
            </span>
        </div>

        <!-- Dashboard buttons -->
        <div class="row row-cols-1 row-cols-md-2 g-5 justify-content-center">

            <!-- Concrete Design -->
            <div class="col d-flex justify-content-center">
                <button class="btn btn-primary btn-lg w-75 rounded-pill"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php'">
                    Concrete Design List
                </button>
            </div>

            <!-- Inventory Management: Manager only -->
            <?php if ($role === 'Manager'): ?>
                <div class="col d-flex justify-content-center">
                    <button class="btn btn-primary btn-lg w-75 rounded-pill"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php'">
                        Inventory Management
                    </button>
                </div>
            <?php endif; ?>

            <!-- User Accounts -->
            <div class="col d-flex justify-content-center">
                <button class="btn btn-primary btn-lg w-75 rounded-pill"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php'">
                    User Accounts
                </button>
            </div>

            <!-- Reports: Manager only -->
            <?php if ($role === 'Manager'): ?>
                <div class="col d-flex justify-content-center">
                    <button class="btn btn-primary btn-lg w-75 rounded-pill"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/pages/reports_management.php'">
                        Reports
                    </button>
                </div>
            <?php endif; ?>

            <!-- Settings -->
            <div class="col d-flex justify-content-center">
                <button class="btn btn-primary btn-lg w-75 rounded-pill"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/settings_management.php'">
                    Settings
                </button>
            </div>

            <!-- Add Manager: Manager only -->
            <?php if ($role === 'Manager'): ?>
                <div class="col d-flex justify-content-center">
                    <button class="btn btn-primary btn-lg w-75 rounded-pill"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/pages/add_manager_page/add_manager.php'">
                        Add Manager
                    </button>
                </div>

                <div class="col d-flex justify-content-center">
                    <button class="btn btn-primary btn-lg w-75 rounded-pill"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/pages/manage_employee_accounts/mea.php'">
                        Manage Employee Accounts
                    </button>
                </div>
            <?php endif; ?>

            <!-- Logout -->
            <div class="col d-flex justify-content-center">
                <button id="logout-button" class="btn btn-danger btn-lg w-75 rounded-pill"
                    onclick="if(confirm('Did you save all changes?')){if(confirm('Are you sure you want to log out?')){window.location.href='/Inventory-Accounts-System-v1/login_page/web_login.php';}};">
                    Logout
                </button>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-warning text-center py-2 fixed-bottom" style="font-size: px; text-align: center;">
        &copy; <?php echo date("Y"); ?> SORCOM Ready-Mix Concrete. All rights reserved.
    </footer>

    <!-- Scripts -->
    <script>
        // Clock
        function realClockTime() {
            const now = new Date();
            const options = { month: 'short', day: '2-digit', year: 'numeric' };
            const dateStr = now.toLocaleDateString('en-US', options).replace(',', '.');
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('clock').textContent = `${dateStr} || ${hours}:${minutes}:${seconds}`;
        }
        setInterval(realClockTime, 1000);
        realClockTime();
    </script>

    <!-- Bootstrap JS -->
    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>