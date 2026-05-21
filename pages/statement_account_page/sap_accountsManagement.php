<?php
ob_start();
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

function checkRemainingInventory($conn, $psi, $days, $type)
{
    // Get total stock from inventory
    $stmt = $conn->prepare("
        SELECT SUM(stocks_in) - SUM(stocks_out) as total_stock 
        FROM sorcom_readymix_inventory 
        WHERE design_mix_id = ? AND curation_days = ? AND concrete_type = ?
    ");
    $stmt->bind_param("sss", $psi, $days, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $total_stock = $row ? floatval($row['total_stock']) : 0;

    // Get total ordered units from ALL orders (not just current)
    $stmt2 = $conn->prepare("
        SELECT SUM(sap_units) as total_ordered 
        FROM statement_of_account 
        WHERE concrete_psi = ? AND curation_days = ? AND concrete_type = ?
        AND order_status != 'Done'
    ");
    $stmt2->bind_param("sss", $psi, $days, $type);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $stmt2->close();

    $total_ordered = $row2 ? floatval($row2['total_ordered']) : 0;

    $remaining = $total_stock - $total_ordered;
    return $remaining > 0 ? $remaining : 0;
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
    <title>Concrete Orders Management</title>
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
            <h2 style="font-family: 'Poppins', sans-serif;">Concrete Orders</h2>
            <div class="button-container">

                <!-- LEFT SIDE -->
                <div class="left-buttons">
                    <a href="sap_accountsForm.php" class="btn btn-primary">+ Add Order</a>
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
                                    <span class="<?= $statusClass ?>"><?= htmlspecialchars($row['order_status']) ?></span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-view"
                                        onclick="toggleDropbar(<?= $row_index ?>); return false;">View</a>
                                    <a href="sap_accountsForm.php?id=<?= urlencode($row['sap_id']) ?>"
                                        class="btn btn-edit">Edit</a>
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
                                            <td class="dropbar-detail"><?= htmlspecialchars($row['concrete_type']) ?></td>
                                            <td class="dropbar-detail">
                                                <?= htmlspecialchars($row['sap_units']) ?> units<br>
                                                <small style="color:#666;">
                                                    <?php
                                                    $inv_check = checkRemainingInventory($conn, $row['concrete_psi'], $row['curation_days'], $row['concrete_type']);
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

        function filterData() {
            const btn = document.getElementById('filterBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            const start_date = document.querySelector('input[name="start_date"]').value;
            const end_date = document.querySelector('input[name="end_date"]').value;
            const trend_view = document.querySelector('select[name="trend_view"]').value;

            window.location.href = `?start_date=${start_date}&end_date=${end_date}&trend_view=${trend_view}`;
        }

    </script>

</body>

</html>