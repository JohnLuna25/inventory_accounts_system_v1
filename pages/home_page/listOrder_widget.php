<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error) {
    echo '<div class="widget-error">Database connection failed</div>';
    return;
}

// Fetch concrete designs
$designs_query = "SELECT vb_concrete_psi, vb_curation_days, vb_concrete_type, vb_total FROM scd_vb_list ORDER BY vb_concrete_psi ASC";
$designs_result = $conn->query($designs_query);

// Add Order URL
$add_order_url = "/Inventory-Accounts-System-v1/pages/statement_account_page/sap_accountsForm.php";
?>

<style>
    /* ===== WIDGET CONTAINER ===== */
    .widget-container {
        width: 45vw !important;
        margin-left: -55px;
        margin-top: -20px;
        padding: 0;
    }

    /* Widget Sidebar */
    .widget-sidebar {
        width: 100%;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Widget header */
    .widget-header {
        background: #2e3590;
        color: white;
        padding: 15px 20px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
    }

    .widget-header i {
        margin-right: 10px;
    }

    /* Widget search bar */
    .widget-search {
        display: flex;
        gap: 10px;
        padding: 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        flex-wrap: wrap;
    }

    .widget-search input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 25px;
        font-size: 14px;
        min-width: 150px;
        outline: none;
    }

    .widget-search input:focus {
        border-color: #2e3590;
    }

    .widget-search .btn-add {
        padding: 10px 20px;
        background: #28a745;
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-weight: bold;
        transition: background 0.2s;
    }

    .widget-search .btn-add:hover {
        background: #218838;
    }

    /* Widget table */
    .widget-table {
        overflow-x: auto;
        max-height: 280px;
        overflow-y: auto;
    }

    .widget-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .widget-table th {
        background: #0d6efd;
        color: white;
        padding: 12px;
        text-align: left;
        font-size: 14px;
        position: sticky;
        top: 0;
        font-family: 'Nunito', sans-serif;
        font-weight: 600;
    }

    .widget-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        color: #333;
        font-family: 'Inter', sans-serif;
    }

    .widget-table tr:hover {
        background: #f5f5f5;
    }

    /* No data message */
    .widget-table .no-data {
        text-align: center;
        padding: 30px;
        color: #999;
    }

    /* Error message */
    .widget-error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .widget-container {
            width: 100%;
            margin-left: 0;
        }
    }
</style>

<div class="widget-container">
    <div class="widget-sidebar">
        <div class="widget-header"
            onclick="window.location.href='/Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php'"
            style="cursor: pointer;">
            <i class="fas fa-cube"></i> Concrete Design List
        </div>

        <div class="widget-search">
            <input type="text" id="widgetSearch" placeholder="Search concrete design..." onkeyup="filterWidgetTable()">
            <button class="btn-add" onclick="window.location.href='<?php echo $add_order_url; ?>'">
                <i class="fas fa-plus"></i> Add Order
            </button>
        </div>

        <div class="widget-table">
            <table id="widgetTable">
                <thead>
                    <tr>
                        <th>Design Mix</th>
                        <th>Curation Days</th>
                        <th>Type</th>
                        <th>Batch (kgs/m³)</th>
                        </td>
                </thead>
                <tbody>
                    <?php if ($designs_result && $designs_result->num_rows > 0): ?>
                        <?php while ($design = $designs_result->fetch_assoc()):
                            $batch_value = $design['vb_total'];
                            if (is_string($batch_value)) {
                                preg_match('/[\d.]+/', $batch_value, $matches);
                                $batch_value = isset($matches[0]) ? floatval($matches[0]) : 0;
                            } else {
                                $batch_value = floatval($batch_value);
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($design['vb_concrete_psi']); ?></td>
                                <td><?php echo htmlspecialchars($design['vb_curation_days']); ?> days

                                <td><?php echo htmlspecialchars($design['vb_concrete_type']); ?></td>
                                <td><?php echo number_format($batch_value, 1); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">No concrete designs found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filterWidgetTable() {
        const input = document.getElementById('widgetSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('widgetTable');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let found = false;
            for (let j = 0; j < cells.length; j++) {
                if (cells[j] && cells[j].innerText.toLowerCase().includes(filter)) {
                    found = true;
                    break;
                }
            }
            rows[i].style.display = found ? '' : 'none';
        }
    }
</script>

<?php $conn->close(); ?>