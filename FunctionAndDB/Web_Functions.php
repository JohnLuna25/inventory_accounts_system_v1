<?php
function showHeader()
{
    echo '
    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: blue;
        padding: 5px 15px;
        width: 100%;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        box-sizing: border-box;
        height: 60px;
    ">
        <!-- Left Links -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="javascript:history.back()">
                <img src="/Inventory-Accounts-System-v1/assets/images/back_button.png" 
                     alt="Back" style="height: 30px; cursor: pointer;">
            </a>
            <a href="/Inventory-Accounts-System-v1/pages/home_page/home.php">
                <img src="/Inventory-Accounts-System-v1/assets/images/home_button.png" 
                     alt="Home" style="height: 30px; cursor: pointer;">
            </a>
        </div>

        <!-- Center Title -->
        <div style="
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        ">
            <h1 style="font-family: Arial, sans-serif; font-size: 18px; font-weight: bold; color: yellow; margin: 0;">
                SORCOM READY-MIX CONCRETE
            </h1>
            <p style="font-family: Arial, sans-serif; font-size: 12px; font-weight: 600; color: yellow; margin: 0;">
                Inventory and Accounts Management System.
            </p>
        </div>

        <!-- Right Date -->
        <div style="font-family: Arial, sans-serif; font-weight: 600; font-size: 14px; color: yellow;">
           <p id="clock"></p>
        </div>
    </div>

    <script>
        function realClockTime() {
            const now = new Date();
            const options = { month: "short", day: "2-digit", year: "numeric" };
            const dateStr = now.toLocaleDateString("en-US", options).replace(",", ".");
            let hours = now.getHours().toString().padStart(2, "0");
            let minutes = now.getMinutes().toString().padStart(2, "0");
            let seconds = now.getSeconds().toString().padStart(2, "0");
            document.getElementById("clock").textContent = dateStr + " || " + hours + ":" + minutes + ":" + seconds;
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
            window.onload = sorcomWriter;
            document.getElementById("demo").innerHTML += txtSorcom.charAt(iSorcom);
            iSorcom++;
            setTimeout(sorcomWriter, speedTitle);
        } else {
            // Once the first text finishes, start the second
            document.getElementById("demo").innerHTML += "<br>"; // optional line break
            setTimeout(iamsWriter, 500); // delay before second line starts
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