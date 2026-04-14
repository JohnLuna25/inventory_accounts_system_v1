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

    <style>
        /* Background with overlay for better readability */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url('/Inventory-Accounts-System-v1/assets/images/SBP.png') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }

        /* Header styling */
        .main-header {
            background: #0d6efd;
            color: #ffff00;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            padding: 10px 0;
        }

        .main-header img {
            max-height: 70px;
            width: auto;
        }

        .header-title {
            font-weight: bold;
            font-size: 1.1rem;
        }

        .header-subtitle {
            font-size: 0.85rem;
        }

        .clock-display {
            font-weight: bold;
            letter-spacing: 0.05em;
            font-size: 0.9rem;
        }

        /* Main content */
        .main-content {
            padding-top: 140px;
            padding-bottom: 80px;
            min-height: 100vh;
        }

        /* Welcome message */
        .welcome-message {
            color: #ffff00;
            font-weight: bold;
            font-size: 1.3rem;
            text-shadow: 1px 1px 2px black;
            margin-bottom: 30px;
        }

        /* Dashboard buttons container */
        .dashboard-container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Button styling */
        .dashboard-btn {
            margin-top: 20px;
            width: 100%;
            max-width: 300px;
            padding: 15px 20px;
            font-size: 1rem;
            border-radius: 50px;
            white-space: normal;
            word-wrap: break-word;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .dashboard-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .btn-primary-custom {
            background-color: #6ea4f5;
            border: none;
        }

        .btn-primary-custom:hover {
            background-color: #0b5ed7;
        }

        .btn-danger-custom {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger-custom:hover {
            background-color: #c82333;
        }

        /* Footer */
        .main-footer {
            background: #0d6efd;
            color: #ffd34e;
            text-align: center;
            padding: 10px;
            font-size: 0.8rem;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-content {
                padding-top: 180px;
            }

            .header-title {
                font-size: 0.9rem;
            }

            .header-subtitle {
                font-size: 0.75rem;
            }

            .clock-display {
                font-size: 0.75rem;
            }

            .welcome-message {
                font-size: 1.1rem;
                padding: 0 10px;
            }

            .dashboard-btn {
                max-width: 100%;
                padding: 12px 15px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .main-header img {
                max-height: 50px;
            }

            .header-title {
                font-size: 0.8rem;
            }

            .main-content {
                padding-top: 140px;
                padding-bottom: 70px;
            }

            .welcome-message {
                font-size: 1rem;
            }

            .dashboard-btn {
                font-size: 0.85rem;
                padding: 10px 12px;
            }
        }

        @media (max-width: 400px) {
            .main-content {
                padding-top: 140px;
            }

            .welcome-message {
                font-size: 1rem;
                padding-bottom: 0px;
            }

            .dashboard-btn {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>

    <!-- Header -->
    <header class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-3 col-md-2 text-center">
                    <img src="/Inventory-Accounts-System-v1/assets/images/SRMCP.jpg" alt="SORCOM Logo"
                        class="img-fluid rounded">
                </div>

                <!-- Title and Subtitle -->
                <div class="col-6 col-md-8 text-center">
                    <h1 class="header-title mb-1">SORCOM READY-MIX CONCRETE</h1>
                    <p class="header-subtitle mb-0">Inventory and Accounts Management System</p>
                </div>

                <!-- Clock -->
                <div class="col-3 col-md-2 text-end">
                    <p id="clock" class="clock-display mb-0"></p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="text-center">
                <span class="welcome-message d-inline-block">
                    Welcome, <?php echo $employee_name; ?>!
                </span>
            </div>

            <!-- Dashboard Buttons -->
            <div class="dashboard-container">
                <div class="row g-3 justify-content-center">

                    <!-- Concrete Design List -->
                    <div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
                        <button class="btn btn-primary-custom dashboard-btn"
                            onclick="window.location.href='/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php'">
                            Concrete Design List
                        </button>
                    </div>

                    <!-- Inventory Management: Manager only -->
                    <?php if ($role === 'Manager'): ?>
                        <div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
                            <button class="btn btn-primary-custom dashboard-btn"
                                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php'">
                                Inventory Management
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- User Accounts -->
                    <div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
                        <button class="btn btn-primary-custom dashboard-btn"
                            onclick="window.location.href='/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php'">
                            Transaction Records
                        </button>
                    </div>

                    <!-- Reports: Manager only -->
                    <?php if ($role === 'Manager'): ?>
                        <div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
                            <button class="btn btn-primary-custom dashboard-btn"
                                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/reports_page/reports_management.php'">
                                Reports
                            </button>
                        </div>
                    <?php endif; ?>












                    <!-- Logout -->
                    <div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
                        <button id="logout-button" class="btn btn-danger-custom dashboard-btn"
                            onclick="if(confirm('Did you save all changes?')){if(confirm('Are you sure you want to log out?')){window.location.href='/Inventory-Accounts-System-v1/login_page/web_login.php';}};">
                            Logout
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
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
            const clockEl = document.getElementById('clock');
            if (clockEl) {
                clockEl.textContent = `${dateStr} || ${hours}:${minutes}:${seconds}`;
            }
        }
        setInterval(realClockTime, 1000);
        realClockTime();
    </script>

    <!-- Bootstrap JS -->
    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>