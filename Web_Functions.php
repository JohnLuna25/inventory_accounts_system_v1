<?php
function showHeader() {
    echo '
    <div style="
        background-color: blue; 
        padding: 5px 10px; 
        width: 100%; 
        margin: 0; 
        text-align: center; 
        position: fixed; 
        top: 0; 
        left: 0;
        z-index: 1000;
    ">
        <h1 style="
            font-family: Arial, sans-serif; 
            font-size: 20px; 
            font-weight: bold; 
            color: yellow; 
            text-align: center; 
            margin: 0;
        ">
            SORCOM READY-MIX CONCRETE
        </h1>

        <p style="
            font-family: Arial, sans-serif; 
            font-size: 14px; 
            font-weight: 600; 
            color: yellow; 
            text-align: center; 
            margin: 0;
        ">
            Inventory and Accounts Management System.
        </p>

        <p style="
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            color: yellow; 
            text-align: right; 
            margin: 2px 10px 0 0;
        ">
            '.date("M d Y").'
        </p>
        <a href="home.php" 
            style="
                font-family: Arial, sans-serif;
                font-size: 15px;
                color: yellow;
                text-decoration: none;
                position: fixed;
                top: 10px; 
                left: 10px;   
    ">
  ⬅️ Return Home
</a>
    </div>
    ';
}
?>
