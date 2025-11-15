<?php
// Start a session to store user data and announcements
session_start();

// Include external PHP files with helper functions and the announcement list
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';          // Web functions
require_once __DIR__ . '/announcement_list.php';                           // Announcement list in the same folder as home.php

// Initialize announcements array in session if it does not exist
if (!isset($_SESSION['announcements'])) {
    $_SESSION['announcements'] = [];
}

// Get the logged-in employee's name from session; default to 'User' if not set
$employee_name = isset($_SESSION['employee_name']) ? htmlspecialchars($_SESSION['employee_name']) : 'User';

// Handle form submissions
if (!empty($_POST['announcement'])) {
    array_unshift($_SESSION['announcements'], htmlspecialchars($_POST['announcement']));
    // Redirect to refresh the announcement list
    header("Location: /Inventory-Accounts-System-v1/pages/home_page/home.php");
    exit();
}

// Cancel add/delete actions
if (isset($_POST['cancel_add']) || isset($_POST['cancel_delete'])) {
    header("Location: /Inventory-Accounts-System-v1/pages/home_page/home.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory and Accounts Management System</title>

    <!-- External stylesheet -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        /* Sidebar container styling */
        #sidebar {
            background-color: lightblue;
            padding: 0;
            top: 131px;
            width: 0;
            /* Sidebar initially hidden */
            min-height: 100vh;
            position: fixed;
            overflow: hidden;
            transition: width 0.2s, padding 0.2s;
        }

        /* Sidebar list styling */
        #sidebar ul {
            display: flex;
            flex-direction: column;
            margin-top: 200px;
        }

        /* Buttons inside sidebar */
        .sidebar-button {
            margin: 5px 0;
            background-color: cornflowerblue;
            border-radius: 40px;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        /* Hover effect for sidebar buttons */
        .sidebar-button:hover {
            background-color: cadetblue;
            color: white;
        }

        /* Main content area transitions when sidebar opens/closes */
        #main-content {
            transition: margin-left 0.2s;
        }

        /* Announcement list styling */
        #announcement-bar ul {
            list-style-type: disc;
            padding-left: 30px;
            margin-bottom: 30px;
        }

        #announcement-bar li {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: justify;
        }

        /* Logout button appearance */
        #logout-button {
            margin-top: 75px;
            margin-bottom: 20px;
            color: black;
            background-color: #dd2500ff;
            border-radius: 20px;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }

        #logout-button:hover {
            background-color: #FF3F1A;
            color: white;
        }

        /* Welcome message styling in sidebar */
        #welcome-user {
            font-size: 17px;
            font-weight: bold;
            color: darkblue;
            text-align: center;
            margin-top: -180px;
            margin-bottom: 30px;
            border-radius: 20px;
            padding: 10px;
            background-color: cornflowerblue;
        }
    </style>
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
            <!-- Display the logged-in user name -->
            <p id="welcome-user">
                Welcome, <?php echo $employee_name; ?>!
            </p>

            <!-- Navigation buttons -->
            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php'">
                Concrete Design List
            </button>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php'">
                Inventory Management
            </button>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php'">
                Accounts Management
            </button>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/reports_management.php'">
                Reports
            </button>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/settings_management.php'">
                Settings
            </button>

            <button class="sidebar-button" onclick="window.location.href='/Inventory-Accounts-System-v1/index.html'">
                Test Area
            </button>

            <!-- Logout button with confirmation prompts -->
            <button id="logout-button" class="sidebar-button" style="margin-top: 55px"
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

        <!-- Announcement panel -->
        <div id="announcement-bar"
            style="width: 40%; margin: 50px auto; background-color: #fff; padding: 20px; border-radius: 15px; text-align: left; margin-top: 200px;">
            <h3 style="text-align:center; margin-bottom: 20px;">Announcements</h3>

            <!-- Display the list of announcements -->
            <ul>
                <?php foreach ($_SESSION['announcements'] as $index => $ann): ?>
                    <li><?php echo $ann; ?></li>
                <?php endforeach; ?>
            </ul>

            <!-- Buttons to show add or delete announcement modals -->
            <div style="margin-top: 20px; text-align: center;">
                <form method="post" style="display:inline-block; margin-right: 10px;">
                    <button type="submit" name="show_add_form"
                        style="padding:10px 20px; border-radius:5px;">Add</button>
                </form>
                <form method="post" style="display:inline-block; margin-left: 10px;">
                    <button type="submit" name="show_delete_form"
                        style="padding:10px 20px; border-radius:5px;">Delete</button>
                </form>
            </div>
        </div>

        <!-- Modal for adding an announcement -->
        <?php if (isset($_POST['show_add_form'])): ?>
            <div style="position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5);">
                <div style="width:50%; margin:100px auto; background-color:#fff; padding:20px; border-radius:15px;">
                    <h3 style="margin-bottom: 20px;">Add Announcement</h3>
                    <form method="post">
                        <textarea name="announcement" placeholder="Type an announcement here..."
                            style=" width:90%; height:100px; padding:10px;"></textarea>
                        <br><br>
                        <button type="submit" name="add_announcement" style="padding:10px 20px; margin-right: 10px;">Add
                            Announcement</button>
                        <button type="submit" name="cancel_add" style="padding:10px 20px;">Cancel</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Modal for deleting announcements -->
        <?php if (isset($_POST['show_delete_form'])): ?>
            <div
                style="position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); overflow-x:auto;">
                <div style="width:60%; margin:50px auto; background-color:#fff; padding:20px; border-radius:15px;">
                    <h3>Delete Announcements</h3>
                    <?php foreach ($_SESSION['announcements'] as $index => $ann): ?>
                        <form method="post"
                            style="display:flex; justify-content: space-between; margin-bottom:10px; border-bottom:1px solid #ccc; padding:5px;">
                            <span style="flex:1; text-align:justify;"><?php echo $ann; ?></span>
                            <button type="submit" name="delete_announcement" value="<?php echo $index; ?>"
                                style="margin-left:10px;">Delete</button>
                        </form>
                    <?php endforeach; ?>
                    <form method="post"><button type="submit" name="cancel_delete" style="margin-top:10px;">Cancel</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

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