<?php
session_start();
require_once '../FunctionAndDB/Web_Functions.php';
require_once 'announcement_list.php'; // handles add/delete logic

// Initialize announcements
if (!isset($_SESSION['announcements'])) {
    $_SESSION['announcements'] = [];
}

// Handle posting announcement from main input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add from main input form
    if (!empty($_POST['announcement'])) {
        array_unshift($_SESSION['announcements'], htmlspecialchars($_POST['announcement']));
        header("Location: home.php");
        exit();
    }

    // Cancel buttons
    if (isset($_POST['cancel_add']) || isset($_POST['cancel_delete'])) {
        header("Location: home.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory and Accounts Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Sidebar */
        #sidebar {
            background-color: lightblue;
            padding: 10px;
            top: 142px;
            width: 0;
            min-height: 100vh;
            position: fixed;
            overflow: hidden;
            transition: width 0.2s;
        }
        #sidebar ul {
            display: flex;
            flex-direction: column;
            margin-top: 200px;
        }
        .sidebar-button {
            margin: 5px 0;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
        }

        /* Main content */
        #main-content {
            transition: margin-left 0.2s;
        }

        /* Announcement styles */
        #announcement-bar ul {
            list-style-type: disc; /* bold bullets */
            padding-left: 30px;
            margin-bottom: 30px; /* space below list */
        }
        #announcement-bar li {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: justify;
        }
    </style>
</head>
<body style="margin:0; font-family: Arial, sans-serif;">

    <!-- Header -->
    <div style="background-color: blue; padding: 7px; width: 100%; position: fixed; text-align: center;">
        <h1 style="font-size: 30px; font-weight: bold; color: yellow;">SORCOM READY-MIX CONCRETE</h1>
        <p style="font-size: 20px; font-weight: 600; color: yellow;">Inventory and Accounts Management System.</p>
        <p style="font-size: 18px; color: yellow; text-align: right; margin-right: 30px; margin-bottom: -10px;">
            <?php echo date("M d Y"); ?>
        </p>
    </div>

    <!-- Sidebar -->
    <div id="sidebar">
        <ul>
            <p style="font-size: 20px; font-weight: bold; color: darkblue; text-align: center; margin-top: -180px; margin-bottom: 50px;">Welcome, User!</p>
            <button class="sidebar-button" onclick="window.location.href='concrete_design_list.php'">Concrete Design List</button>
            <button class="sidebar-button" onclick="window.location.href='inventory_management.php'">Inventory Management</button>
            <button class="sidebar-button" onclick="window.location.href='accounts_management.php'">Accounts Management</button>
            <button class="sidebar-button" onclick="window.location.href='reports_management.php'">Reports</button>
            <button class="sidebar-button" onclick="window.location.href='settings_management.php'">Settings</button>
            <button class="sidebar-button" style="margin-top:90px; color:black; background-color:#FF3F1A;"
                onclick="if(confirm('Did you save all changes?')){if(confirm('Are you sure you want to log out?')){window.location.href='sorcomweb_login.php';}}">
                Logout
            </button>
        </ul>
    </div>

    <!-- Toggle Button -->
    <button id="toggle-btn" style="position: fixed; top: 145px; left: -25px; transform: translateX(50%); border: none; background: none; color: darkblue; font-size: 24px; cursor: pointer;">➡️</button>

    <div id="main-content" style="margin-left:0; padding: 20px;">

        <!-- Announcement Bar -->
        <div id="announcement-bar" style="width: 90%; margin: 50px auto; background-color: #fff; padding: 20px; border-radius: 15px; text-align: left; margin-top: 200px;">

            <h3 style="text-align:center; margin-bottom: 20px;">Announcements</h3>

            <!-- Announcement List -->
            <ul>
                <?php foreach($_SESSION['announcements'] as $index => $ann): ?>
                    <li><?php echo $ann; ?></li>
                <?php endforeach; ?>
            </ul>

            <!-- Add/Delete Buttons -->
            <div style="margin-top: 20px; text-align: center;">
                <form method="post" style="display:inline-block; margin-right: 10px;">
                    <button type="submit" name="show_add_form" style="padding:10px 20px; border-radius:5px;">Add</button>
                </form>
                <form method="post" style="display:inline-block; margin-left: 10px;">
                    <button type="submit" name="show_delete_form" style="padding:10px 20px; border-radius:5px;">Delete</button>
                </form>
            </div>
        </div>

        <!-- Add Announcement Modal -->
        <?php if(isset($_POST['show_add_form'])): ?>
            <div style="position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5);">
                <div style="width:50%; margin:100px auto; background-color:#fff; padding:20px; border-radius:15px;">
                    <h3 style="margin-bottom: 20px;">Add Announcement</h3>
                    <form method="post">
                        <textarea name="announcement" placeholder="Type an announcement here..." style=" width:90%; height:100px; padding:10px;"></textarea>
                        <br><br>
                        <button type="submit" name="add_announcement" style="padding:10px 20px; margin-right: 10px;">Add Announcement</button>
                        <button type="submit" name="cancel_add" style="padding:10px 20px;">Cancel</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Delete Announcement Modal -->
        <?php if(isset($_POST['show_delete_form'])): ?>
            <div style="position:fixed; top:0; left:0; width:100%; height:100%; background-color: rgba(0,0,0,0.5); overflow-x:auto;">
                <div style="width:60%; margin:50px auto; background-color:#fff; padding:20px; border-radius:15px;">
                    <h3>Delete Announcements</h3>
                    <?php foreach($_SESSION['announcements'] as $index => $ann): ?>
                        <form method="post" style="display:flex; justify-content: space-between; margin-bottom:10px; border-bottom:1px solid #ccc; padding:5px;">
                            <span style="flex:1; text-align:justify;"><?php echo $ann; ?></span>
                            <button type="submit" name="delete_announcement" value="<?php echo $index; ?>" style="margin-left:10px;">Delete</button>
                        </form>
                    <?php endforeach; ?>
                    <form method="post"><button type="submit" name="cancel_delete" style="margin-top:10px;">Cancel</button></form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar Toggle JS -->
    <script>
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.getElementById("main-content");
        const toggleBtn = document.getElementById("toggle-btn");
        let isClose = true;

        toggleBtn.addEventListener("click", () => {
            if(isClose){
                sidebar.style.width = "18%";
                mainContent.style.marginLeft = "18%";
                toggleBtn.textContent = "⬅️";
            } else {
                sidebar.style.width = "0";
                mainContent.style.marginLeft = "0";
                toggleBtn.textContent = "➡️";
            }
            isClose = !isClose;
        });
    </script>

</body>
</html>
