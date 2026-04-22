<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Get record by ID
$id = $_GET['id'] ?? 0;
$result = $conn->query("SELECT * FROM sorcom_mixture_inventory WHERE id = $id");
$row = $result->fetch_assoc();

// Update record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cement = $_POST['cement'];
    $fly_ash = $_POST['fly_ash'];
    $sand = $_POST['sand'];
    $three_fourth = $_POST['three_fourth'];
    $three_eight = $_POST['three_eight'];
    $g1 = $_POST['g1'];
    $admix = $_POST['admix'];
    $water = $_POST['water'];
    $air = $_POST['air'];
    $others = $_POST['others'];
    $total = $_POST['total'];

    $stmt = $conn->prepare("UPDATE sorcom_mixture_inventory SET cement=?, fly_ash=?, sand=?, `3_4`=?, `3_8`=?, g1=?, admix=?, water=?, air=?, others=?, total=? WHERE id=?");
    $stmt->bind_param("dddddddddddi", $cement, $fly_ash, $sand, $three_fourth, $three_eight, $g1, $admix, $water, $air, $others, $total, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: mixture_inventory.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Mixture</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../inventory_page/mixture_inventory.css">
    <link rel="stylesheet" href="../inventory_page/editMixture_inventory.css">
    <?php showHeader(); ?>
</head>

<body>

    <div class="form-container">
        <h2 style="color: black;">Edit Mixture</h2>

        <form method="POST">
            <div class="form-row">
                <label>Cement:</label>
                <input type="number" step="0.01" name="cement" value="<?php echo $row['cement']; ?>" required>
            </div>

            <div class="form-row">
                <label>Fly Ash:</label>
                <input type="number" step="0.01" name="fly_ash" value="<?php echo $row['fly_ash']; ?>" required>
            </div>

            <div class="form-row">
                <label>Sand:</label>
                <input type="number" step="0.01" name="sand" value="<?php echo $row['sand']; ?>" required>
            </div>

            <div class="form-row">
                <label>3/4:</label>
                <input type="number" step="0.01" name="three_fourth" value="<?php echo $row['3_4']; ?>" required>
            </div>

            <div class="form-row">
                <label>3/8:</label>
                <input type="number" step="0.01" name="three_eight" value="<?php echo $row['3_8']; ?>" required>
            </div>

            <div class="form-row">
                <label>G1:</label>
                <input type="number" step="0.01" name="g1" value="<?php echo $row['g1']; ?>" required>
            </div>

            <div class="form-row">
                <label>Admix:</label>
                <input type="number" step="0.01" name="admix" value="<?php echo $row['admix']; ?>" required>
            </div>

            <div class="form-row">
                <label>Water:</label>
                <input type="number" step="0.01" name="water" value="<?php echo $row['water']; ?>" required>
            </div>

            <div class="form-row">
                <label>Air:</label>
                <input type="number" step="0.01" name="air" value="<?php echo $row['air']; ?>" required>
            </div>

            <div class="form-row">
                <label>Others:</label>
                <input type="number" step="0.01" name="others" value="<?php echo $row['others']; ?>" required>
            </div>

            <div class="form-row">
                <label>Total:</label>
                <input type="number" step="0.01" name="total" value="<?php echo $row['total']; ?>" required>
            </div>

            <div class="form-row">
                <button type="submit">Update</button>
                <a href="mixture_inventory.php">Cancel</a>
            </div>
        </form>
    </div>

</body>

</html>