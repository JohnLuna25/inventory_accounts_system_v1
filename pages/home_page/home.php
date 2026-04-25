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
    <link rel="stylesheet" href="home.css">
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">



</head>

<body>

    <!-- Header -->
    <header class="main-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Burger Menu Button -->
                <div class="col-1 col-md-1 text-center">
                    <button class="burger-menu" id="burgerMenuBtn" aria-label="Toggle Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>

                <!-- Logo -->
                <div class="col-2 col-md-1 text-center">
                    <img src="/Inventory-Accounts-System-v1/assets/images/SRMCP.png" alt="SORCOM Logo"
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

    <!-- Slide Menu (hidden until button clicked) -->
    <nav class="slide-menu" id="slideMenuNav">

        <ul class="menu-list">
            <!-- Welcome Message inside menu -->
            <li class="welcome-message">
                <p>Welcome,
                    <?php echo $employee_name; ?>!
                </p>
            </li>
            <li><a href="/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php">
                    <i class="fas fa-list me-2"></i> Concrete Design List
                </a></li>
            <li><a href="/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php">
                    <i class="fas fa-box me-2"></i> Inventory
                </a></li>
            <li><a href="/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php">
                    <i class="fas fa-dollar-sign me-2"></i> Transaction
                </a></li>
            <li><a href="/Inventory-Accounts-System-v1/pages/reports_page/reports_management.php">
                    <i class="fas fa-chart-line me-2"></i> Reports
                </a></li>
            <li class="logout-item"><a href="/Inventory-Accounts-System-v1/login_page/web_login.php"
                    onclick="return confirmLogout()">
                    <i class="fas fa-sign-out-alt me-2"></i> Log out
                </a></li>
        </ul>

    </nav>
    <div class="menu-overlay" id="menuOverlayDiv"></div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">

            <!-- Widget -->
            <?php include 'listOrder_widget.php'; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        &copy; <?php echo date("Y"); ?> SORCOM Ready-Mix Concrete. All rights reserved.
    </footer>

    <!-- Scripts -->
    <script>

        function realClockTime() {
            const now = new Date();
            const options = { month: 'long', day: '2-digit', year: 'numeric' };
            const dateStr = now.toLocaleDateString('en-US', options).replace(',', ',');
            let hours = now.getHours();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('clock').textContent = `${dateStr} || ${hours}:${minutes}:${seconds} ${ampm}`;
        }
        realClockTime();
        setInterval(realClockTime, 1000);

    </script>

    <script>
        (function () {
            let isMenuOpen = false;

            function toggleMenu() {
                const burger = document.getElementById("burgerMenuBtn");
                const menu = document.getElementById("slideMenuNav");
                const overlay = document.getElementById("menuOverlayDiv");

                if (!burger || !menu || !overlay) return;

                isMenuOpen = !isMenuOpen;

                if (isMenuOpen) {
                    burger.classList.add("active");
                    menu.classList.add("active");
                    overlay.classList.add("active");
                    document.body.classList.add("menu-open");  // ← This line
                } else {
                    burger.classList.remove("active");
                    menu.classList.remove("active");
                    overlay.classList.remove("active");
                    document.body.classList.remove("menu-open");  // ← This line
                }
            }

            const burger = document.getElementById("burgerMenuBtn");
            const closeBtn = document.getElementById("closeMenuBtn");
            const overlay = document.getElementById("menuOverlayDiv");

            if (burger) burger.onclick = toggleMenu;
            if (overlay) overlay.onclick = toggleMenu;

            document.querySelectorAll(".menu-list a").forEach(link => {
                link.onclick = function () {
                    setTimeout(toggleMenu, 10);
                };
            });

            window.confirmLogout = () => {
                return confirm("Did you save all changes?") && confirm("Are you sure?");
            };
        })();
    </script>

    <script>
        function setMenuPosition() {
            const header = document.querySelector('.main-header');
            const menu = document.querySelector('.slide-menu');
            if (header && menu) {
                const headerHeight = header.offsetHeight;
                menu.style.top = headerHeight + 'px';
                // DO NOT set height — let it be auto based on content
            }
        }

        window.addEventListener('load', setMenuPosition);
        window.addEventListener('resize', setMenuPosition);
    </script>

    <!-- Bootstrap JS -->
    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>

</html>