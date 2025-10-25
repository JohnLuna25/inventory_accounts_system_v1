<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory and Accounts Management System</title>
    <link rel="stylesheet" href="style.css">



</head>
    <body style="margin: 0;">

        
        <div style="background-color: blue; padding: 7px; width: 100%; margin: 0; text-align: center; margin-top: 0; position: fixed;">

            <h1 style="font-family: Arial, sans-serif; font-size: 30px; font-weight: bold; color: yellow; text-align: center;">
                SORCOM READY-MIX CONCRETE
            </h1>

            <p style="font-family: Arial, sans-serif; font-size: 20px; font-weight: semibold; color: yellow; text-align: center;">
                Inventory and Accounts Management System.
            </p>

            <p style="font-family: Arial, sans-serif; font-size: 18px; color: yellow; text-align: right; margin-right: 30px; margin-bottom: -10px;">
                <?php echo date("M d Y"); ?>
            </p>
            

        </div>

<!-- Sidebar -->
        <div id="sidebar" style="background-color: lightblue; padding: 10px; top: 142px; width: 0%; min-height: 100vh; position: fixed; overflow: hidden; transition: width 0.2s;">
                <ul style="margin: 0; display: flex; flex-direction: column; margin-top: 200px">

                    <p style="font-family: Arial, sans-serif; font-size: 20px; font-weight: bold; color: darkblue; text-align:  center; margin-top: -180px; margin-bottom: 50px;">Welcome, User!</p>

                    <button class="sidebar-button" onclick="window.location.href='concrete_design_list.php'">
                        Concrete Design List
                    </button>
                    <br>
                    <button class="sidebar-button" onclick="window.location.href='inventory_management.php'">
                        Inventory Management</button>
                    <br>
                    <button class="sidebar-button" onclick="window.location.href='accounts_management.php'">
                        Accounts Management</button>
                    <br>
                    <button class="sidebar-button" onclick="window.location.href='reports_management.php'">
                        Reports</button>
                    <br>
                    <button class="sidebar-button" onclick="window.location.href='settings_management.php'">
                        Settings</button>

                    <button class="sidebar-button" 
                            style="margin-top: 90px; color: black; background-color: #FF3F1A;" 
                            onclick="
                                if (confirm('Did you save all the changes you made?')) {
                                    if (confirm('Are you sure you want to log out?')) {
                                        window.location.href='sorcomweb_login.php';
                                    }
                                }
                            ">
                        Logout
                    </button>

                    <br>
                </ul>
        </div>

        <!-- Toggle Button -->
        <button id="toggle-btn" style="position: fixed; top: 145px; left: -25px; transform: translateX(50%); border: none; background: none; color: darkblue; font-size: 24px; cursor: pointer;">➡️</button>

        <!-- Main Content -->
        <div style="flex; margin-left: 18%; padding: 20px; transition: margin-left 0s;" id="main-content"></div>

        <script>
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.getElementById("main-content");
            const toggleBtn = document.getElementById("toggle-btn");
            let isClose = true;

            toggleBtn.addEventListener("click", () => {
                if (isClose) {
                 sidebar.style.width = "18%";
                mainContent.style.marginLeft = "18%";
                toggleBtn.textContent = "⬅️";
                toggleBtn.style.left = "position: fixed;";
                } else {
                    sidebar.style.width = "0";
                    mainContent.style.marginLeft = "0";
                    toggleBtn.textContent = "➡️";
                    toggleBtn.style.left = "position: fixed;";
                }
                isClose = !isClose;
            });
        </script>

         




        

    </body>
</html>