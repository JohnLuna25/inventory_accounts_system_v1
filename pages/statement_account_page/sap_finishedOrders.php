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

// Fetch done orders (newest to oldest)
function getDoneOrders($conn)
{
    $sql = "SELECT * FROM statement_of_account 
            WHERE order_status='Done' 
            ORDER BY order_date DESC, sap_id DESC";

    $result = $conn->query($sql);

    $orders = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    return $orders;
}

$doneOrders = getDoneOrders($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="sap_accountsManagement.css">

    <style>
        .month-separator td {
            background: linear-gradient(135deg, #386da1, #456183, #575d63);
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            letter-spacing: 1px;
        }

        .page-title-bar {
            background: white;
            margin: 15px 10px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title-bar h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            font-weight: 300;
        }

        .search-container {
            display: flex;
            justify-content: flex-end;
            /* 👉 pushes everything to the RIGHT */
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        #searchInput {
            width: 400px;
            /* 👉 fixed width = actually noticeable */
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
        }

        #searchInput:focus {
            outline: none;
            border-color: #007bff;
        }

        .search-container button {
            padding: 10px 15px;
            border-radius: 20px;
        }
    </style>
</head>

<body>

    <div class="main-content">

        <div class="page-title-bar">
            <h2>Transaction History</h2>
            <div class="button-container">
                <a href="sap_accountsManagement.php" class="btn btn-primary">← Back to Orders</a>
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search orders...">
                    <button onclick="filterOrders()" class="btn btn-primary">Search</button>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table id="ordersTable">
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

                        <?php
                        $currentMonth = "";

                        foreach ($doneOrders as $order):

                            $orderDate = $order['order_date'] ?? '';
                            $monthLabel = date("F Y", strtotime($orderDate));
                            ?>

                            <?php if ($monthLabel !== $currentMonth): ?>
                                <?php $currentMonth = $monthLabel; ?>
                                <tr class="month-separator">
                                    <td colspan="8"><?= strtoupper($monthLabel) ?></td>
                                </tr>
                            <?php endif; ?>

                            <tr>
                                <td><?= htmlspecialchars($order['sap_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($order['order_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['full_address'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['concrete_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['cubic_meters'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['delivery_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($order['order_status'] ?? '') ?></td>
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

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const input = document.getElementById("searchInput");

            input.addEventListener("keyup", function (event) {
                if (event.key === "Enter") {
                    filterOrders();
                }
            });

            window.filterOrders = function () {
                let filter = input.value.toLowerCase();
                let table = document.getElementById("ordersTable");
                let tr = table.getElementsByTagName("tr");

                for (let i = 1; i < tr.length; i++) {

                    // Skip month separators
                    if (tr[i].classList.contains("month-separator")) {
                        tr[i].style.display = "";
                        continue;
                    }

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
            };

        });
    </script>

</body>

</html>