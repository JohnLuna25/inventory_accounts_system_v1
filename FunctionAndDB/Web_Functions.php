<?php
// ==========================================================
// --- Web Functions ---
// ==========================================================

/**
 * Display the fixed header
 */
function showHeader()
{
    echo '
    <div style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: blue;
        padding: 5px 15px; /* smaller padding */
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
                     alt="Back" style="height: 30px; cursor: pointer;"> <!-- smaller -->
            </a>
            <a href="/Inventory-Accounts-System-v1/home_page/home.php">
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
            ' . date("M d, Y - h:i") . '
        </div>
    
    <style>background-color: #BFC3AA;</style>

    </div>
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

/**
 * Display sidebar navigation
 */
function showSidebar($username = "User")
{
    echo '
    <!-- Sidebar (Bootstrap Offcanvas) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" style="width:250px;">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarLabel">Welcome, ' . htmlspecialchars($username) . '!</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column">
        <button class="btn btn-primary mb-2" onclick="window.location.href=\'concrete_design_list.php\'">Concrete Design List</button>
        <button class="btn btn-primary mb-2" onclick="window.location.href=\'inventory_management.php\'">Inventory Management</button>
        <button class="btn btn-primary mb-2" onclick="window.location.href=\'accounts_management.php\'">Accounts Management</button>
        <button class="btn btn-primary mb-2" onclick="window.location.href=\'reports_management.php\'">Reports</button>
        <button class="btn btn-primary mb-2" onclick="window.location.href=\'settings_management.php\'">Settings</button>
        <div class="mt-auto">
            <button class="btn btn-danger w-100" onclick="
                if(confirm(\'Did you save all changes?\')) {
                    if(confirm(\'Are you sure you want to log out?\')) {
                        window.location.href=\'sorcomweb_login.php\';
                    }
                }
            ">Logout</button>
        </div>
      </div>
    </div>

    <!-- Sidebar Toggle Button -->
    <button class="btn btn-outline-primary position-fixed" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" style="top: 145px; left: 10px; z-index:1050;">
        ☰
    </button>
    ';
}
?>

<!-- Modal Script -->
<script>
    function showConcreteDetails(id) {
        fetch('view_design_ajax.php?id=' + id)
            .then(response => response.text())
            .then(data => {
                const modal = document.getElementById('concreteModal');
                const details = document.getElementById('concreteDetails');
                if (modal && details) {
                    details.innerHTML = data;
                    modal.style.display = 'flex';
                }
            })
            .catch(err => {
                alert('Error fetching design details.');
                console.error(err);
            });
    }

    function closeModal() {
        const modal = document.getElementById('concreteModal');
        if (modal) modal.style.display = 'none';
    }




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