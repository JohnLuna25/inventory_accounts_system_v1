<?php
include '../FunctionAndDB/Web_Functions.php';
showHeader();

// Database connection
$conn = new mysqli("localhost", "root", "", "sorcom_db");
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

// Handle Done button with POST-Redirect-GET pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['done_btn'])) {
    $order_date = $_POST['order_date'];
    $customer_name = $_POST['customer_name'];
    $delivery_date = $_POST['delivery_date'];

    $stmt = $conn->prepare("UPDATE statement_of_account SET order_status='done' WHERE order_date=? AND customer_name=? AND delivery_date=?");
    $stmt->bind_param("sss", $order_date, $customer_name, $delivery_date);

    if ($stmt->execute()) {
        $stmt->close();
        // Redirect to the same page with success message parameters
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&customer=" . urlencode($customer_name));
        exit;
    } else {
        $success_msg = "<span style='color:red;'>Error: " . $stmt->error . "</span>";
        $stmt->close();
    }
}

// Show success message only if GET parameters exist
$success_msg = "";
if (isset($_GET['success']) && isset($_GET['customer'])) {
    $success_msg = "Order for <strong>" . htmlspecialchars($_GET['customer']) . "</strong> marked as done successfully!";
}

// Fetch pending orders
$sql = "SELECT * FROM statement_of_account WHERE order_status='pending' ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Accounts Management</title>
</head>

<body style="font-family: Arial, sans-serif; padding: 20px; background: #bfc3aa; color: #000; text-align: center;">

    <h2 style="margin-top: 65px; margin-bottom: 20px;">Statement of Account</h2>

    <?php if (!empty($success_msg)): ?>
        <div id="flash-msg"
            style="margin: 20px auto; padding: 10px; width: 90%; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
            <?= $success_msg ?>
        </div>
    <?php endif; ?>

    <div style="text-align: left; margin-left: 5%; margin-bottom: 20px;">
        <button onclick="window.location.href='account_form.php'"
            style="border: 1px solid black; border-radius: 20px; background-color: #4441e7; color: white; padding: 10px; cursor: pointer;">Add
            Order</button>
        <button onclick="window.location.href='finished_orders.php'"
            style="border: 1px solid black; border-radius: 20px; background-color: #4441e7; color: white; padding: 10px; cursor: pointer;">Show
            Finished Orders</button>
    </div>

    <table
        style="border-collapse: collapse; width: 90%; margin: 20px auto; background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; font-size: 13px;">
        <thead style="background-color: #0b63c4; color: white;">
            <tr>
                <th style="border: 1px solid black; padding: 10px 15px;">Date of Order</th>
                <th style="border: 1px solid black; padding: 10px 15px;">Customer Name</th>
                <th style="border: 1px solid black; padding: 10px 15px;">Full Address</th>
                <th style="border: 1px solid black; padding: 10px 15px;">Concrete Design</th>
                <th style="border: 1px solid black; padding: 10px 15px;">Cubic Meters</th>
                <th style="border: 1px solid black; padding: 10px 15px;">Delivery Date</th>
                <th style="border: 1px solid black; padding: 10px 15px;">Order Status</th>
                <th style="border: 1px solid black; padding: 10px 15px; min-width: 160px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr style="background-color: #fff;">
                        <form method="POST">
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['order_date']) ?><input type="hidden" name="order_date"
                                    value="<?= htmlspecialchars($row['order_date']) ?>">
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['customer_name']) ?><input type="hidden" name="customer_name"
                                    value="<?= htmlspecialchars($row['customer_name']) ?>">
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['full_address']) ?><input type="hidden" name="full_address"
                                    value="<?= htmlspecialchars($row['full_address']) ?>">
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['concrete_type']) ?><input type="hidden" name="concrete_type"
                                    value="<?= htmlspecialchars($row['concrete_type']) ?>">
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['cubic_meters']) ?><input type="hidden" name="cubic_meters"
                                    value="<?= htmlspecialchars($row['cubic_meters']) ?>">
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['delivery_date']) ?><input type="hidden" name="delivery_date"
                                    value="<?= htmlspecialchars($row['delivery_date']) ?>">
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <?= htmlspecialchars($row['order_status']) ?>
                            </td>
                            <td style="border: 1px solid black; padding: 10px 15px;">
                                <button type="submit" name="done_btn"
                                    style="background-color:#4caf50;color:white;border:none;padding:3px 6px;margin:0 1px;border-radius:3px;cursor:pointer;font-size:13px;">Done</button>
                                <button type="button"
                                    onclick="window.location.href='account_form.php?order_date=<?= $row['order_date'] ?>&customer_name=<?= urlencode($row['customer_name']) ?>&full_address=<?= urlencode($row['full_address']) ?>&concrete_type=<?= urlencode($row['concrete_type']) ?>&cubic_meters=<?= $row['cubic_meters'] ?>&delivery_date=<?= $row['delivery_date'] ?>&order_status=<?= $row['order_status'] ?>'"
                                    style="background-color:#ff9800;color:white;border:none;padding:3px 6px;margin:0 1px;border-radius:3px;cursor:pointer;font-size:13px;">Edit</button>
                                <button type="button"
                                    onclick="if(confirm('Are you sure you want to delete this order?')) this.closest('tr').remove();"
                                    style="background-color:#f44336;color:white;border:none;padding:3px 6px;margin:0 1px;border-radius:3px;cursor:pointer;font-size:13px;">Delete</button>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="border: 1px solid black; padding: 10px 15px;">No pending orders found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        // Flash message disappears after 5 seconds
        window.addEventListener('DOMContentLoaded', () => {
            const flash = document.getElementById('flash-msg');
            if (flash) {
                setTimeout(() => { flash.style.display = 'none'; }, 5000);
            }
        });
    </script>

</body>

</html>

<?php $conn->close(); ?>