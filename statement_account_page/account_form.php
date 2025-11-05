<?php
include '../FunctionAndDB/Web_Functions.php';
showHeader();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sorcom_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$order_date = $delivery_date = $customer_name = $full_address = $concrete_type = $cubic_meters = $order_status = "";
$error_message = "";

// Check if URL contains data (edit mode)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $order_date = $_GET['order_date'] ?? '';
    $delivery_date = $_GET['delivery_date'] ?? '';
    $customer_name = $_GET['customer_name'] ?? '';
    $full_address = $_GET['full_address'] ?? '';
    $concrete_type = $_GET['concrete_type'] ?? '';
    $cubic_meters = $_GET['cubic_meters'] ?? '';
    $order_status = $_GET['order_status'] ?? '';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_date = $_POST['order_date'];
    $delivery_date = $_POST['delivery_date'];
    $customer_name = $_POST['customer_name'];
    $full_address = $_POST['full_address'];
    $concrete_type = $_POST['concrete_type'];
    $cubic_meters = $_POST['cubic_meters'];
    $order_status = $_POST['order_status'];

    // Check if record already exists
    $check = $conn->query("SELECT id FROM statement_of_account LIMIT 1");

    if ($check && $check->num_rows > 0) {
        // Update existing record (replace data)
        $row = $check->fetch_assoc();
        $id = $row['id'];

        $stmt = $conn->prepare("UPDATE statement_of_account 
            SET order_date=?, customer_name=?, full_address=?, concrete_type=?, cubic_meters=?, delivery_date=?, order_status=? 
            WHERE id=?");

        $stmt->bind_param("ssssdssi", $order_date, $customer_name, $full_address, $concrete_type, $cubic_meters, $delivery_date, $order_status, $id);
    } else {
        // Insert new record if none exists
        $stmt = $conn->prepare("INSERT INTO statement_of_account 
            (order_date, customer_name, full_address, concrete_type, cubic_meters, delivery_date, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssdss", $order_date, $customer_name, $full_address, $concrete_type, $cubic_meters, $delivery_date, $order_status);
    }

    if ($stmt->execute()) {
        header("Location: accounts_management.php?success=1");
        exit;
    } else {
        $error_message = "Error saving order: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Order Form</title>
</head>

<body
    style="font-family: Arial, sans-serif; background-color: #bfc3aa; display: flex; justify-content: center; padding-top: 50px; margin: 0;">

    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST"
        style="background-color: #fff; padding: 25px 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15); display: grid; grid-template-columns: 1fr 1fr; column-gap: 20px; row-gap: 15px; width: 800px; max-width: 90%;">
        <h2 style="grid-column: 1 / -1; text-align: center; margin-bottom: 20px;">Concrete Order Form</h2>

        <div>
            <label for="order_date">Date of Order:</label>
            <input type="date" id="order_date" name="order_date" required value="<?= htmlspecialchars($order_date) ?>"
                style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="delivery_date">Delivery Date:</label>
            <input type="date" id="delivery_date" name="delivery_date" required
                value="<?= htmlspecialchars($delivery_date) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" placeholder="Enter full name" required
                value="<?= htmlspecialchars($customer_name) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="full_address">Full Address:</label>
            <input type="text" id="full_address" name="full_address"
                placeholder="House/Blk/Lt, St., Subd., Brgy., City/Municipality, Prov." required
                value="<?= htmlspecialchars($full_address) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="concrete_type">Concrete Design:</label>
            <select id="concrete_type" name="concrete_type" required style="width: 100%; padding: 8px;">
                <option value="">--Select Type--</option>
                <option value="standard" <?= $concrete_type === 'standard' ? 'selected' : '' ?>>Standard</option>
                <option value="decorative" <?= $concrete_type === 'decorative' ? 'selected' : '' ?>>Decorative</option>
                <option value="high_strength" <?= $concrete_type === 'high_strength' ? 'selected' : '' ?>>High Strength
                </option>
            </select>
        </div>

        <div>
            <label for="cubic_meters">Cubic Meters:</label>
            <input type="number" id="cubic_meters" name="cubic_meters" min="0" step="0.1" placeholder="Enter quantity"
                required value="<?= htmlspecialchars($cubic_meters) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="order_status">Order Status:</label>
            <select id="order_status" name="order_status" required style="width: 100%; padding: 8px;">
                <option value="">--Select Status--</option>
                <option value="pending" <?= $order_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="in_progress" <?= $order_status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="delivered" <?= $order_status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="done" <?= $order_status === 'done' ? 'selected' : '' ?>>Done</option>
            </select>
        </div>

        <div style="grid-column: 1 / -1; display: flex; justify-content: center; margin-top: 20px;">
            <input type="submit" value="Save Order"
                style="background-color: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 5px; margin-right: 10px; cursor: pointer;">
            <input type="button" value="Cancel" onclick="window.history.back();"
                style="background-color: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
        </div>
    </form>
</body>

</html>