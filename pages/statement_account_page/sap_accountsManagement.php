<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

// Fetch all non-done orders
$sql = "SELECT * FROM statement_of_account WHERE order_status != 'done' ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Orders Management</title>
    <link rel="stylesheet" href="sap_accountsManagement.css">
</head>

<body>
    <h2 style="text-align:center; margin-top:60px;">Concrete Orders</h2>

    <div class="table-controls"
        style="width:100%; margin:auto; display:flex; justify-content: space-between; margin-bottom:2%;">
        <a href="sap_accountsForm.php" class="add-order-btn">+ Add Order</a>
        <a href="sap_finishedOrders.php" class="show-finished-btn">Show Finished Orders</a>
    </div>

    <table class="orders-table" style="width:100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Order Date</th>
                <th>Delivery Date</th>
                <th>Customer Name</th>
                <th>Address</th>
                <th>Concrete Type</th>
                <th>Cubic Meters</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['order_date']) ?></td>
                    <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    <td><?= htmlspecialchars($row['full_address']) ?></td>
                    <td><?= htmlspecialchars($row['concrete_type']) ?></td>
                    <td><?= htmlspecialchars($row['cubic_meters']) ?></td>
                    <td><?= htmlspecialchars($row['order_status']) ?></td>
                    <td>
                        <a href="sap_accountsForm.php?id=<?= $row['id'] ?>" style="color:#2196f3;">Edit</a> |
                        <a href="delete_order.php?id=<?= $row['id'] ?>" style="color:#f44336;"
                            onclick="return confirm('Delete this order?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>