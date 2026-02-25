<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

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

// Fetch done orders
$doneOrders = getDoneOrders($conn);

// Function to get done orders
function getDoneOrders($conn)
{
    $sql = "SELECT * FROM statement_of_account WHERE order_status='Done' ORDER BY sap_id DESC";
    $result = $conn->query($sql);

    $orders = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    return $orders;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finished Orders</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="sap_accountsManagement.css">
</head>

<body>

    <div class="main-content">

        <!-- Banner for Title and Back Button -->
        <div class="page-title-bar">
            <h2>Finished Orders</h2>
            <div class="button-container">
                <a href="sap_accountsManagement.php" class="btn btn-primary">← Back to Orders</a>
            </div>
        </div>

        <!-- Banner for Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date of Order</th>
                        <th>Customer Name</th>
                        <th>Full Address</th>
                        <th>Concrete Design</th>
                        <th>Cubic Meters</th>
                        <th>Delivery Date</th>
                        <th>Order Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($doneOrders)): ?>
                        <?php foreach ($doneOrders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['sap_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($order['order_date']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['full_address']) ?></td>
                                <td><?= htmlspecialchars($order['concrete_type']) ?></td>
                                <td><?= htmlspecialchars($order['cubic_meters']) ?></td>
                                <td><?= htmlspecialchars($order['delivery_date']) ?></td>
                                <td><?= htmlspecialchars($order['order_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-orders">No finished orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>