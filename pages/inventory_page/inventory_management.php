<?php

// Corrected Includes
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
include_once __DIR__ . '/ip_postHandler.php';

?>



<!DOCTYPE html>
<html lang="en">

<?php
showHeader();
include 'ip_table.php';
?>

<body style="font-family: Arial; background-color: #BFC3AA;">

    <!-- Group for Inventory Control Buttons located at the upper left side of the tablee. -->
    <h2 style="font-size: 24px; text-align: center; margin-top: 65px; color: black;">Ready-Mix Inventory</h2>

    <div class="inventory-controls" style="text-align:left; margin-left: 5%;">
        <button onclick="openModal('addInventoryModal')"
            style="text-align: left; border: 1px solid black; border-radius: 20px; background-color: #4441e7ff; color: white; padding: 10px">Add
            Item</button>
        <button onclick="location.href='mixture_inventory.php'"
            style="text-align: left; border: 1px solid black; border-radius: 20px; background-color: #4441e7ff; color: white; padding: 10px">Open
            Mixture Inventory</button>
    </div>

    <!-- TABLE DISPLAY -->
    <?php include __DIR__ . '/ip_tableDisplay.php'; ?>

    <!-- ADD MODAL -->
    <?php include 'ip_addModal.php'; ?>

    <!-- EDIT MODAL -->
    <?php include 'ip_editModal.php'; ?>

    <!--  -->
    <?php include 'ip_functions.js'; ?>

</body>

</html>