<?php
session_start();
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Plant Manager only access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'master') {
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

// Date filters: default to current month (1st to today)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$trend_view = isset($_GET['trend_view']) ? $_GET['trend_view'] : 'monthly';

// Clear filter
if (isset($_GET['clear'])) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-d');
    header("Location: sales_analytics.php?start_date=" . $start_date . "&end_date=" . $end_date);
    exit();
}

// --------------------------------------------------------------
// 1. Customer purchase summary
// --------------------------------------------------------------
$customer_sql = "SELECT s.customer_name, 
                        SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                        COUNT(*) as order_count
                 FROM statement_of_account s
                 LEFT JOIN scd_vb_list v 
                     ON s.concrete_psi = v.vb_concrete_psi 
                     AND s.curation_days = v.vb_curation_days 
                     AND s.concrete_type = v.vb_concrete_type
                 WHERE s.order_status = 'Done'
                   AND s.order_date BETWEEN ? AND ?
                 GROUP BY s.customer_name
                 ORDER BY s.customer_name ASC";
$stmt_cust = $conn->prepare($customer_sql);
$stmt_cust->bind_param("ss", $start_date, $end_date);
$stmt_cust->execute();
$customer_result = $stmt_cust->get_result();
$all_customers = [];
while ($row = $customer_result->fetch_assoc()) {
    $all_customers[] = $row;
}

// --------------------------------------------------------------
// 2. Top customers (top 10 by total weight)
// --------------------------------------------------------------
$top_sql = "SELECT s.customer_name, 
                   SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                   COUNT(*) as order_count
            FROM statement_of_account s
            LEFT JOIN scd_vb_list v 
                ON s.concrete_psi = v.vb_concrete_psi 
                AND s.curation_days = v.vb_curation_days 
                AND s.concrete_type = v.vb_concrete_type
            WHERE s.order_status = 'Done' AND s.order_date BETWEEN ? AND ?
            GROUP BY s.customer_name
            ORDER BY total_weight DESC
            LIMIT 10";
$stmt_top = $conn->prepare($top_sql);
$stmt_top->bind_param("ss", $start_date, $end_date);
$stmt_top->execute();
$top_result = $stmt_top->get_result();
$top_customers = [];
while ($row = $top_result->fetch_assoc()) {
    $top_customers[] = $row;
}

// --------------------------------------------------------------
// 3. Sales trend (monthly or annual)
// --------------------------------------------------------------
if ($trend_view == 'monthly') {
    $trend_sql = "SELECT DATE_FORMAT(s.order_date, '%M %Y') as period,
                         s.customer_name,
                         SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                         COUNT(*) as order_count
                  FROM statement_of_account s
                  LEFT JOIN scd_vb_list v 
                      ON s.concrete_psi = v.vb_concrete_psi 
                      AND s.curation_days = v.vb_curation_days 
                      AND s.concrete_type = v.vb_concrete_type
                  WHERE s.order_status = 'Done'
                    AND s.order_date BETWEEN ? AND ?
                  GROUP BY period, s.customer_name
                  ORDER BY s.order_date DESC, total_weight DESC";
} else {
    $trend_sql = "SELECT DATE_FORMAT(s.order_date, '%Y') as period,
                         s.customer_name,
                         SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                         COUNT(*) as order_count
                  FROM statement_of_account s
                  LEFT JOIN scd_vb_list v 
                      ON s.concrete_psi = v.vb_concrete_psi 
                      AND s.curation_days = v.vb_curation_days 
                      AND s.concrete_type = v.vb_concrete_type
                  WHERE s.order_status = 'Done'
                    AND s.order_date BETWEEN ? AND ?
                  GROUP BY period, s.customer_name
                  ORDER BY s.order_date DESC, total_weight DESC";
}
$stmt_trend = $conn->prepare($trend_sql);
$stmt_trend->bind_param("ss", $start_date, $end_date);
$stmt_trend->execute();
$trend_result = $stmt_trend->get_result();
$trend_data = [];
while ($row = $trend_result->fetch_assoc()) {
    $trend_data[] = $row;
}

// --------------------------------------------------------------
// 4. Designs never ordered (all time)
// --------------------------------------------------------------
$never_ordered_sql = "SELECT vb_concrete_psi as design_mix, vb_curation_days as curation_days, vb_concrete_type as concrete_type
                      FROM scd_vb_list
                      WHERE CONCAT(vb_concrete_psi, vb_curation_days, vb_concrete_type) NOT IN (
                          SELECT CONCAT(concrete_psi, curation_days, concrete_type)
                          FROM statement_of_account
                          WHERE order_status = 'Done'
                      )
                      ORDER BY vb_concrete_psi ASC, vb_curation_days ASC";
$never_result = $conn->query($never_ordered_sql);
$never_designs = [];
while ($row = $never_result->fetch_assoc()) {
    $never_designs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Analytics - SORCOM</title>
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        /* All existing CSS (unchanged) */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: white;
            font-family: 'Poppins', 'Nunito', Arial, sans-serif;
            padding: 40px 20px;
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2e3590;
        }

        .report-header h1 {
            color: #2e3590;
            font-family: 'Lexend Giga', sans-serif;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .report-header h2 {
            color: #333;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: normal;
            margin-bottom: 5px;
        }

        .report-header .report-date {
            color: #666;
            font-size: 12px;
            margin-top: 10px;
        }

        .filter-bar {
            background: #f5f5f5;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form label {
            font-weight: 600;
            color: #333;
        }

        .filter-form input,
        .filter-form select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-filter,
        .btn-clear,
        .btn-print,
        .btn-pdf {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-filter {
            background: #2e3590;
            color: white;
        }

        .btn-clear {
            background: #6c757d;
            color: white;
        }

        .btn-print {
            background: #28a745;
            color: white;
        }

        .btn-pdf {
            background: #dc3545;
            color: white;
            margin-left: 10px;
        }

        .btn-filter:hover,
        .btn-clear:hover,
        .btn-print:hover,
        .btn-pdf:hover {
            opacity: 0.85;
        }

        .btn-print-section,
        .btn-pdf-section {
            background: #2e3590;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }

        .btn-print-section {
            background: #28a745;
        }

        .btn-pdf-section {
            background: #dc3545;
        }

        .btn-print-section:hover,
        .btn-pdf-section:hover {
            opacity: 0.85;
        }

        .section-title {
            background: #2e3590;
            color: white;
            padding: 12px 15px;
            margin: 25px 0 15px 0;
            border-radius: 8px;
            font-family: 'Lexend Giga', sans-serif;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .section-buttons {
            display: flex;
            gap: 10px;
        }

        .section-buttons button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            color: white;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.2s;
        }

        .section-buttons button:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }

        .analytics-table th {
            background: #2e3590;
            color: white;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #1a1f5e;
            -webkit-print-color-adjust: exact;
        }

        .analytics-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .analytics-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        @media print {

            /* Hide filter bar */
            .filter-bar {
                display: none !important;
            }

            /* Each section starts on a new page */
            .section-title {
                page-break-before: always;
                break-before: page;
            }

            /* First section should not have extra page break */
            .section-title:first-of-type {
                page-break-before: avoid;
                break-before: auto;
            }

            /* Keep section title background & white text */
            .section-title {
                background: #2e3590 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="report-container">
        <div class="report-header">
            <h1>SORCOM READY-MIX CONCRETE</h1>
            <h2>Sales Analytics Report</h2>
            <div class="report-date">
                Period: <?php echo date('F d, Y', strtotime($start_date)); ?> -
                <?php echo date('F d, Y', strtotime($end_date)); ?>
                <br>Generated: <?php echo date('F d, Y h:i A'); ?>
            </div>
        </div>

        <div class="filter-bar no-print" style="display: flex; justify-content: space-between; align-items: center;">
            <form method="GET" class="filter-form"
                style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <label>From:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                <label>To:</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                <label>Trend view:</label>
                <select name="trend_view">
                    <option value="monthly" <?php echo $trend_view == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="annual" <?php echo $trend_view == 'annual' ? 'selected' : ''; ?>>Annual</option>
                </select>
                <button type="submit" class="btn-filter">Filter</button>
                <a href="?clear=1" class="btn-clear">Clear Filter</a>
            </form>
            <div style="margin-left: auto;">
                <button id="sr-print" onclick="window.print()" class="btn-print-section"><i class="fas fa-print"></i>
                    Print</button>
                <button id="sr-export" onclick="exportPDF()" class="btn-pdf-section"><i class="fas fa-file-pdf"></i>
                    PDF</button>
            </div>
        </div>

        <!-- Table 1: Customer Purchase Summary -->
        <div class="section-title">
            <span><i class="fas fa-users"></i> Customer Purchase Summary</span>
            <!-- All print/export buttons removed -->
        </div>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Total Weight (kgs/m³)</th>
                    <th>Number of Orders</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($all_customers) > 0): ?>
                    <?php foreach ($all_customers as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['customer_name']) ?></td>
                            <td><?= number_format($c['total_weight'], 2) ?></td>
                            <td><?= $c['order_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No completed orders in the selected period.快
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <br>

        <!-- Table 2: Top Customers -->
        <div class="section-title">
            <span><i class="fas fa-trophy"></i> Top 10 Customers (by total weight)</span>
        </div>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Customer Name</th>
                    <th>Total Units (kgs/m³)</th>
                    <th>Number of Orders</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($top_customers) > 0): ?>
                    <?php $rank = 1;
                    foreach ($top_customers as $t): ?>
                        <tr>
                            <td><?= $rank++ ?></td>
                            <td><?= htmlspecialchars($t['customer_name']) ?></td>
                            <td><?= number_format($t['total_weight'], 2) ?></td>
                            <td><?= $t['order_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No data available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <br>

        <!-- Table 3: Sales Trend Over Time -->
        <div class="section-title">
            <span><i class="fas fa-chart-line"></i> Sales Trend Over Time (<?= ucfirst($trend_view) ?>)</span>
        </div>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Customer Name</th>
                    <th>Total Weight (kgs)</th>
                    <th>Number of Orders</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trend_data) > 0): ?>
                    <?php foreach ($trend_data as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['period']) ?></td>
                            <td><?= htmlspecialchars($t['customer_name']) ?></td>
                            <td><?= number_format($t['total_weight'], 2) ?></td>
                            <td><?= $t['order_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No sales data for selected period.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <br>

        <!-- Table 4: Designs Never Ordered -->
        <div class="section-title">
            <span><i class="fas fa-ban"></i> Designs Never Ordered (All Time)</span>
        </div>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Design Mix</th>
                    <th>Curation Days</th>
                    <th>Concrete Type</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($never_designs) > 0): ?>
                    <?php foreach ($never_designs as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['design_mix']) ?></td>
                            <td><?= htmlspecialchars($d['curation_days']) ?></td>
                            <td><?= htmlspecialchars($d['concrete_type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">All designs have been ordered at least once.快
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <br>
        <br>

        <div class="footer">This report is system-generated. For inquiries, please contact the System Administrator.
        </div>
    </div>

    <?php include 'sa_PrintExport.php'; ?>
    <?php $conn->close(); ?>
</body>

</html>