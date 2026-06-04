<?php
ob_start();
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

function checkRemainingInventory($conn, $psi, $days, $type)
{
    // Net stock = total_stock_in - total_stock_out
    $stmt = $conn->prepare("
        SELECT SUM(stocks_in) - SUM(stocks_out) as net_stock
        FROM sorcom_readymix_inventory 
        WHERE design_mix_id = ? AND curation_days = ? AND concrete_type = ?
    ");
    $stmt->bind_param("sss", $psi, $days, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $net_stock = $row ? floatval($row['net_stock']) : 0;

    return $net_stock > 0 ? $net_stock : 0;
}

function automateOrderStatus($conn, $sap_id, $delivery_date, $current_status)
{
    $today = date('Y-m-d');

    // Determine what status SHOULD be
    if ($today < $delivery_date) {
        $new_status = 'pending';
    } elseif ($today === $delivery_date) {
        $new_status = 'in transit';
    } else {
        $new_status = 'done';
    }

    // Rule: If manually marked 'done' early, respect it UNLESS delivery date has passed
    if (strtolower($current_status) === 'done' && $today < $delivery_date) {
        return $current_status; // Respect early manual done
    }

    // Only update DB if status actually changed
    if (strtolower($current_status) !== $new_status) {
        $stmt = $conn->prepare("
            UPDATE statement_of_account 
            SET order_status = ? 
            WHERE sap_id = ?
        ");
        $stmt->bind_param("ss", $new_status, $sap_id);
        $stmt->execute();
        $stmt->close();
    }

    return $new_status;
}

// ✅ SAFE SORTING (uses EXISTING columns only)
$sql = "
    SELECT * FROM statement_of_account 
    WHERE order_status != 'done' 
    ORDER BY 
        order_date DESC,   -- 🆗 Uses existing column
        sap_id DESC        -- 🆗 Uses existing column  
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Transactions Management</title>
    <link rel="stylesheet" href="sap_accountsManagement.css">
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <?php include '../../assets/resources.php'; ?>
</head>

<body>

    <div class="main-content">

        <!-- Banner for Title and Buttons -->
        <div class="page-title-bar">
            <h2 style="font-family: 'Poppins', sans-serif;">Concrete Transactions</h2>
            <div class="button-container">

                <!-- LEFT SIDE -->
                <div class="left-buttons">
                    <a href="add_accountsForm.php" class="btn btn-primary">+ Add Order</a>
                    <a href="sap_transactionHistory.php" class="btn btn-primary">Transaction History</a>
                    <a href="sales_analytics.php" class="btn btn-primary">Transaction Summary</a>
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
                        <th>Concrete Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php $row_index = 0; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $row_index++; ?>
                            <?php
                            // ✅ AUTO-UPDATE STATUS BASED ON DELIVERY DATE
                            $row['order_status'] = automateOrderStatus(
                                $conn,
                                $row['sap_id'],
                                $row['delivery_date'],
                                $row['order_status']
                            );
                            ?>
                            <?php
                            // ✅ SHOW FULL ADDRESS (NO TRIMMING)
                            $raw_address = $row['full_address'] ?? 'No address';
                            $full_address = str_replace(' || ', ', ', $raw_address);
                            $full_address = trim($full_address, ', |');
                            ?>

                            <!-- Main Row (8 columns) -->
                            <tr>
                                <td><?= htmlspecialchars($row['sap_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['order_date']) ?></td>
                                <td><?= htmlspecialchars($row['delivery_date']) ?></td>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>

                                <!-- ✅ FIXED ADDRESS CELL -->
                                <td class="address-cell">
                                    <?= htmlspecialchars($full_address) ?>
                                </td>

                                <td>₱<?= number_format($row['sap_concrete_price'] ?? 0, 2) ?></td>
                                <td class="order-status">
                                    <?php
                                    $status = strtolower($row['order_status']);
                                    $statusClass = str_replace(' ', '-', $status);
                                    ?>
                                    <a href="edit_accountsForm.php?sap_id=<?= urlencode($row['sap_id']) ?>"
                                        class="status-link <?= $statusClass ?>">
                                        <?= htmlspecialchars($row['order_status']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-view"
                                        onclick="toggleDropbar(<?= $row_index ?>); return false;">View</a>
                                </td>
                            </tr>

                            <!-- Dropbar Row (2x4 grid) -->
                            <tr id="dropbar-<?= $row_index ?>" class="dropbar-row">
                                <td colspan="8">
                                    <table class="dropbar-inner">
                                        <!-- ROW 1: HEADERS -->
                                        <tr>
                                            <td class="dropbar-header">Concrete Design</td>
                                            <td class="dropbar-header">Curation Days</td>
                                            <td class="dropbar-header">Concrete Type</td>
                                            <td class="dropbar-header">Units</td>
                                        </tr>
                                        <!-- ROW 2: DETAILS -->
                                        <tr>
                                            <td class="dropbar-detail"><?= htmlspecialchars($row['concrete_psi'] ?? 'N/A') ?>
                                            </td>
                                            <td class="dropbar-detail"><?= htmlspecialchars($row['curation_days'] ?? 'N/A') ?>
                                            </td>
                                            <td class="dropbar-detail"><?= htmlspecialchars($row['concrete_type'] ?? 'N/A') ?>
                                            </td>
                                            <td class="dropbar-detail">
                                                <?= htmlspecialchars($row['sap_units']) ?> units<br>
                                                <small style="color:#666;">
                                                    <?php
                                                    $type_for_stock = (empty($row['concrete_type']) || $row['concrete_type'] === '0') ? 'ORDINARY' : $row['concrete_type'];
                                                    $inv_check = checkRemainingInventory($conn, $row['concrete_psi'], $row['curation_days'], $type_for_stock);
                                                    if ($inv_check > 0) {
                                                        echo "📦 {$inv_check} left";
                                                    }
                                                    // Removed the "Out of stock" message completely
                                                    ?>
                                                </small>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-orders">No records of Current Orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        let searchTimeout;

        // Real-time search while typing (debounced)
        document.getElementById("searchInput").addEventListener("input", function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(filterOrders, 300);

            // Show all when input is empty
            if (!this.value.trim()) {
                clearTimeout(searchTimeout);
                showAllOrders();
            }
        });

        // Enter key support
        document.getElementById("searchInput").addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                clearTimeout(searchTimeout);
                filterOrders();
            }
        });

        function filterOrders() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toLowerCase().trim();
            let table = document.getElementById("ordersTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let tds = tr[i].getElementsByTagName("td");
                let found = false;

                // Skip/hide dropbar rows during search
                if (tds.length === 1 && tds[0].colSpan === 8) {
                    tr[i].style.display = "none";
                    continue;
                }

                for (let j = 0; j < tds.length; j++) {
                    let txtValue = tds[j].textContent || tds[j].innerText || tds[j].innerHTML;
                    if (txtValue.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        }

        function showAllOrders() {
            let table = document.getElementById("ordersTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                tr[i].style.display = "";
            }
        }

        function toggleDropbar(rowIndex) {
            const dropbar = document.getElementById('dropbar-' + rowIndex);
            if (!dropbar) return;

            const isVisible = dropbar.classList.contains('show');
            document.querySelectorAll('.dropbar-row.show').forEach(row => row.classList.remove('show'));
            if (!isVisible) dropbar.classList.add('show');
        }
    </script>

</body>

</html>