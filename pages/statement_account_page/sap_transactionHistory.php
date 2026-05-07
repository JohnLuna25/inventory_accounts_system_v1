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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
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

            // Real-time search while typing
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

        // ✅ FULL exportPDF using jsPDF autoTable (multi-page support)
        async function exportPDF() {
            const btn = document.querySelector('.btn-pdf');
            const originalBtnHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
            btn.disabled = true;

            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                // ✅ Header - Company name
                pdf.setFont("helvetica", "bold");
                pdf.setFontSize(14);
                pdf.setTextColor(44, 62, 80);
                pdf.text("SORCOM READY-MIX CONCRETE", 105, 15, { align: "center" });

                // ✅ Header - Document title
                pdf.setFont("helvetica", "bold");
                pdf.setFontSize(12);
                pdf.setTextColor(46, 53, 144);
                pdf.text("Transaction Record", 105, 22, { align: "center" });

                // ✅ Header - Generated date
                pdf.setFont("helvetica", "normal");
                pdf.setFontSize(8);
                pdf.setTextColor(100, 100, 100);
                pdf.text(`Generated: ${new Date().toLocaleString()}`, 105, 28, { align: "center" });

                // ✅ Divider line
                pdf.setDrawColor(46, 53, 144);
                pdf.setLineWidth(0.5);
                pdf.line(15, 31, 195, 31);

                // ✅ Extract table headers
                const table = document.getElementById('ordersTable');
                const headers = [...table.querySelectorAll('thead th')].map(th => th.innerText.trim());

                // ✅ Extract table rows (handle month separators)
                const body = [];
                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    if (row.classList.contains('month-separator')) {
                        const label = row.querySelector('td').innerText.trim();
                        body.push([{
                            content: label,
                            colSpan: headers.length,
                            styles: {
                                halign: 'center',
                                fillColor: [46, 53, 144],
                                textColor: [255, 255, 255],
                                fontStyle: 'bold',
                                fontSize: 8
                            }
                        }]);
                    } else {
                        const cells = [...row.querySelectorAll('td')].map(td => td.innerText.trim());
                        if (cells.length > 0) body.push(cells);
                    }
                });

                // ✅ autoTable - flows across pages automatically
                pdf.autoTable({
                    head: [headers],
                    body: body,
                    startY: 35,
                    margin: { left: 7, right: 0 },
                    tableWidth: 'auto',
                    styles: {
                        fontSize: 7,
                        cellPadding: 2.5,
                        overflow: 'linebreak',
                        lineWidth: 0.3,
                        lineColor: [180, 180, 180],
                    },
                    headStyles: {
                        fillColor: [46, 53, 144],
                        textColor: 255,
                        fontStyle: 'bold',
                        halign: 'center',
                        fontSize: 7,
                        lineWidth: 0.3,
                        lineColor: [255, 255, 255],
                    },
                    columnStyles: {
                        0: { cellWidth: 22 },  // Order ID
                        1: { cellWidth: 20 },  // Order Date
                        2: { cellWidth: 25 },  // Customer
                        3: { cellWidth: 45 },  // Address
                        4: { cellWidth: 15 },  // PSI
                        5: { cellWidth: 12 },  // Days
                        6: { cellWidth: 18 },  // Type
                        7: { cellWidth: 10 },  // m³
                        8: { cellWidth: 20 },  // Delivery
                        9: { cellWidth: 14 },  // Status
                    },
                    alternateRowStyles: {
                        fillColor: [245, 245, 255]
                    },
                    didDrawPage: function (data) {
                        const pageCount = pdf.internal.getNumberOfPages();
                        const currentPage = pdf.internal.getCurrentPageInfo().pageNumber;
                        pdf.setFontSize(8);
                        pdf.setTextColor(100);
                        pdf.text(
                            `Page ${currentPage} of ${pageCount}`,
                            105,
                            pdf.internal.pageSize.height - 8,
                            { align: 'center' }
                        );
                    }
                });

                pdf.save(`transaction_history_${new Date().toISOString().slice(0, 10)}.pdf`);

            } catch (error) {
                console.error('PDF Error:', error);
                alert('Failed to generate PDF: ' + error.message);
            } finally {
                btn.innerHTML = originalBtnHTML;
                btn.disabled = false;
            }
        }
    </script>

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</body>

</html>