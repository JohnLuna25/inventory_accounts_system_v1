<?php
function showHeader()
{
    echo '
<div class="main-header">
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

<style>
    .main-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #0d6efd;
        padding: 8px 15px;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        box-sizing: border-box;
        min-height: 60px;
    }

    .header-left {
        flex: 0 0 auto;
    }

    .home-icon {
        height: 28px;
        cursor: pointer;
    }

    .header-center {
        flex: 1;
        text-align: center;
        padding: 0 10px;
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

    .header-right {
        flex: 0 0 auto;
    }

    .clock-text {
        font-family: Arial, sans-serif;
        font-weight: 600;
        font-size: 12px;
        color: #ffff00;
        margin: 0;
        white-space: nowrap;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .main-header {
            padding: 6px 10px;
            min-height: 55px;
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
            flex-direction: row;
            flex-wrap: wrap;
            padding: 5px 10px;
            min-height: auto;
        }

        .header-left {
            flex: 0 0 auto;
            order: 1;
        }

        .header-center {
            flex: 1 1 auto;
            order: 2;
            text-align: center;
            padding: 0 5px;
            min-width: 0;
        }

        .header-right {
            flex: 0 0 auto;
            order: 3;
        }

        .home-icon {
            height: 22px;
        }

        .header-title {
            font-size: 11px;
            line-height: 1.2;
        }

        .header-subtitle {
            font-size: 7px;
            line-height: 1.2;
        }

        .clock-text {
            font-size: 9px;
        }
    }

    @media (max-width: 400px) {
        .header-title {
            font-size: 10px;
        }

        .header-subtitle {
            font-size: 6px;
        }

        .clock-text {
            font-size: 8px;
        }

        .home-icon {
            height: 20px;
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

    var iSorcom = 0;
    var iIAMS = 0;

    var txtSorcom = 'SORCOM READY-MIX CONCRETE';
    var txtIAMS = 'Inventory and Accounts Management System';

    var speedTitle = 80;
    var speedSubtitle = 80;

    function sorcomWriter() {
        if (iSorcom < txtSorcom.length) {
            window.onload = sorcomWriter; // ❌ remove this
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


</script>