<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sorcom_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🔥 FIX UTF-8 ENCODING FOR SPECIAL CHARACTERS (ñ, é, etc.)
$conn->set_charset("utf8mb4");

// Helper function to format address correctly
function formatAddress($full_address)
{
    if (empty($full_address)) {
        return 'No address';
    }

    // Replace ' || ' with ', '
    $formatted = str_replace(' || ', ', ', $full_address);

    // Remove leading comma or space if any
    $formatted = ltrim($formatted, ', ');

    return $formatted;
}

// Fetch done orders (newest to oldest)
function getDoneOrders($conn)
{
    $sql = "SELECT 
                sap_id, 
                order_date, 
                delivery_date, 
                customer_name, 
                full_address, 
                concrete_psi, 
                curation_days, 
                concrete_type, 
                sap_units, 
                sap_concrete_price, 
                order_status 
            FROM statement_of_account 
            WHERE order_status = 'Done' 
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
    <?php include '../../assets/resources.php'; ?>
    <link rel="stylesheet" href="sap_accountsManagement.css">
    <link rel="stylesheet" href="sap_transactionHistory.css">

    <!-- PDF Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body style="background: #bfc3aa !important;">

    <?php showHeader(); ?>
    <div class="main-content">

        <div class="page-title-bar">
            <h2 style="font-family: 'Poppins', sans-serif;">Transaction History</h2>
            <div class="button-container">
                <a href="sap_accountsManagement.php" class="btn btn-primary btn-back">← Back to Orders</a>
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
                        <th>Order Date</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>PSI</th>
                        <th>Days</th>
                        <th>Type</th>
                        <th>m³</th>
                        <th>Delivery</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($doneOrders)): ?>

                        <?php
                        $currentMonth = "";

                        foreach ($doneOrders as $order):

                            $orderDate = $order['order_date'] ?? '';
                            $monthLabel = date("F Y", strtotime($orderDate));
                            $formatted_address = formatAddress($order['full_address'] ?? '');
                            ?>

                            <?php if ($monthLabel !== $currentMonth): ?>
                                <?php $currentMonth = $monthLabel; ?>
                                <tr class="month-separator">
                                    <td colspan="10"><?= strtoupper($monthLabel) ?></td>
                                </tr>
                            <?php endif; ?>

                            <tr>
                                <td><?= htmlspecialchars($order['sap_id'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['order_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($formatted_address, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['concrete_psi'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['curation_days'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(substr($order['concrete_type'] ?? '', 0, 12), ENT_QUOTES, 'UTF-8') ?><?= strlen($order['concrete_type'] ?? '') > 12 ? '...' : '' ?>
                                </td>
                                <td><?= htmlspecialchars($order['sap_units'] ?? '0', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['delivery_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['order_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="no-orders">No finished orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>

        <!-- Action Buttons: PDF Export & Print -->
        <div class="action-buttons">
            <button onclick="exportPDF()" class="btn-pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button onclick="window.print()" class="btn-print">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const input = document.getElementById("searchInput");

            // 🔥 REAL-TIME SEARCH while typing
            input.addEventListener("input", function () {
                filterOrders();
            });

            // Enter key also works
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

        // PDF Export Function - WITH UTF-8 FIX (FIXED event parameter)
        async function exportPDF(event) {
            if (typeof html2canvas === 'undefined') {
                alert('PDF library not loaded. Please refresh the page.');
                return;
            }
            if (typeof window.jspdf === 'undefined') {
                alert('PDF library not loaded. Please refresh the page.');
                return;
            }

            const btn = event ? event.currentTarget : document.querySelector('.btn-pdf');
            let originalBtnHTML = '';
            if (btn) {
                originalBtnHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
                btn.disabled = true;
            }

            try {
                const table = document.getElementById('ordersTable');
                const title = document.querySelector('.page-title-bar h2').innerText;

                const wrapper = document.createElement('div');
                wrapper.style.cssText = `
            position: absolute;
            top: -9999px;
            left: 0;
            width: 210mm;
            background: white;
            padding: 12px 15px;
            font-family: Arial, sans-serif;
            `;

                // FORCE UTF-8 ENCODING FOR SPECIAL CHARACTERS (ñ, é, etc.)
                const meta = document.createElement('meta');
                meta.setAttribute('charset', 'UTF-8');
                wrapper.appendChild(meta);

                const header = document.createElement('div');
                header.style.cssText = `
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #2e3590;
            `;
                header.innerHTML = `
            <h1 style="color: #2c3e50; margin: 0 0 3px 0; font-size: 16px;">SORCOM READY-MIX CONCRETE</h1>
            <h2 style="color: #2e3590; margin: 0; font-size: 14px;">${title}</h2>
            <p style="color: #666; margin: 5px 0 0 0; font-size: 9px;">Generated: ${new Date().toLocaleString()}</p>
            `;
                wrapper.appendChild(header);

                const tableClone = table.cloneNode(true);
                tableClone.style.width = '100%';
                tableClone.style.borderCollapse = 'collapse';
                tableClone.style.fontSize = '8px';
                tableClone.style.tableLayout = 'auto';

                // Style all cells
                const cells = tableClone.querySelectorAll('th, td');
                cells.forEach(cell => {
                    cell.style.border = '1px solid #000';
                    cell.style.padding = '4px 5px';
                    cell.style.textAlign = 'left';
                });

                // Style headers
                const headers = tableClone.querySelectorAll('th');
                headers.forEach(th => {
                    th.style.backgroundColor = '#2e3590';
                    th.style.color = 'white';
                    th.style.fontWeight = 'bold';
                    th.style.textAlign = 'center';
                    th.style.fontSize = '9px';
                });

                // Style month separators
                const separators = tableClone.querySelectorAll('.month-separator td');
                separators.forEach(td => {
                    td.style.backgroundColor = '#2e3590';
                    td.style.color = 'white';
                    td.style.fontWeight = 'bold';
                    td.style.textAlign = 'center';
                    td.style.fontSize = '9px';
                });

                wrapper.appendChild(tableClone);
                document.body.appendChild(wrapper);
                await new Promise(r => setTimeout(r, 200));

                const canvas = await html2canvas(wrapper, {
                    scale: 2.5,
                    backgroundColor: '#ffffff',
                    logging: false
                });

                document.body.removeChild(wrapper);

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const imgWidth = 180;
                const pageHeight = 277;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                // Center the image with equal margins
                const marginLeft = (210 - imgWidth) / 2;
                let position = 15;

                pdf.addImage(imgData, 'JPEG', marginLeft, position, imgWidth, imgHeight);

                pdf.save(`transaction_history_${new Date().toISOString().slice(0, 10)}.pdf`);

            } catch (error) {
                console.error('PDF Error:', error);
                alert('Failed to generate PDF: ' + error.message);
            } finally {
                if (btn) {
                    btn.innerHTML = originalBtnHTML;
                    btn.disabled = false;
                }
            }
        }
    </script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</body>

</html>