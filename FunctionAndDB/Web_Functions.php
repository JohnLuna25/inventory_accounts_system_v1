<?php
function showHeader()
{
    echo '
<div class="main-header">
    <div class="header-grid">
        <!-- Left: Burger Menu + Home Button -->
        <div class="header-left">
            <!-- 🔥 BULLETPROOF BURGER -->
            <button class="burger-menu" id="burgerMenuBtn" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php"
            style="color: yellow;font-size: 12px;font-weight: bold;text-decoration: none;background: transparent;border: 2px solid yellow;padding: 4px 10px;border-radius: 20px;display: inline-block;margin-left: 12px;">
                Go to Home
            </a>
        </div>

        <!-- Center: Title -->
        <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php">
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

<!-- 🔥 MENU -->
<nav class="slide-menu" id="slideMenuNav">
    <button class="close-menu" id="closeMenuBtn">&times;</button>
    <ul class="menu-list">
        <li class="dashboard-title">
            <p>
                Main Menu
            </p>
        </li>
        <li><a href="/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php">Concrete Design List</a></li>
        <li><a href="/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php">Inventory Management</a></li>
        <li><a href="/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsManagement.php">Transaction Records</a></li>
        <li><a href="/Inventory-Accounts-System-v1/pages/reports_page/reports_management.php">Reports</a></li>
        <li class="logout-item"><a href="/Inventory-Accounts-System-v1/login_page/web_login.php" onclick="return confirmLogout()">Log out</a></li>
    </ul>
</nav>
<div class="menu-overlay" id="menuOverlayDiv"></div>

<style>
/* 🔥 Z-INDEX WAR - BULLETPROOF */

li.dashboard-title > p {
    color: yellow;
    font-size: 22px;
    margin-top: -53px;
    margin-bottom: 20px;
          
    /* ✅ FIXED: Center text INSIDE p */
    text-align: center;
    display: block;  /* p is block by default */
    
    /* ✅ OPTIONAL: Perfect width control */
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

#burgerMenuBtn {
    display: inline-flex !important;
    position: relative !important;
    top: -5px !important;
    left: -14px !important;
    margin: 0 !important;
    padding: 4px 3px !important;
    width: 28px !important;
    height: 22px !important;
    background: none !important;
    border: 2px solid yellow !important;
    border-radius: 4px !important;
    cursor: pointer !important;
    flex-direction: column !important;
    justify-content: space-around !important;
    z-index: 9999 !important;
    pointer-events: auto !important;
    touch-action: manipulation !important;
}
#burgerMenuBtn:hover { background: rgba(255,255,0,0.1) !important; }
#burgerMenuBtn span {
    height: 2px !important;
    width: 24px !important;
    background: #ffff00 !important;
    border-radius: 1px !important;
    margin: 0 !important;
    transition: all 0.3s ease !important;
    display: block !important;
}
#burgerMenuBtn.active span:nth-child(1) { transform: rotate(45deg) translate(6px, 6px) !important; }
#burgerMenuBtn.active span:nth-child(2) { opacity: 0 !important; }
#burgerMenuBtn.active span:nth-child(3) { transform: rotate(-45deg) translate(6px, -6px) !important; }

#slideMenuNav {
    position: fixed !important;
    top: 0 !important;
    left: -300px !important;
    width: 300px !important;
    background: linear-gradient(180deg, #0d6efd 0%, #1e40af 100%) !important;
    z-index: 9998 !important;
    transition: left 0.4s ease !important;
    padding: 80px 20px 20px !important;
    box-shadow: 4px 0 25px rgba(0,0,0,0.5) !important;
}
#slideMenuNav.active { left: 0 !important; }

#closeMenuBtn {
    position: absolute !important;
    top: 15px !important;
    right: 15px !important;
    background: rgba(255,255,255,0.1) !important;
    border: 2px solid rgba(255,255,255,0.3) !important;
    color: white !important;
    font-size: 22px !important;
    cursor: pointer !important;
    padding: 8px 12px !important;
    border-radius: 50% !important;
    width: 42px !important;
    height: 42px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 9999 !important;
}
#closeMenuBtn:hover { background: rgba(255,255,0,0.2) !important; transform: rotate(90deg) !important; }

#menuOverlayDiv {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0,0,0,0.6) !important;
    z-index: 9997 !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transition: all 0.3s ease !important;
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
.menu-list li { margin-bottom: 8px !important; }
.menu-list a {
    display: block !important;
    color: white !important;
    text-decoration: none !important;
    padding: 16px 20px !important;
    border-radius: 8px !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
    border-left: 3px solid transparent !important;
}
.menu-list a:hover {
    background: rgba(255,255,0,0.15) !important;
    color: yellow !important;
    transform: translateX(8px) !important;
    border-left-color: #ffff00 !important;
}
.menu-list li:last-child a { border-left-color: #ff4444 !important; }
.menu-list li:last-child a:hover {
    background: linear-gradient(90deg, #ff4444, #dc2626) !important;
    border-left-color: white !important;
}

/* BASE STYLES - UNCHANGED */
body, html { overflow-x: hidden; margin: 0; padding: 0; }
.main-header {
    background-color: #0d6efd;
    padding: 8px 15px;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
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
.header-left, .header-center, .header-right { flex: 0 0 auto; }
.header-center { text-align: center; }
.header-title {
    font-family: Arial, sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: #ffff00;
    margin: 0;
    white-space: nowrap;
}
.header-subtitle {
    font-family: Arial, sans-serif;
    font-size: 10px;
    font-weight: 600;
    color: #ffff00;
    margin: 2px 0 0 0;
    white-space: nowrap;
}
.clock-text {
    font-family: Arial, sans-serif;
    font-weight: 600;
    font-size: 12px;
    color: #ffff00;
    margin: 0;
    white-space: nowrap;
}
@media (max-width: 768px) {
    .header-grid { padding: 0 10px; }
    .header-title { font-size: 13px; }
    .header-subtitle { font-size: 9px; }
    .clock-text { font-size: 10px; }
}
@media (max-width: 576px) {
    .main-header { padding: 5px 10px; }
    .header-grid { flex-direction: column; gap: 5px; }
    .header-center { order: 1; }
    .header-left { order: 2; }
    .header-right { order: 3; }
    .header-title { font-size: 12px; }
    .header-subtitle { font-size: 8px; }
    .clock-text { font-size: 10px; }
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
        
        // 🛡️ CLEAN PREVIOUS
        burger.onclick = null;
        closeBtn.onclick = null;
        overlay.onclick = null;
        
        // 🔒 NEW HANDLERS
        burger.onclick = toggleMenu;
        closeBtn.onclick = toggleMenu;
        overlay.onclick = toggleMenu;
        
        // LINKS
        document.querySelectorAll(".menu-list a").forEach(link => {
            link.onclick = function(e) {
                setTimeout(toggleMenu, 10);
            };
        });
        
        handlersAttached = true;
        console.log("🔥 Burger menu locked in - 100% reliable");
    }
    
    // INIT MULTIPLE TIMES
    // 🔥 FORCE RESET FIRST
function init() { 
    // FORCE RESET FIRST
    const burger = document.getElementById("burgerMenuBtn");
    if (burger) {
        burger.classList.remove("active");
        burger.style.zIndex = "99999";
        burger.style.pointerEvents = "auto";
    }
    attachHandlers(); 
}

document.addEventListener("DOMContentLoaded", init);
setTimeout(init, 100);  // FASTER
setTimeout(init, 500);
setTimeout(init, 1500);

// 🔥 ESCAPE - FORCE CLOSE
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