<?php
// ==========================
// PHP Setup & Includes
// ==========================
session_start();
require_once '../FunctionAndDB/Web_Functions.php';
require_once('announcement_list.php');

// ==========================
// Initialize Announcements
// ==========================
if (!isset($_SESSION['announcements'])) {
    $_SESSION['announcements'] = [];
}

//=====================================================================
// Get employee name from session (default to "User" if not logged in)
//=====================================================================
$employee_name = isset($_SESSION['employee_name']) ? htmlspecialchars($_SESSION['employee_name']) : 'User';

// ==========================
// Handle Form Submissions
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Add Announcement from main input ----
    if (!empty($_POST['announcement'])) {
        array_unshift($_SESSION['announcements'], htmlspecialchars($_POST['announcement']));
        header("Location: home.php");
        exit();
    }

    // ---- Cancel Buttons ----
    if (isset($_POST['cancel_add']) || isset($_POST['cancel_delete'])) {
        header("Location: home.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- ==========================
         Head Section
    ========================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory and Accounts Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">



    <style>
        /* ==========================
           Sidebar Styles
        ========================== */
        #sidebar {
            background-color: lightblue;
            padding: 0;
            /* remove padding when collapsed */
            top: 131px;
            width: 0;
            min-height: 100vh;
            position: fixed;
            overflow: hidden;
            /* hide anything outside */
            transition: width 0.2s, padding 0.2s;
        }

        #sidebar ul {
            display: flex;
            flex-direction: column;
            margin-top: 200px;
        }

        .sidebar-button {
            margin: 5px 0;
            background-color: cornflowerblue;
            border-radius: 40px;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        .sidebar-button:hover {
            background-color: cadetblue;
            color: white;
        }

        /* ==========================
           Main Content Styles
        ========================== */
        #main-content {
            transition: margin-left 0.2s;
        }

        /* ==========================
           Announcement Styles
        ========================== */
        #announcement-bar ul {
            list-style-type: disc;
            /* bold bullets */
            padding-left: 30px;
            margin-bottom: 30px;
            /* space below list */
        }

        #announcement-bar li {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: justify;
        }

        #logout-button {
            margin-top: 75px;
            margin-bottom: 20px;
            /* if needed */
            color: black;
            background-color: #dd2500ff;
            border-radius: 20px;
            border: none;
            /* optional */
            padding: 10px 20px;
            /* optional */
            cursor: pointer;
        }

        #logout-button:hover {
            background-color: #FF3F1A;
            color: white;
        }

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
             background-image: url('../assets/images/SBP.png');
             background-size:cover; 
             background-position:center; 
             background-repeat:no-repeat; 
             height:100vh;">

    <!-- ==========================
         Header Section
    ========================== -->
    <div style="background-color: blue; padding: 7px; width: 100%; position: fixed; text-align: center;">
        <h1 style="font-size: 30px; font-weight: bold; color: yellow;">SORCOM READY-MIX CONCRETE</h1>
        <p style="font-size: 20px; font-weight: 600; color: yellow;">Inventory and Accounts Management System.</p>

        <!-- Logo -->
        <img src="/Inventory-Accounts-System-v1/assets/images/SRMCP.jpg" alt="SORCOM Logo"
            style="height:80px; width:auto; position:absolute; top:25px; left:45px;">

        <!-- Clock -->
        <p id="clock"
            style="font-size: 18px; font-weight: 400; color: yellow; position: absolute; top: 60px; right: 30px; margin: 0;">
            <!-- JS will fill this -->
        </p>
    </div>



    <!-- ==========================
         Sidebar Section
    ========================== -->
    <div id="sidebar">
        <ul>
            <p id="welcome-user">
                Welcome, <?php echo $employee_name; ?>!
            </p>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/concrete_design_page/concrete_design_list.php'">
                Concrete Design List
            </button>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_management.php'">
                Inventory Management
            </button>

            <button class="sidebar-button"
                onclick="window.location.href='/Inventory-Accounts-System-v1/statement_account_page/accounts_management.php'">
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

            <button id="logout-button" class="sidebar-button" style="margin-top: 55px"
                onclick="if(confirm('Did you save all changes?')){if(confirm('Are you sure you want to log out?')){window.location.href='/Inventory-Accounts-System-v1/login_page/web_login.php';}};">
                Logout
            </button>
        </ul>
    </div>

    <!-- ==========================
         Sidebar Toggle Button
    ========================== -->
    <button id="toggle-btn"
        style="position: fixed; top: 112px; left: -25px; transform: translateX(50%); border: none; background: none; color: darkblue; font-size: 24px; cursor: pointer;">➡️</button>

    <!-- ==========================
         Main Content Section
    ========================== -->
    <div id="main-content" style="margin-left:0; padding: 20px;">

        <!-- ==========================
             Announcement Bar
        ========================== -->
        <div id="announcement-bar"
            style="width: 40%; margin: 50px auto; background-color: #fff; padding: 20px; border-radius: 15px; text-align: left; margin-top: 200px;">
            <h3 style="text-align:center; margin-bottom: 20px;">Announcements</h3>

            <!-- Announcement List -->
            <ul>
                <?php foreach ($_SESSION['announcements'] as $index => $ann): ?>
                    <li><?php echo $ann; ?></li>
                <?php endforeach; ?>
            </ul>

            <!-- Add/Delete Buttons -->
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

        <!-- ==========================
             Add Announcement Modal
        ========================== -->
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

        <!-- ==========================
             Delete Announcement Modal
        ========================== -->
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

    </div> <!-- End of Main Content -->

    <!-- ==========================
         Sidebar Toggle JavaScript
    ========================== -->
    <script>
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("main-content");
        const toggleBtn = document.getElementById("toggle-btn");
        let isClose = true;

        toggleBtn.addEventListener("click", () => {
            if (isClose) {
                // Open sidebar
                sidebar.style.width = "18%";
                sidebar.style.padding = "10px"; // restore padding when open
                mainContent.style.marginLeft = "18%";
                toggleBtn.textContent = "⬅️"; // icon changes to left arrow when open
            } else {
                // Close sidebar
                sidebar.style.width = "0";
                sidebar.style.padding = "0"; // remove padding when collapsed
                mainContent.style.marginLeft = "0";
                toggleBtn.textContent = "➡️"; // icon changes to right arrow when closed
            }
            isClose = !isClose;
        });


        function realClockTime() {
            const now = new Date();

            // Format date as "MMM. dd, YYYY HH:mm:ss"
            const options = { month: 'short', day: '2-digit', year: 'numeric' };
            const dateStr = now.toLocaleDateString('en-US', options).replace(',', '.');

            let hours = now.getHours().toString().padStart(2, '0');
            let minutes = now.getMinutes().toString().padStart(2, '0');
            let seconds = now.getSeconds().toString().padStart(2, '0');

            document.getElementById('clock').textContent = `${dateStr} || ${hours}:${minutes}:${seconds}`;
        }

        // Update every second
        setInterval(realClockTime, 1000);
        realClockTime(); // initial call


    </script>

</body>
<!-- ==========================
     Footer
========================== -->
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