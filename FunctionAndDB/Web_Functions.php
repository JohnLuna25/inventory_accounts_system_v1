<?php
function showHeader()
{
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get session variables for role-based access (INSIDE the function)
    $user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'regular';
    $employee_name = isset($_SESSION['employee_name']) ? $_SESSION['employee_name'] : 'User';

    echo '
<div class="main-header">
    <div class="header-grid">
        <!-- Left: Burger Menu -->
        <div class="header-left">
            <button class="burger-menu" id="burgerMenuBtn" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <!-- Center: Title -->
        <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php" style="text-decoration: none;">
            <div class="header-center">
                <h1 class="header-title">SORCOM READY-MIX CONCRETE</h1>
                <p class="header-subtitle">Inventory and Accounts Management System</p>
            </div>
        </a> 

        <!-- Right: Clock -->
        <div class="header-right">
            <p id="clock" class="clock-text"></p>
        </div>
    </div>
</div>

<nav class="slide-menu" id="slideMenuNav">
    <ul class="menu-list">
        <!-- Home Page link -->
        <li class="dashboard-title">
            <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php" style="text-decoration: none; color: white; display: block; text-align: center;">
                <i class="fas fa-home"></i> Home Page
            </a>
        </li>
        <li><a href="/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php" 
            class="' . (strpos($_SERVER['REQUEST_URI'], '/concrete_design_page/') !== false ? 'active-page' : '') . '">
            <i class="fas fa-list me-2"></i> Concrete Design List
        </a></li>';

    if ($user_type === 'master') {
        echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php"
            class="' . (strpos($_SERVER['REQUEST_URI'], '/inventory_page/') !== false ? 'active-page' : '') . '">
            <i class="fas fa-box me-2"></i> Inventory
        </a></li>';
    }

    echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php"
            class="' . (strpos($_SERVER['REQUEST_URI'], '/statement_account_page/') !== false ? 'active-page' : '') . '">
            <i class="fas fa-dollar-sign me-2"></i> Transaction
        </a></li>';

    if ($user_type === 'master') {
        echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/reports_page/reports_management.php"
            class="' . (strpos($_SERVER['REQUEST_URI'], '/reports_page/') !== false ? 'active-page' : '') . '">
            <i class="fas fa-chart-line me-2"></i> Reports
        </a></li>';
    }

    echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/user_settings_page/user_settings.php"
            class="' . (strpos($_SERVER['REQUEST_URI'], '/user_settings_page/') !== false ? 'active-page' : '') . '">
            <i class="fas fa-cog me-2"></i> User Settings
        </a></li>
        <li class="logout-item"><a href="/Inventory-Accounts-System-v1/login_page/web_login.php" onclick="return confirmLogout()">
            <i class="fas fa-sign-out-alt me-2"></i> Log out
        </a></li>
    </ul>
</nav>
<div class="menu-overlay" id="menuOverlayDiv"></div>

<link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<style>
/* ALL HEADER TEXT WHITE */
.main-header,
.main-header *,
.header-left a,
.header-title,
.header-subtitle,
.clock-text {
    color: white !important;
}

/* Burger Menu Button - No border, white lines */
#burgerMenuBtn {
    display: inline-flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    position: relative !important;
    top: -5px !important;
    left: -14px !important;
    margin: 0 !important;
    padding: 4px 3px !important;
    width: 28px !important;
    height: 22px !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer !important;
    z-index: 9999 !important;
    pointer-events: auto !important;
    touch-action: manipulation !important;
}

#burgerMenuBtn span {
    display: block !important;
    width: 100% !important;
    height: 2px !important;
    background: white !important;
    border-radius: 2px !important;
    margin: 0 !important;
    transition: all 0.3s ease !important;
}

/* SLIDE MENU - From home.php */
#slideMenuNav {
    position: fixed !important;
    top: 0 !important;
    left: -250px !important;
    width: 230px !important;
    height: 100% !important;
    background: #2e3590 !important;
    z-index: 999 !important;
    transition: left 0.35s ease !important;
    padding: 20px 20px 20px !important;
    box-shadow: 4px 0 25px rgba(0, 0, 0, 0.4) !important;
    overflow-y: auto !important;
    box-sizing: border-box !important;
}

#slideMenuNav.active { 
    left: 0 !important; 
}

/* MENU OVERLAY - From home.php */
#menuOverlayDiv {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.6) !important;
    z-index: 9999 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transition: opacity 0.3s ease, visibility 0.3s ease !important;
    pointer-events: none !important;
}

#menuOverlayDiv.active {
    visibility: visible !important;
}

/* MENU LIST - From home.php */
.menu-list {
    list-style: none !important;
    padding: 0 !important;
    margin-top: 100px !important;
}

.menu-list li { 
    margin-bottom: 8px !important; 
}

.menu-list a {
    text align: left;
    display: block !important;
    color: white !important;
    text-decoration: none !important;
    padding: 14px 20px !important;
    border-radius: 10px !important;
    font-weight: 500 !important;
    transition: all 0.25s ease !important;
    border-left: 3px solid transparent !important;
    border-right: 3px solid transparent !important;
}

.menu-list a:hover {
    background: #f1b33f !important;
    color: #303030 !important;
    transform: translateX(5px) !important;
    border-left-color: #ffffff !important;
    border-right-color: #ffffff !important;
}

.menu-list li:last-child a {
    border-left-color: #ff4444 !important;
}

.menu-list li:last-child a:hover {
    background: rgb(255, 49, 70) !important;
    border-left-color: white !important;
    border-right-color: white !important;
}

/* ACTIVE PAGE INDICATOR */
.menu-list a.active-page {
    background: rgba(0, 180, 0, 0.7) !important; /* green, 50% opacity */
    color: white !important;
    border-left-color: #ffffff !important;
    border-right-color: #ffffff !important;
    text-align: left !important;
}

.menu-list a.active-page:hover {
    background: rgba(40, 245, 40, 0.5) !important; /* slightly brighter on hover */
    color: white !important;
}

/* WELCOME MESSAGE - From home.php */
.welcome-message {
    margin-bottom: 20px !important;
}

.welcome-message p {
    color: white !important;
    font-size: 16px !important;
    font-weight: bold !important;
    text-align: center !important;
    padding: 10px !important;
    background: #1e2460 !important;
    border-radius: 8px !important;
}

/* BASE HEADER STYLES */
body, html { 
    overflow-x: hidden; 
    margin: 0; 
    padding: 0; 
}

.main-content {
    padding-top: 80px !important;
}

.main-header {
    background-color: #2e3590;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    box-sizing: border-box;
}

.header-grid {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}

.header-left, .header-center, .header-right { 
    flex: 0 0 auto; 
}

.header-center { 
    text-align: center; 
}

/* HEADER TITLE FONT */
.header-title {
    margin-top: 5px;
    font-weight: bold;
    font-size: 18px;
    color: white;
    font-family: "Iosevka Charon Mono", monospace;
    letter-spacing: 4px;
    margin-bottom: 5px;
}

/* HEADER SUBTITLE FONT */
.header-subtitle {
    font-size: 16px;
    color: white;
    font-family: "Lexend Giga", sans-serif;
    letter-spacing: 4px;
    margin-top: 0;
}

/* CLOCK FONT */
.clock-text {
    font-family: "JetBrains Mono", "Roboto Mono", "Courier New", monospace;
    font-weight: bold;
    font-size: 0.9rem;
    color: white;
    white-space: nowrap;
    margin: 0;
    text-align: right;  
    margin-left: -50vw;
}

/* Responsive */
@media (max-width: 768px) {
    .header-grid { padding: 0 10px; }
    .header-title { font-size: 13px; letter-spacing: 2px; }
    .header-subtitle { font-size: 10px; }
    .clock-text { font-size: 0.7rem; margin-left: -20vw; }
}

@media (max-width: 576px) {
    .main-header { padding: 10px 0; }
    .header-grid { flex-direction: column; gap: 5px; }
    .header-center { order: 1; }
    .header-left { order: 2; }
    .header-right { order: 3; }
    .header-title { font-size: 12px; letter-spacing: 1px; }
    .header-subtitle { font-size: 8px; }
    .clock-text { font-size: 0.6rem; margin-left: 0; text-align: center; }
}
</style>

<script>
(function(){
    let isMenuOpen = false;
    let handlersAttached = false;
    
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
            document.body.classList.add("menu-open");
        } else {
            burger.classList.remove("active");
            menu.classList.remove("active");
            overlay.classList.remove("active");
            document.body.classList.remove("menu-open");
        }
    }
    
    function attachHandlers() {
        if (handlersAttached) return;
        
        const burger = document.getElementById("burgerMenuBtn");
        const menu = document.getElementById("slideMenuNav");
        const overlay = document.getElementById("menuOverlayDiv");
        
        if (!burger || !menu || !overlay) return;
        
        burger.onclick = toggleMenu;
        overlay.onclick = toggleMenu;
        
        document.querySelectorAll(".menu-list a").forEach(link => {
            link.onclick = function() {
                setTimeout(toggleMenu, 10);
            };
        });
        
        handlersAttached = true;
    }
    
    function init() { 
        const burger = document.getElementById("burgerMenuBtn");
        if (burger) {
            burger.classList.remove("active");
        }
        attachHandlers(); 
    }
    
    document.addEventListener("DOMContentLoaded", init);
    setTimeout(init, 100);
    setTimeout(init, 500);
    setTimeout(init, 1500);
    
    document.addEventListener("keydown", e => {
        if (e.key === "Escape") {
            const burger = document.getElementById("burgerMenuBtn");
            const menu = document.getElementById("slideMenuNav");
            const overlay = document.getElementById("menuOverlayDiv");
            if (burger) burger.classList.remove("active");
            if (menu) menu.classList.remove("active");
            if (overlay) overlay.classList.remove("active");
            document.body.classList.remove("menu-open");
            isMenuOpen = false;
        }
    });
    
    window.confirmLogout = () => {
        return confirm("Did you save all changes?") && confirm("Are you sure?");
    };
    
    // CLOCK
    setInterval(() => {
        const clock = document.getElementById("clock");
        if (clock) {
            const now = new Date();
            clock.textContent = now.toLocaleDateString("en-US", {month:"short",day:"2-digit",year:"numeric"}).replace(",", ".") + 
                               " | " + now.toLocaleTimeString("en-US", {hour12:false});
        }
    }, 1000);
    
})();
</script>
';
}




/*



function showMenuOnly()
{
    // Get session variables for role-based access
    $user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'regular';

    echo '
<!-- MENU ONLY (No Header) -->
<nav class="slide-menu" id="slideMenuNav">
    <ul class="menu-list">
        <li class="dashboard-title">
            <p>Main Menu</p>
        </li>
        <li><a href="/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php">
            <i class="fas fa-list"></i> Concrete Design List
        </a></li>';

    // Inventory Management: Visible ONLY to master users (Plant Managers)
    if ($user_type === 'master') {
        echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php">
            <i class="fas fa-box"></i> Inventory Management
        </a></li>';
    }

    echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php">
            <i class="fas fa-dollar-sign"></i> Transaction Records
        </a></li>';

    // Reports: Visible ONLY to master users (Plant Managers)
    if ($user_type === 'master') {
        echo '
        <li><a href="/Inventory-Accounts-System-v1/pages/reports_page/reports_management.php">
            <i class="fas fa-chart-line"></i> Reports
        </a></li>';
    }

    echo '
        <li class="logout-item"><a href="/Inventory-Accounts-System-v1/login_page/web_login.php" onclick="return confirmLogout()">
            <i class="fas fa-sign-out-alt"></i> Log out
        </a></li>
    </ul>
</nav>
<div class="menu-overlay" id="menuOverlayDiv"></div>

<!-- Burger Button (Floating) -->
<button class="burger-menu" id="burgerMenuBtn" aria-label="Toggle Menu" style="position: fixed; top: 15px; left: 15px; z-index: 10001;">
    <span></span>
    <span></span>
    <span></span>
</button>

<style>

#burgerMenuBtn {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    width: 30px !important;
    height: 24px !important;
    background: transparent !important;
    border: 2px solid yellow !important;
    border-radius: 6px !important;
    padding: 5px !important;
    cursor: pointer !important;
    z-index: 10001 !important;
    background-color: #2e3590 !important;
}

#burgerMenuBtn span {
    display: block !important;
    width: 100% !important;
    height: 2px !important;
    background: #ffff00 !important;
    border-radius: 2px !important;
    transition: all 0.3s ease !important;
}

#burgerMenuBtn.active span:first-child {
    transform: translateY(6px) rotate(45deg) !important;
}

#burgerMenuBtn.active span:nth-child(2) {
    opacity: 0 !important;
    transform: scale(0) !important;
}

#burgerMenuBtn.active span:last-child {
    transform: translateY(-6px) rotate(-45deg) !important;
}


#slideMenuNav {
    position: fixed !important;
    top: 0 !important;
    left: -300px !important;
    width: 300px !important;
    height: 100% !important;
    background: linear-gradient(180deg, #0d6efd 0%, #1e40af 100%) !important;
    z-index: 10000 !important;
    transition: left 0.35s ease !important;
    padding: 80px 20px 20px !important;
    box-shadow: 4px 0 25px rgba(0,0,0,0.4) !important;
}

#slideMenuNav.active {
    left: 0 !important;
}

#closeMenuBtn {
    position: absolute !important;
    top: 15px !important;
    right: 15px !important;
    background: rgba(255,255,255,0.1) !important;
    border: 2px solid rgba(255,255,255,0.3) !important;
    color: white !important;
    font-size: 24px !important;
    cursor: pointer !important;
    width: 42px !important;
    height: 42px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 10001 !important;
}

#closeMenuBtn:hover {
    background: rgba(255,255,0,0.2) !important;
    transform: rotate(90deg) !important;
}

#menuOverlayDiv {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0,0,0,0.6) !important;
    z-index: 9999 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transition: opacity 0.3s ease, visibility 0.3s ease !important;
}

#menuOverlayDiv.active {
    opacity: 1 !important;
    visibility: visible !important;
}

.menu-list {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.menu-list li {
    margin-bottom: 8px !important;
}

.menu-list a {
    display: block !important;
    color: white !important;
    text-decoration: none !important;
    padding: 14px 20px !important;
    border-radius: 10px !important;
    font-weight: 500 !important;
    transition: all 0.25s ease !important;
    border-left: 3px solid transparent !important;
}

.menu-list a:hover {
    background: rgba(255,255,0,0.15) !important;
    color: #ffff00 !important;
    transform: translateX(5px) !important;
    border-left-color: #ffff00 !important;
}

.menu-list li:last-child a {
    border-left-color: #ff4444 !important;
}

.menu-list li:last-child a:hover {
    background: rgba(220, 53, 69, 0.8) !important;
    border-left-color: white !important;
}

.dashboard-title p {
    color: yellow;
    font-size: 22px;
    margin-top: -53px;
    margin-bottom: 20px;
    text-align: center;
    display: block;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
</style>

<script>
(function(){
    let isMenuOpen = false;
    let handlersAttached = false;

    function toggleMenu() {
        const burger = document.getElementById("burgerMenuBtn");
        const menu = document.getElementById("slideMenuNav");
        const overlay = document.getElementById("menuOverlayDiv");
        const closeBtn = document.getElementById("closeMenuBtn");

        if (!burger || !menu || !overlay || !closeBtn) return;

        isMenuOpen = !isMenuOpen;

        if (isMenuOpen) {
            burger.classList.add("active");
            menu.classList.add("active");
            overlay.classList.add("active");
            closeBtn.classList.add("active");
            document.body.classList.add("menu-open");
        } else {
            burger.classList.remove("active");
            menu.classList.remove("active");
            overlay.classList.remove("active");
            closeBtn.classList.remove("active");
            document.body.classList.remove("menu-open");
        }
    }

    function attachHandlers() {
        if (handlersAttached) return;

        const burger = document.getElementById("burgerMenuBtn");
        const menu = document.getElementById("slideMenuNav");
        const overlay = document.getElementById("menuOverlayDiv");
        const closeBtn = document.getElementById("closeMenuBtn");

        if (!burger || !menu || !overlay || !closeBtn) return;

        burger.onclick = toggleMenu;
        closeBtn.onclick = toggleMenu;
        overlay.onclick = toggleMenu;

        document.querySelectorAll(".menu-list a").forEach(link => {
            link.onclick = function(e) {
                setTimeout(toggleMenu, 10);
            };
        });

        handlersAttached = true;
    }

    function init() { 
        const burger = document.getElementById("burgerMenuBtn");
        if (burger) {
            burger.classList.remove("active");
        }
        attachHandlers(); 
    }

    document.addEventListener("DOMContentLoaded", init);
    setTimeout(init, 100);
    setTimeout(init, 500);

    document.addEventListener("keydown", e => {
        if (e.key === "Escape") {
            const burger = document.getElementById("burgerMenuBtn");
            const menu = document.getElementById("slideMenuNav");
            const overlay = document.getElementById("menuOverlayDiv");
            const closeBtn = document.getElementById("closeMenuBtn");
            if (burger) burger.classList.remove("active");
            if (menu) menu.classList.remove("active");
            if (overlay) overlay.classList.remove("active");
            if (closeBtn) closeBtn.classList.remove("active");
            document.body.classList.remove("menu-open");
            isMenuOpen = false;
        }
    });

    window.confirmLogout = () => {
        return confirm("Did you save all changes?") && confirm("Are you sure?");
    };
})();
</script>
';
}



*/






/**
 * Fetch inventory rows from the database
 */
function fetchInventoryRows()
{
    require 'db_connect.php';

    $sql = "SELECT * FROM sorcom_readymix_inventory
            WHERE date_added IS NOT NULL 
               OR design_mix_id IS NOT NULL 
               OR description <> '' 
               OR batch <> '' 
               OR stocks_in IS NOT NULL 
               OR stocks_out IS NOT NULL 
               OR remarks <> ''
            ORDER BY date_added DESC, id ASC";

    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $conn->close();
    return $rows;
}



?>


<script>
    // Only run if #demo element exists
    if (document.getElementById("demo")) {
        var iSorcom = 0;
        var iIAMS = 0;

        var txtSorcom = 'SORCOM READY-MIX CONCRETE';
        var txtIAMS = 'Inventory and Accounts Management System';

        var speedTitle = 80;
        var speedSubtitle = 80;

        function sorcomWriter() {
            if (iSorcom < txtSorcom.length) {
                document.getElementById("demo").innerHTML += txtSorcom.charAt(iSorcom);
                iSorcom++;
                setTimeout(sorcomWriter, speedTitle);
            } else {
                document.getElementById("demo").innerHTML += "<br>";
                setTimeout(iamsWriter, 500);
            }
        }

        function iamsWriter() {
            if (iIAMS < txtIAMS.length) {
                document.getElementById("demo").innerHTML += txtIAMS.charAt(iIAMS);
                iIAMS++;
                setTimeout(iamsWriter, speedSubtitle);
            }
        }

        sorcomWriter();
    }
</script>