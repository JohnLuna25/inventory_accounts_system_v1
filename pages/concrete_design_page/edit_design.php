<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required files
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check DB connection
if (!isset($conn)) {
    die("Database connection not established.");
}

// Check for design ID
if (!isset($_GET['id'])) {
    die("No concrete design selected.");
}

$design_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $psi = $_POST['concrete_psi'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $type = $_POST['concrete_type'] ?? '';
    $cubic = $_POST['cubic_meter'] ?? '';
    $slump = $_POST['concrete_slump'] ?? '';
    $wc = $_POST['wc_ratio'] ?? '';
    $cement_source = $_POST['cement_source'] ?? '';
    $admix_source = $_POST['admix_source'] ?? '';
    $cement_mix = $_POST['cement_mix'] ?? '';
    $concrete_price = $_POST['concrete_price'] ?? '';
    $fly_ash = $_POST['fly_ash'] ?? '';
    $sand = $_POST['sand'] ?? '';
    $three_eight = $_POST['three_eight'] ?? '';
    $three_fourth = $_POST['three_fourth'] ?? '';
    $g1 = $_POST['G1'] ?? '';
    $admix = $_POST['admix'] ?? '';
    $water = $_POST['water'] ?? '';
    $air = $_POST['air'] ?? '';
    $others = $_POST['others'] ?? '';
    $total = $_POST['total'] ?? '';

    $update_sql = "UPDATE sorcom_concrete_design_masterlist SET
        concrete_psi='$psi', curation_days='$curation_days', concrete_type='$type',
        cubic_meter='$cubic', concrete_slump='$slump', wc_ratio='$wc',
        cement_source='$cement_source', admix_source='$admix_source',
        cement_mix='$cement_mix', concrete_price='$concrete_price',
        fly_ash='$fly_ash', sand='$sand', three_eight='$three_eight',
        three_fourth='$three_fourth', G1='$g1', admix='$admix',
        water='$water', air='$air', others='$others', total='$total'
        WHERE id=$design_id";

    if ($conn->query($update_sql)) {
        echo "<script>alert('Design updated successfully!');window.location.href='concrete_design_list.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating design: {$conn->error}');</script>";
    }
}

$design_result = $conn->query("SELECT * FROM sorcom_concrete_design_masterlist WHERE id = $design_id");
if (!$design_result || $design_result->num_rows == 0) {
    die("Concrete design not found.");
}
$design = $design_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Concrete Design</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <?php showHeader(); ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
        }

        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;