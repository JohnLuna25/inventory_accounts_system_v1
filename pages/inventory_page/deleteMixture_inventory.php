<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided and is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Use Prepared Statement to prevent SQL Injection
    $stmt = $conn->prepare("DELETE FROM sorcom_mixture_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Optional: Add success message logic here
    }

    $stmt->close();
}

$conn->close();

// Redirect back to inventory
header("Location: mixture_inventory.php");
exit;
?>