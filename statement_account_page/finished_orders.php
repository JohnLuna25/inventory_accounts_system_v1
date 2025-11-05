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

// Fetch done orders
$doneOrders = getDoneOrders($conn);

// Function to get done orders
function getDoneOrders($conn)
{
    $sql = "SELECT * FROM statement_of_account WHERE order_status='Done' ORDER BY order_date DESC";
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="accounts_management.css">
</head>

<body>

    <h2>Finished Orders</h2>

    <div style="text-align: left; margin-left: 5%; margin-bottom: 20px;">
        <button
            style="border: 1px solid black; border-radius: 20px; background-color: #4441e7ff; color: white; padding: 10px"
            onclick="window.location.href='accounts_management.php'">
            Back to Orders
        </button>
    </div>

    <table>
        <thead>
            <tr>
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
                    <td colspan="7">No finished orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>