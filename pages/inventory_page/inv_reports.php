<?php
session_start();
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Plant Manager only access
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'master') {
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

// Get date filters - default to first day of current month to today
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Handle Clear Filter
if (isset($_GET['clear'])) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-d');
    header("Location: inv_reports.php?start_date=" . $start_date . "&end_date=" . $end_date);
    exit();
}

// Fetch Ready-Mix Inventory
$readymix_query = "SELECT date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, current_stock_batch, remarks 
                   FROM sorcom_readymix_inventory 
                   WHERE (recycled_data = 0 OR recycled_data IS NULL)
                   AND date_added BETWEEN ? AND ?
                   ORDER BY design_mix_id ASC, date_added DESC";
$stmt1 = $conn->prepare($readymix_query);
$stmt1->bind_param("ss", $start_date, $end_date);
$stmt1->execute();
$readymix_result = $stmt1->get_result();

// Fetch Mixture Inventory
$mixture_query = "SELECT date_added, cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air, others, total 
                  FROM sorcom_mixture_inventory 
                  WHERE (recycled_data = 0 OR recycled_data IS NULL)
                  AND date_added BETWEEN ? AND ?
                  ORDER BY date_added DESC";
$stmt2 = $conn->prepare($mixture_query);
$stmt2->bind_param("ss", $start_date, $end_date);
$stmt2->execute();
$mixture_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Summary Report - SORCOM</title>
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
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
            max-width: 1400px;
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

        .filter-form input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-filter,
        .btn-clear,
        .btn-print {
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
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-filter:hover,
        .btn-clear:hover,
        .btn-print:hover,
        .btn-pdf:hover,
        .btn-back:hover {
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
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }

        .inventory-table th {
            background: #2e3590;
            color: white;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #1a1f5e;
            font-weight: 600;
            white-space: nowrap;
        }

        .inventory-table td {
            border: 1px solid #ddd;
            padding: 10px 8px;
            text-align: center;
            vertical-align: middle;
        }

        .inventory-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-row {
            background: #e8e8e8;
            font-weight: bold;
        }

        .total-row td {
            border-top: 2px solid #2e3590;
        }

        .report-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #999;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .filter-bar,
            .btn-filter,
            .btn-clear,
            .btn-print,
            .btn-pdf {
                display: none;
            }

            .report-header {
                margin-bottom: 20px;
            }

            .inventory-table th {
                background: #2e3590;
                color: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 1.5cm;

                @bottom-center {
                    content: counter(page);
                    font-size: 10px;
                    color: #999;
                }
            }
        }
    </style>
</head>

<body>

    <div class="report-container">
        <div class="report-header">
            <h1>SORCOM READY-MIX CONCRETE</h1>
            <h2>Inventory Summary Report</h2>
            <div class="report-date">
                Period: <?php echo date('F d, Y', strtotime($start_date)); ?> -
                <?php echo date('F d, Y', strtotime($end_date)); ?>
                <br>Generated: <?php echo date('F d, Y h:i A'); ?>
            </div>
        </div>

        <div class="filter-bar no-print">
            <form method="GET" class="filter-form">
                <label>From:</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                <label>To:</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                <button type="submit" class="btn-filter">Filter</button>
                <a href="?clear=1" class="btn-clear">Clear Filter</a>
            </form>
            <div>
                <div>
                    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print</button>
                    <button onclick="exportPDF()" class="btn-pdf"><i class="fas fa-file-pdf"></i> Export PDF</button>
                    <a href="#" onclick="history.back()" class="btn-back" style="margin-left: 15px;">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>

        <!-- SECTION 1: READY-MIX INVENTORY -->
        <div class="section-title"><i class="fas fa-cube"></i> Ready-Mix Inventory</div>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Design Mix</th>
                    <th>Curation Days</th>
                    <th>Concrete Type</th>
                    <th>Batch (kgs/m³)</th>
                    <th>Stocks In</th>
                    <th>Stock Out</th>
                    <th>Current Stock (kgs/m³)</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($readymix_result && $readymix_result->num_rows > 0): ?>
                    <?php $total_stocks_in = 0;
                    $total_stocks_out = 0;
                    $total_current_stock = 0; ?>
                    <?php while ($row = $readymix_result->fetch_assoc()):
                        $total_stocks_in += intval($row['stocks_in']);
                        $total_stocks_out += intval($row['stocks_out']);
                        $total_current_stock += floatval($row['current_stock_batch']);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date_added']) ?></td>
                            <td><?= htmlspecialchars($row['design_mix_id']) ?></td>
                            <td><?= htmlspecialchars($row['curation_days']) ?></td>
                            <td><?= htmlspecialchars($row['concrete_type']) ?></td>
                            <td><?= number_format(floatval($row['batch']), 1) ?></td>
                            <td><?= intval($row['stocks_in']) ?></td>
                            <td><?= intval($row['stocks_out']) ?></td>
                            <td><?= number_format(floatval($row['current_stock_batch']), 1) ?></td>
                            <td><?= htmlspecialchars($row['remarks']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right;"><strong>TOTAL</strong></td>
                        <td><strong><?= $total_stocks_in ?></strong></td>
                        <td><strong><?= $total_stocks_out ?></strong></td>
                        <td><strong><?= number_format($total_current_stock, 1) ?> (kgs/m³)</strong></td>
                        <td></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No ready-mix inventory records found for this period.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- SECTION 2: MIXTURE INVENTORY -->
        <div class="section-title"><i class="fas fa-layer-group"></i> Mixture Inventory (Raw Materials)</div>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Cement (m³)</th>
                    <th>Fly Ash (m³)</th>
                    <th>Sand (m³)</th>
                    <th>3/4" (m³)</th>
                    <th>3/8" (m³)</th>
                    <th>G1 (m³)</th>
                    <th>Admix (m³)</th>
                    <th>Water (L)</th>
                    <th>Air (m³)</th>
                    <th>Others</th>
                    <th>Total (kgs/m³)</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($mixture_result && $mixture_result->num_rows > 0): ?>
                    <?php
                    $totals = ['cement' => 0, 'fly_ash' => 0, 'sand' => 0, '3_4' => 0, '3_8' => 0, 'g1' => 0, 'admix' => 0, 'water' => 0, 'air' => 0, 'others' => 0];
                    while ($row = $mixture_result->fetch_assoc()):
                        foreach ($totals as $key => &$val)
                            $val += floatval($row[$key]);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date_added']) ?></td>
                            <td><?= number_format($row['cement'], 2) ?></td>
                            <td><?= number_format($row['fly_ash'], 2) ?></td>
                            <td><?= number_format($row['sand'], 2) ?></td>
                            <td><?= number_format($row['3_4'], 2) ?></td>
                            <td><?= number_format($row['3_8'], 2) ?></td>
                            <td><?= number_format($row['g1'], 2) ?></td>
                            <td><?= number_format($row['admix'], 2) ?></td>
                            <td><?= number_format($row['water'], 2) ?></td>
                            <td><?= number_format($row['air'], 2) ?></td>
                            <td><?= number_format($row['others'], 2) ?></td>
                            <td><?= number_format($row['total'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td style="text-align: right;"><strong>TOTAL</strong></td>
                        <?php foreach ($totals as $t): ?>
                            <td><strong><?= number_format($t, 2) ?></strong></td>
                        <?php endforeach; ?>
                        <td><strong><?= number_format(array_sum($totals), 2) ?></strong></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="12" style="text-align: center;">No mixture inventory records found for this period.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="report-footer">This report is system-generated. For inquiries, please contact the System
            Administrator.</div>
    </div>

    <script>
        async function exportPDF() {
            if (typeof html2canvas === 'undefined' || typeof window.jspdf === 'undefined') {
                alert('PDF libraries not loaded. Please refresh the page.');
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Generating...';
            btn.disabled = true;

            try {
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pageWidth = 190;
                const pageHeight = 277;
                const margin = 10;

                const container = document.querySelector('.report-container');
                const clone = container.cloneNode(true);
                const filterBar = clone.querySelector('.filter-bar');
                if (filterBar) filterBar.remove();

                // Helper to render an element across multiple pages
                async function renderElement(element, startNewPage = false) {
                    if (!element) return;
                    if (startNewPage && pdf.internal.getNumberOfPages() > 0) pdf.addPage();

                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = `width:1100px; background:white; padding:20px 30px;`;
                    wrapper.appendChild(element.cloneNode(true));
                    document.body.appendChild(wrapper);
                    await new Promise(r => setTimeout(r, 100));

                    const canvas = await html2canvas(wrapper, { scale: 2.5, backgroundColor: '#ffffff', logging: false });
                    document.body.removeChild(wrapper);

                    const imgData = canvas.toDataURL('image/jpeg', 0.95);
                    const imgWidth = pageWidth;
                    const imgHeight = (canvas.height * imgWidth) / canvas.width;

                    let remaining = imgHeight;
                    let yOffset = 0;
                    while (remaining > 0) {
                        if (yOffset > 0) pdf.addPage();
                        const sliceH = Math.min(remaining, pageHeight);
                        const srcY = (yOffset / imgHeight) * canvas.height;
                        const srcH = (sliceH / imgHeight) * canvas.height;
                        const sliceCanvas = document.createElement('canvas');
                        sliceCanvas.width = canvas.width;
                        sliceCanvas.height = srcH;
                        const ctx = sliceCanvas.getContext('2d');
                        ctx.drawImage(canvas, 0, srcY, canvas.width, srcH, 0, 0, canvas.width, srcH);
                        pdf.addImage(sliceCanvas.toDataURL('image/jpeg', 0.95), 'JPEG', margin, margin, imgWidth, sliceH);
                        remaining -= sliceH;
                        yOffset += sliceH;
                    }
                }

                const header = clone.querySelector('.report-header');
                const rmTitle = clone.querySelector('.section-title');
                let rmTable = rmTitle?.nextElementSibling;
                while (rmTable && rmTable.tagName !== 'TABLE') rmTable = rmTable.nextElementSibling;

                if (header && rmTitle && rmTable) {
                    const firstWrapper = document.createElement('div');
                    firstWrapper.style.cssText = `width:1100px; background:white; padding:20px 30px;`;
                    firstWrapper.appendChild(header.cloneNode(true));
                    firstWrapper.appendChild(rmTitle.cloneNode(true));
                    firstWrapper.appendChild(rmTable.cloneNode(true));
                    await renderElement(firstWrapper, false);
                }

                const allTitles = clone.querySelectorAll('.section-title');
                const mixtureTitle = allTitles[1];
                let mixtureTable = mixtureTitle?.nextElementSibling;
                while (mixtureTable && mixtureTable.tagName !== 'TABLE') mixtureTable = mixtureTable.nextElementSibling;

                if (mixtureTitle && mixtureTable) {
                    const mixWrapper = document.createElement('div');
                    mixWrapper.style.cssText = `width:1100px; background:white; padding:20px 30px;`;
                    mixWrapper.appendChild(mixtureTitle.cloneNode(true));
                    mixWrapper.appendChild(mixtureTable.cloneNode(true));
                    await renderElement(mixWrapper, true);
                }

                const today = new Date();
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                pdf.save(`Inventory_Report_${months[today.getMonth()]}_${today.getDate()}_${today.getFullYear()}.pdf`);
            } catch (err) {
                console.error(err);
                alert('PDF generation failed: ' + err.message);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>

    <?php
    $stmt1->close();
    $stmt2->close();
    $conn->close();
    ?>
</body>

</html>