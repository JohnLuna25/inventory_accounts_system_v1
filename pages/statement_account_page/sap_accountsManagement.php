<?php
ob_start();
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

// Delete function
function deleteOrder($conn, $sap_id)
{
    $stmt = $conn->prepare("DELETE FROM statement_of_account WHERE sap_id = ?");
    $stmt->bind_param("s", $sap_id);
    return $stmt->execute();
}

// Handle delete
if (isset($_GET['delete'])) {
    $sap_id = $_GET['delete'];
    if (deleteOrder($conn, $sap_id)) {
        header("Location: sap_accountsManagement.php?deleted=1");
        exit;
    }
}

// Fetch all non-done orders
$sql = "SELECT * FROM statement_of_account WHERE order_status != 'done' ORDER BY sap_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Orders Management</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="sap_accountsManagement.css">
</head>

<body>

    <div class="main-content">

        <!-- Banner for Title and Buttons -->
        <div class="page-title-bar">
            <h2>Concrete Orders</h2>
            <div class="button-container">

                <!-- LEFT SIDE -->
                <div class="left-buttons">
                    <a href="sap_accountsForm.php" class="btn btn-primary">+ Add Order</a>
                    <a href="sap_finishedOrders.php" class="btn btn-primary">Show Finished Orders</a>
                </div>

                <!-- RIGHT SIDE -->
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search orders...">
                    <button onclick="filterOrders()" class="btn btn-primary">Search</button>
                </div>

            </div>

        </div>

        <!-- Banner for Table -->
        <div class="table-container">
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Customer Name</th>
                        <th>Address</th>
                        <th>Concrete Design</th>
                        <th>Curation Days</th>
                        <th>Concrete Type</th>
                        <th>Cubic Meters</th>
                        <th>Concrete Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['sap_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['order_date']) ?></td>
                                <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td><?= htmlspecialchars($row['full_address']) ?></td>
                                <td><?= htmlspecialchars($row['concrete_psi'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['curation_days'] ?? 'N/A') ?></td>
                                <td>
                                    <?= htmlspecialchars($row['concrete_type']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['cubic_meters']) ?>
                                </td>
                                <td>
                                    ₱<?= number_format($row['sap_concrete_price'] ?? 0, 2) ?>
                                </td>
                                <td class="order-status">
                                    <?php
                                    $status = strtolower($row['order_status']);
                                    $statusClass = str_replace(' ', '-', $status);
                                    ?>
                                    <span class="<?= $statusClass ?>"><?= htmlspecialchars($row['order_status']) ?></span>
                                </td>
                                <td>
                                    <a href="sap_accountsForm.php?id=<?= urlencode($row['sap_id']) ?>"
                                        class="btn btn-view">Edit</a>
                                    <a href="sap_accountsManagement.php?delete=<?= urlencode($row['sap_id']) ?>"
                                        class="btn btn-delete" onclick="return confirm('Delete this order?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="no-orders">No records of Current Orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>

        document.getElementById("searchInput").addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                filterOrders();
            }
        });

        function filterOrders() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase();
            let table = document.getElementById("ordersTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let tds = tr[i].getElementsByTagName("td");
                let found = false;

                for (let j = 0; j < tds.length; j++) {
                    let txtValue = tds[j].textContent || tds[j].innerText;

                    if (txtValue.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                tr[i].style.display = found ? "" : "none";
            }
        }
    </script>

</body>

</html>