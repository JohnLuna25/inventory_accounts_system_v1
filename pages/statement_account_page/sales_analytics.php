<?php
session_start();
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'master') {
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

$trend_view = isset($_GET['trend_view']) ? $_GET['trend_view'] : 'daily';

// Clear filter
if (isset($_GET['clear'])) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-d');
    header("Location: sales_analytics.php?start_date=$start_date&end_date=$end_date&trend_view=daily");
    exit();
}

// --------------------------------------------------------------
// Build $start_date and $end_date based on trend_view
// --------------------------------------------------------------
if ($trend_view === 'daily') {
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

} elseif ($trend_view === 'monthly') {
    $month = isset($_GET['month_of']) ? (int) $_GET['month_of'] : (int) date('n');
    $year = isset($_GET['from_year_monthly']) ? (int) $_GET['from_year_monthly'] : (int) date('Y');
    $start_date = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
    $end_date = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year)); // last day of that month

} elseif ($trend_view === 'annual') {
    $from_year = isset($_GET['from_year']) ? (int) $_GET['from_year'] : (int) date('Y');
    $to_year = isset($_GET['to_year']) ? (int) $_GET['to_year'] : (int) date('Y');

    // Safety: never allow to_year < from_year
    if ($to_year < $from_year) {
        $to_year = $from_year;
    }

    $start_date = "$from_year-01-01";
    $end_date = "$to_year-12-31";
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
            WHERE s.order_status = 'Done'
              AND s.order_date BETWEEN ? AND ?
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
// 3. Sales trend (daily, monthly, or annual)
// --------------------------------------------------------------
if ($trend_view === 'daily') {
    $trend_sql = "SELECT DATE_FORMAT(s.order_date, '%M %d, %Y') as period,
                         s.customer_name,
                         s.concrete_psi,
                         s.curation_days,
                         s.concrete_type,
                         SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                         SUM(s.sap_units) as total_units,
                         SUM(s.sap_concrete_price) as total_sales,
                         COUNT(*) as order_count
                  FROM statement_of_account s
                  LEFT JOIN scd_vb_list v 
                      ON s.concrete_psi = v.vb_concrete_psi 
                      AND s.curation_days = v.vb_curation_days 
                      AND s.concrete_type = v.vb_concrete_type
                  WHERE s.order_status = 'Done'
                    AND s.order_date BETWEEN ? AND ?
                  GROUP BY s.order_date, s.customer_name, s.concrete_psi, s.curation_days, s.concrete_type
                  ORDER BY s.order_date ASC, total_weight DESC";

} elseif ($trend_view === 'monthly') {
    $trend_sql = "SELECT DATE_FORMAT(s.order_date, '%d, %Y') as period,
                         s.customer_name,
                         s.concrete_psi,
                         s.curation_days,
                         s.concrete_type,
                         SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                         SUM(s.sap_units) as total_units,
                         SUM(s.sap_concrete_price) as total_sales,
                         COUNT(*) as order_count
                  FROM statement_of_account s
                  LEFT JOIN scd_vb_list v 
                      ON s.concrete_psi = v.vb_concrete_psi 
                      AND s.curation_days = v.vb_curation_days 
                      AND s.concrete_type = v.vb_concrete_type
                  WHERE s.order_status = 'Done'
                    AND s.order_date BETWEEN ? AND ?
                  GROUP BY DATE_FORMAT(s.order_date, '%Y-%m'), s.customer_name, s.concrete_psi, s.curation_days, s.concrete_type
                  ORDER BY s.order_date ASC, total_weight DESC";

} else {
    // annual
    $trend_sql = "SELECT DATE_FORMAT(s.order_date, '%Y') as period,
                         s.customer_name,
                         s.concrete_psi,
                         s.curation_days,
                         s.concrete_type,
                         SUM(s.sap_units * COALESCE(v.vb_total, 0)) as total_weight,
                         SUM(s.sap_units) as total_units,
                         SUM(s.sap_concrete_price) as total_sales,
                         COUNT(*) as order_count
                  FROM statement_of_account s
                  LEFT JOIN scd_vb_list v 
                      ON s.concrete_psi = v.vb_concrete_psi 
                      AND s.curation_days = v.vb_curation_days 
                      AND s.concrete_type = v.vb_concrete_type
                  WHERE s.order_status = 'Done'
                    AND s.order_date BETWEEN ? AND ?
                  GROUP BY DATE_FORMAT(s.order_date, '%Y'), s.customer_name, s.concrete_psi, s.curation_days, s.concrete_type
                  ORDER BY s.order_date ASC, total_weight DESC";
}
$stmt_trend = $conn->prepare($trend_sql);
$stmt_trend->bind_param("ss", $start_date, $end_date);
$stmt_trend->execute();
$trend_result = $stmt_trend->get_result();
$trend_data = [];
while ($row = $trend_result->fetch_assoc()) {
    $trend_data[] = $row;
}

function getConcreteTypeBadge($type)
{
    $type_upper = strtoupper(trim($type));
    if (str_contains($type_upper, 'ORDINARY')) {
        $color = '#343434';
        $bg = '#ffde3b';
        $border = '#000000';
    } elseif (str_contains($type_upper, 'PUMPCRETE')) {
        $color = '#ffffff';
        $bg = '#00ad59';
        $border = '#000000';
    } elseif (str_contains($type_upper, 'TREMIE')) {
        $color = '#ffffff';
        $bg = '#c62828';
        $border = '#8e0000';
    } else {
        $color = '#555';
        $bg = '#f0f0f0';
        $border = '#000000';
    }
    return "<span style='
        background-color: {$bg};
        color: {$color};
        border: 1px solid {$border};
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        white-space: nowrap;
    '>" . htmlspecialchars($type) . "</span>";
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
        /* ========== RESET & BASE ========== */
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
            font-size: 15px;
            margin-top: 10px;
            border: rgba(51, 51, 51, 0.2) solid;
            border-radius: 10px;
            padding: 5px 8px;
            display: inline-block;
        }

        /* ========== FILTER BAR CARD (same as Concrete Orders) ========== */
        .page-title-bar {
            background: white !important;
            margin: 15px 10px !important;
            padding: 20px !important;
            border-radius: 15px !important;
            box-shadow: 0 2px 4px !important;
        }

        .page-title-bar h2 {
            text-align: center !important;
            margin-top: 0 !important;
            margin-bottom: 15px !important;
            color: #333 !important;
            font-weight: 300 !important;
        }

        /* Unify all button sizes */
        .btn,
        .btn-primary,
        .btn-print-section,
        .btn-pdf-section,
        .button-container .btn {
            padding: 8px 20px !important;
            font-size: 14px !important;
            line-height: 1.2 !important;
            min-width: 100px !important;
            /* optional: makes them equal width */
            text-align: center !important;
        }

        /* ========== BUTTON CONTAINER – two rows stacked ========== */


        .btn-sm {
            padding: 8px 12px !important;
            font-size: 13px !important;
        }

        /* Make Back button gray */
        .btn-secondary {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: white !important;
        }

        .btn-secondary:hover {
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
        }

        /* Print button - Green */
        #sr-print {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
        }

        #sr-print:hover {
            background-color: #218838 !important;
            border-color: #1e7e34 !important;
        }

        /* PDF button - Red */
        #sr-export {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }

        #sr-export:hover {
            background-color: #c82333 !important;
            border-color: #bd2130 !important;
        }

        /* Filter button stays blue (already btn-primary) */
        /* Clear Filter button stays gray (btn-secondary) */
        /* Back button stays gray (btn-secondary) */

        /* ========== FORM ELEMENTS & BUTTONS ========== */
        label,
        select,
        input,
        button {
            font-family: inherit;
        }

        input,
        select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            background-color: #2e3590;
            color: white;
        }

        .btn-primary {
            background-color: #2e3590 !important;
            color: white !important;
        }

        .btn-primary:hover {
            background-color: #0a4a8f !important;
        }

        /* Specific buttons (if needed) */
        .btn-print-section,
        .btn-pdf-section {
            background: #2e3590;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        /* ========== SECTION TITLES & TABLES (unchanged) ========== */
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

        /* ========== PRINT STYLES ========== */
        @media print {

            .filter-bar,
            .button-container,
            .btn,
            .btn-primary,
            .right-buttons {
                display: none !important;
            }

            .section-title {
                page-break-before: always;
                break-before: page;
                background: #2e3590 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .section-title:first-of-type {
                page-break-before: avoid;
            }

            .analytics-table th {
                background: #2e3590 !important;
                color: white !important;
            }
        }
    </style>
</head>

<body>

    <div class="report-container">
        <div class="report-header">
            <h1>SORCOM READY-MIX CONCRETE</h1>
            <h2>Transaction Summary Report</h2>
            <div class="report-date">
                Period:
                <?php echo date('F d, Y', strtotime($start_date)); ?> -
                <?php echo date('F d, Y', strtotime($end_date)); ?>
                <br>Generated:
                <?php echo date('F d, Y h:i A'); ?>
            </div>
        </div>

        <div class="page-title-bar">
            <!-- ROW 1 -->
            <div class="row align-items-center g-3 mb-3">
                <div class="col-md-3">
                    <a href="#" onclick="history.back()" class="btn btn-secondary btn-sm w-100 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <label class="form-label small fw-bold mb-0 me-2 text-primary" id="label-from"
                            style="min-width: 85px;">From:</label>

                        <!-- Daily -->
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm"
                            value="<?php echo $start_date; ?>">

                        <!-- Monthly: Month picker -->
                        <select name="month_of" id="month_of" class="form-select form-select-sm d-none">
                            <?php
                            $selected_month = isset($_GET['month_of']) ? (int) $_GET['month_of'] : (int) date('n');
                            for ($m = 1; $m <= 12; $m++):
                                ?>
                                <option value="<?= $m ?>" <?= $selected_month === $m ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <!-- Annual: From Year -->
                        <select name="from_year" id="from_year" class="form-select form-select-sm d-none">
                            <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                                <option value="<?= $y ?>" <?= (isset($_GET['from_year']) && $_GET['from_year'] == $y) ? 'selected' : ($y == date('Y') ? 'selected' : '') ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button id="filterBtn" class="btn btn-primary btn-sm w-100 fw-bold" onclick="filterData()">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-3">
                    <button id="sr-print" onclick="window.print()" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>

            <!-- ROW 2 -->
            <div class="row align-items-center g-3">
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <label class="form-label small fw-bold mb-0 me-2 text-primary" style="min-width: 85px;">Trend
                            view:</label>
                        <select name="trend_view" id="trend_view" class="form-select form-select-sm"
                            onchange="updateFields()">
                            <option value="daily" <?php echo $trend_view == 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="monthly" <?php echo $trend_view == 'monthly' ? 'selected' : ''; ?>>Monthly
                            </option>
                            <option value="annual" <?php echo $trend_view == 'annual' ? 'selected' : ''; ?>>Annual
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <label class="form-label small fw-bold mb-0 me-2 text-primary" id="label-to"
                            style="min-width: 85px;">To:</label>

                        <!-- Daily -->
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm"
                            value="<?php echo $end_date; ?>">

                        <!-- Monthly: From Year -->
                        <select name="from_year_monthly" id="from_year_monthly"
                            class="form-select form-select-sm d-none">
                            <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                                <option value="<?= $y ?>" <?= (isset($_GET['from_year_monthly']) && $_GET['from_year_monthly'] == $y) ? 'selected' : ($y == date('Y') ? 'selected' : '') ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <!-- Annual: To Year -->
                        <select name="to_year" id="to_year" class="form-select form-select-sm d-none"
                            onchange="validateYearRange()">
                            <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                                <option value="<?= $y ?>" <?= (isset($_GET['to_year']) && $_GET['to_year'] == $y) ? 'selected' : ($y == date('Y') ? 'selected' : '') ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="?clear=1" class="btn btn-secondary btn-sm w-100 fw-bold">
                        <i class="fas fa-eraser me-1"></i> Clear Filter
                    </a>
                </div>
                <div class="col-md-3">
                    <button id="sr-export" onclick="exportPDF()" class="btn btn-danger btn-sm w-100 fw-bold">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </button>
                </div>
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
                    <th>Customer Name (A-Z)</th>
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
                            <td><?= $rank++ ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($t['customer_name']) ?>
                            </td>
                            <td>
                                <?= number_format($t['total_weight'], 2) ?>
                            </td>
                            <td><?= $t['order_count'] ?>
                            </td>
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
            <span><i class="fas fa-chart-line"></i> Sales Trend Over Time (
                <?= ucfirst($trend_view) ?> )
            </span>
        </div>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>
                        Period
                        <?php if ($trend_view === 'monthly'): ?>
                            <br><small style="font-weight: 400; font-size: 12px;">(Day, Year)</small>
                        <?php endif; ?>
                    </th>
                    <th>Customer<br>Name</th>
                    <th>Concrete<br>Design</th>
                    <th>Curation<br>Days</th>
                    <th>Concrete Type</th>
                    <th>
                        Total<br>Weight
                        <br><small style="font-weight: 400; font-size: 12px;">(kgs)</small>
                    </th>
                    <th>Total<br>Units</th>
                    <th>Number of<br>Transactions</th>
                    <th>Sales (₱)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($trend_data) > 0): ?>
                    <?php
                    $grand_total_sales = 0;
                    foreach ($trend_data as $t):
                        $grand_total_sales += $t['total_sales'];
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($t['period']) ?></td>
                            <td><?= htmlspecialchars($t['customer_name']) ?></td>
                            <td><?= htmlspecialchars($t['concrete_psi']) ?> </td>
                            <td><?= htmlspecialchars($t['curation_days']) ?> Days</td>
                            <td><?= getConcreteTypeBadge($t['concrete_type']) ?></td>
                            <td><?= number_format($t['total_weight'], 2) ?></td>
                            <td><?= number_format($t['total_units'], 0) ?></td>
                            <td><?= $t['order_count'] ?></td>
                            <td>₱ <?= number_format($t['total_sales'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- TOTAL ROW -->
                    <tr style="background-color: #2e3590; color: white; font-weight: bold;">
                        <td colspan="2" style="text-align: center;">
                            <?= date('F d, Y', strtotime($start_date)) ?> — <?= date('F d, Y', strtotime($end_date)) ?>
                        </td>
                        <td colspan="4" style="text-align: center;">Total:</td>
                        <td colspan="3" style="text-align: center;">
                            ₱ <?= number_format($grand_total_sales, 2) ?>
                        </td>
                    </tr>

                <?php else: ?>
                    <tr>
                        <td colspan="7">No sales data for selected period.</td>
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
                            <td><?= htmlspecialchars($d['design_mix']) ?>
                            </td>
                            <td><?= htmlspecialchars($d['curation_days']) ?></td>
                            <td>
                                <?= htmlspecialchars($d['concrete_type']) ?>
                            </td>
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

    <script>
        function updateFields() {
            const trend = document.getElementById('trend_view').value;

            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const monthOf = document.getElementById('month_of');
            const fromYear = document.getElementById('from_year');
            const fromYearMonthly = document.getElementById('from_year_monthly');
            const toYear = document.getElementById('to_year');
            const labelFrom = document.getElementById('label-from');
            const labelTo = document.getElementById('label-to');

            // Hide & disable all dynamic fields
            [startDate, endDate, monthOf, fromYear, fromYearMonthly, toYear].forEach(el => {
                el.classList.add('d-none');
                el.disabled = true;
            });

            if (trend === 'daily') {
                labelFrom.textContent = 'From:';
                labelTo.textContent = 'To:';
                startDate.classList.remove('d-none'); startDate.disabled = false;
                endDate.classList.remove('d-none'); endDate.disabled = false;

            } else if (trend === 'monthly') {
                labelFrom.textContent = 'Month of:';
                labelTo.textContent = 'From year:';
                monthOf.classList.remove('d-none'); monthOf.disabled = false;
                fromYearMonthly.classList.remove('d-none'); fromYearMonthly.disabled = false;

            } else if (trend === 'annual') {
                labelFrom.textContent = 'From year:';
                labelTo.textContent = 'To year:';
                fromYear.classList.remove('d-none'); fromYear.disabled = false;
                toYear.classList.remove('d-none'); toYear.disabled = false;
                fromYear.onchange = validateYearRange;
                toYear.onchange = validateYearRange;
            }
        }

        function validateYearRange() {
            const fromVal = parseInt(document.getElementById('from_year').value);
            const toEl = document.getElementById('to_year');
            if (parseInt(toEl.value) < fromVal) {
                alert('To year cannot be earlier than From year.');
                toEl.value = fromVal;
            }
        }

        function filterData() {
            const btn = document.getElementById('filterBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            const trend = document.getElementById('trend_view').value;
            let params = `trend_view=${trend}`;

            if (trend === 'daily') {
                params += `&start_date=${document.getElementById('start_date').value}`;
                params += `&end_date=${document.getElementById('end_date').value}`;
            } else if (trend === 'monthly') {
                params += `&month_of=${document.getElementById('month_of').value}`;
                params += `&from_year_monthly=${document.getElementById('from_year_monthly').value}`;
            } else if (trend === 'annual') {
                params += `&from_year=${document.getElementById('from_year').value}`;
                params += `&to_year=${document.getElementById('to_year').value}`;
            }

            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-check"></i> Done!';
                setTimeout(() => {
                    window.location.href = `?${params}`;
                }, 500);
            }, 500);
        }

        // Run on load to match saved PHP trend_view
        document.addEventListener('DOMContentLoaded', updateFields);
    </script>

</body>

</html>