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

<head>
    <link rel="stylesheet" href="inventory_management.css">
</head>

<body style="font-family: Arial; background-color: #BFC3AA;">

    <!-- Group for Inventory Control Buttons located at the upper left side of the tablee. -->
    <h2 style="font-size: 24px; text-align: center; margin-top: 65px; color: black;">Ready-Mix Inventory</h2>

    <div class="inventory-controls"
        style="width:95%; margin:auto; display:flex; justify-content: space-between; margin-bottom:10px;">

        <a href="ip_addModal.php" class="add-inventory-btn">+ Add Inventory</a>

        <a href="mixture_inventory.php" class="open-mixture-btn">
            Open Mixture Inventory
        </a>

    </div>

    <!-- TABLE DISPLAY -->
    <?php include __DIR__ . '/ip_tableDisplay.php'; ?>


    <!-- EDIT MODAL -->
    <?php include 'ip_editModal.php'; ?>

    <!-- FUNCTION SOURCE -->
    <script src="ip_functions.js"></script>


</body>

</html>