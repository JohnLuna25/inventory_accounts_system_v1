<?php
// Start a session to store user data
session_start();

// Include external PHP files with helper functions
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php'; // Web functions

// Get the logged-in employee's name from session; default to 'User' if not set
$employee_name = isset($_SESSION['employee_name']) ? htmlspecialchars($_SESSION['employee_name']) : 'User';

// --- NEW: Get the role from session, default to Employee if not set ---
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Employee';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory and Accounts Management System</title>

    <!-- External stylesheet -->
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/css/sidebar.css">


    <?php include 'home_style.php'; ?>

</head>

<body style="margin:0; font-family: Arial, sans-serif; 
             background-image: url('/Inventory-Accounts-System-v1/assets/images/SBP.png');
             background-size:cover; 
             background-position:center; 
             background-repeat:no-repeat; 
             height:100vh;">

    <!-- Header section with title, subtitle, logo, and clock -->
    <div style="background-color: blue; padding: 7px; width: 100%; position: fixed; text-align: center;">
        <h1 style="font-size: 30px; font-weight: bold; color: yellow;">SORCOM READY-MIX CONCRETE</h1>
        <p style="font-size: 20px; font-weight: 600; color: yellow;">Inventory and Accounts Management System.</p>

        <img src="/Inventory-Accounts-System-v1/assets/images/SRMCP.jpg" alt="SORCOM Logo"
            style="height:80px; width:auto; position:absolute; top:25px; left:45px;">

        <p id="clock"
            style="font-size: 18px; font-weight: 400; color: yellow; position: absolute; top: 60px; right: 30px; margin: 0;">
            <!-- JavaScript will update the clock here -->
        </p>
    </div>

    <!-- Sidebar menu -->
    <div id="sidebar">
        <ul>
            <p id="welcome-user">
                Welcome, <?php echo htmlspecialchars($employee_name); ?>!
            </p>

            <!-- Concrete Design: Everyone can see -->
            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php'">
                Concrete Design List
            </button>

            <!-- Inventory Management: Manager only -->
            <?php if ($role === 'Manager'): ?>
                <button class="sidebar-button"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php'">
                    Inventory Management
                </button>
            <?php endif; ?>

            <!-- User Accounts: Everyone -->
            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php'">
                User Accounts
            </button>

            <!-- Reports: Manager only -->
            <?php if ($role === 'Manager'): ?>
                <button class="sidebar-button"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/reports_management.php'">
                    Reports
                </button>
            <?php endif; ?>

            <!-- Settings: Everyone -->
            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/settings_management.php'">
                Settings
            </button>


            <!-- Add Manager: Manager only -->
            <?php if ($role === 'Manager'): ?>
                <button class="sidebar-button"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/add_manager_page/add_manager.php'">
                    Add Manager
                </button>
            <?php endif; ?>

            <!-- Manage Employee Accounts: Manager only -->
            <?php if (isset($role) && strtolower(trim($role)) === 'manager'): ?>
                <button class="sidebar-button"
                    onclick="window.location.href='/Inventory-Accounts-System-v1/pages/manage_employee_accounts/mea.php'">
                    Manage Employee Accounts
                </button>
            <?php endif; ?>

            <!-- Logout: Everyone -->
            <button id="logout-button" class="sidebar-button" style="margin-top: 20%"
                onclick="if(confirm('Did you save all changes?')){if(confirm('Are you sure you want to log out?')){window.location.href='/Inventory-Accounts-System-v1/login_page/web_login.php';}};">
                Logout
            </button>
        </ul>
    </div>

    <!-- Button to toggle sidebar open/close -->
    <button id="toggle-btn"
        style="position: fixed; top: 112px; left: -25px; transform: translateX(50%); border: none; background: none; color: darkblue; font-size: 24px; cursor: pointer;">➡️</button>

    <!-- Main content area -->
    <div id="main-content" style="margin-left:0; padding: 20px;">





    </div> <!-- End main content -->

    <!-- Sidebar toggle JavaScript -->
    <script>
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("main-content");
        const toggleBtn = document.getElementById("toggle-btn");
        let isClose = true;

        toggleBtn.addEventListener("click", () => {
            if (isClose) {
                sidebar.style.width = "18%";
                sidebar.style.padding = "10px";
                mainContent.style.marginLeft = "18%";
                toggleBtn.textContent = "⬅️";
            } else {
                sidebar.style.width = "0";
                sidebar.style.padding = "0";
                mainContent.style.marginLeft = "0";
                toggleBtn.textContent = "➡️";
            }
            isClose = !isClose;
        });

        // Real-time clock display
        function realClockTime() {
            const now = new Date();
            const options = { month: 'short', day: '2-digit', year: 'numeric' };
            const dateStr = now.toLocaleDateString('en-US', options).replace(',', '.');
            let hours = now.getHours().toString().padStart(2, '0');
            let minutes = now.getMinutes().toString().padStart(2, '0');
            let seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('clock').textContent = `${dateStr} || ${hours}:${minutes}:${seconds}`;
        }
        setInterval(realClockTime, 1000);
        realClockTime();
    </script>

</body>

<footer style="
    background-color: blue;
    color: yellow;
    text-align: center;
    padding: 5px 0;
    position: fixed;
    bottom: 0;
    width: 100%;
    font-family: Arial, sans-serif;
    font-size: 14px;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.2);
">
    &copy; <?php echo date("Y"); ?> SORCOM Ready-Mix Concrete. All rights reserved.
</footer>

</html>