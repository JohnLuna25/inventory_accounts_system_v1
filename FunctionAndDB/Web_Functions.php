<?php
function showHeader()
{
    echo '
<div class="main-header">
    <div class="header-grid">
        <!-- Left: Home Button -->
        <div class="header-left">
            <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php">
                <img src="/Inventory-Accounts-System-v1/assets/images/home_button.png" alt="Home" class="home-icon">
            </a>
        </div>

        <!-- Center: Title -->
        <div class="header-center">
            <h1 class="header-title">SORCOM READY-MIX CONCRETE</h1>
            <p class="header-subtitle">Inventory and Accounts Management System</p>
        </div>

        <!-- Right: Clock -->
        <div class="header-right">
            <p id="clock" class="clock-text"></p>
        </div>
    </div>
</div>

<style>

    body, html {
        overflow-x: hidden; /* Prevents horizontal scrollbar */
        margin: 0;
        padding: 0;
    }
    .main-header {
        background-color: #0d6efd;
        padding: 8px 15px;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        box-sizing: border-box;
        overflow: hidden; /* Prevents horizontal scroll */
    }

    .header-grid {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto; /* Centers the grid */
        padding: 0 15px; /* Adds padding to prevent edge touching */
    }

    .header-left,
    .header-center,
    .header-right {
        flex: 0 0 auto;
    }

    .header-center {
        text-align: center;
    }

    .home-icon {
        height: 28px;
        cursor: pointer;
    }

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
        .header-grid {
            padding: 0 10px;
        }

        .home-icon {
            height: 24px;
        }

        .header-title {
            font-size: 13px;
        }

        .header-subtitle {
            font-size: 9px;
        }

        .clock-text {
            font-size: 10px;
        }
    }

    @media (max-width: 576px) {
        .main-header {
            padding: 5px 10px;
        }

        .header-grid {
            flex-direction: column;
            gap: 5px;
        }

        .header-center {
            order: 1;
        }

        .header-left {
            order: 2;
        }

        .header-right {
            order: 3;
        }

        .home-icon {
            height: 22px;
        }

        .header-title {
            font-size: 12px;
        }

        .header-subtitle {
            font-size: 8px;
        }

        .clock-text {
            font-size: 10px;
        }
    }
</style>

<script>
    function realClockTime() {
        const now = new Date();
        const options = { month: "short", day: "2-digit", year: "numeric" };
        const dateStr = now.toLocaleDateString("en-US", options).replace(",", ".");
        let hours = now.getHours().toString().padStart(2, "0");
        let minutes = now.getMinutes().toString().padStart(2, "0");
        let seconds = now.getSeconds().toString().padStart(2, "0");
        const clockEl = document.getElementById("clock");
        if (clockEl) {
            clockEl.textContent = dateStr + " || " + hours + ":" + minutes + ":" + seconds;
        }
    }
    setInterval(realClockTime, 1000);
    realClockTime();
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