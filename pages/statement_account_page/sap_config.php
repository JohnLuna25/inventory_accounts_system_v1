<?php
// sap_config.php

$conn = new mysqli("localhost", "root", "", "sorcom_db");

// Check connection
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
?>