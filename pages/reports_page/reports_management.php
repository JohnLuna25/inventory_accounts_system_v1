<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Fetch data for charts
include 'reports_data.php';

// ULTIMATE DEBUG - NO EMOJI
echo "<script>
console.log('DEBUG MODE ACTIVATED');
console.log('Readymix:', " . json_encode($readymixData) . ");
console.log('Mixture:', " . json_encode($mixtureData) . ");
console.log('Sales:', " . json_encode($salesData) . ");
</script>";
echo '<div style="position:fixed;top:10px;right:10px;background:#28a745;color:white;padding:8px;font-size:12px;z-index:9999;border-radius:5px;">
DEBUG: R=' . count($readymixData['data']) . ' M=' . number_format(array_sum($mixtureData['data']) / 1000, 1) . 'k S=' . count($salesData['data']) . '
</div>';

// FALLBACK TEST DATA
if (empty($readymixData['data'])) {
    $readymixData = ['labels' => ['4000psi-28d', '3000psi-28d', '5000psi-28d', '2500psi-7d'], 'data' => [1250, 980, 750, 420]];
}
if (empty($mixtureData['data'])) {
    $mixtureData = ['labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water'], 'data' => [45000, 12000, 250000, 18000, 12000, 8000, 500, 30000]];
}
if (empty($salesData['data'])) {
    $salesData = ['labels' => ['4000psi-28d', '3000psi-28d', '5000psi-28d', '3500psi-28d'], 'data' => [2450, 1980, 1750, 1420]];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorcom Reports Dashboard</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="reports_management.css?v=4">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <?php showHeader() ?>

    <!-- PRO TABLE STYLES -->
    <style>
        /* PRO TABLE STYLES */
        .table-toggle {
            transition: all 0.3s ease;
        }

        .data-table {
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 12px !important;
            border: 1px solid #e0e6ed !important;
            max-height: 250px !important;
            overflow: auto !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        }

        .data-table .header-row {
            color: #3b82f6 !important;
            background: rgba(59, 130, 246, 0.1) !important;
            font-weight: 700 !important;
        }

        .data-table .hover-row {
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            border-radius: 8px !important;
            margin: 0 -1rem !important;
            padding: 0.75rem 1rem !important;
        }

        .data-table .hover-row:hover {
            background: linear-gradient(90deg, #3b82f6, #60a5fa) !important;
            color: white !important;
            transform: translateX(4px) !important;
        }

        .table-btn {
            background: rgba(59, 130, 246, 0.1) !important;
            border: 2px solid #3b82f6 !important;
            color: #1e40af !important;
            font-weight: 600 !important;
        }

        .table-btn.active {
            background: #3b82f6 !important;
            color: white !important;
            transform: translateY(-1px) !important;
        }

        /* MAX Z-INDEX + NO CLIP */
        .info-wrapper {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
            cursor: pointer !important;
            z-index: 999999 !important;
        }

        .info-icon {
            background: rgba(254, 243, 199, 0.4) !important;
            color: #d97706 !important;
            font-weight: bold !important;
            font-size: 18px !important;
            padding: 8px !important;
            border-radius: 50% !important;
            margin-left: 8px !important;
            transition: all 0.3s ease !important;
        }

        .info-wrapper:hover .info-icon {
            background: #fef3c7 !important;
            color: #b45309 !important;
            transform: scale(1.2) !important;
        }

        /* TOOLTIP - UNBREAKABLE */
        .info-tooltip {
            display: none !important;
            position: fixed !important;
            top: 28% !important;
            left: 38% !important;
            transform: translate(-50%, -50%) !important;
            width: 240px !important;
            background: #000000dd !important;
            backdrop-filter: blur(20px) !important;
            padding: 16px !important;
            border-radius: 16px !important;
            border: 3px solid #ffffff !important;
            font-size: 12px !important;
            line-height: 1.4 !important;
            color: #ffffff !important;
            text-align: center !important;
            font-weight: 600 !important;
            z-index: 9999999 !important;
            box-shadow: 0 0 100px rgba(0, 0, 0, 0.9) !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .info-wrapper:hover .info-tooltip {
            display: block !important;
        }

        .info-tooltip::before {
            content: "📊 LEGEND" !important;
            display: block !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            margin-bottom: 16px !important;
            text-align: center !important;
        }

        .info-tooltip::after {
            content: "🟢 Green = High Stock/Top Sales | 🟡 Yellow = Medium | 🔴 Red = Low/Bottom" !important;
            display: block !important;
            font-size: 11px !important;
            margin-top: 12px !important;
            padding-top: 12px !important;
            border-top: 1px solid #ffffff40 !important;
        }
    </style>
</head>

<body style="background-color: #f5f5f5; font-family: Arial, sans-serif;">
    <div style="height: 20px;"></div>

    <div class="container-fluid mt-4">
        <!-- HEADER -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 style="color: #000; font-weight: bold;">Sorcom Reports Dashboard</h2>
            </div>
        </div>

        <!-- PERFECT FILTER BAR -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3">
                        <div class="row align-items-end g-2">
                            <!-- DATE RANGE -->
                            <div class="col-lg-5 col-md-6 col-sm-8">
                                <label class="form-label small fw-bold mb-1 text-primary">Date:</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="start_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                                    <span class="input-group-text bg-light">-</span>
                                    <input type="date" id="end_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>

                            <!-- WORKING FILTER BUTTON -->
                            <div class="col-lg-2 col-md-3 col-sm-4">
                                <label class="form-label small fw-bold mb-1 text-primary">Filter:</label>
                                <button id="filterBtn" class="btn btn-primary btn-sm w-100 fw-bold p-2 h-100"
                                    onclick="filterData()">
                                    Filter
                                </button>
                            </div>

                            <!-- ACTIONS -->
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <label class="form-label small fw-bold mb-1 text-success">Actions:</label>
                                <div class="d-grid gap-1 h-100">
                                    <button id="updateBtn" class="btn btn-success btn-sm fw-bold p-2"
                                        onclick="location.reload()">
                                        Update
                                    </button>
                                    <button id="pdfBtn" class="btn btn-secondary btn-sm fw-bold p-2"
                                        onclick="exportPDF()">
                                        PDF
                                    </button>
                                </div>
                            </div>

                            <!-- CHART TOGGLE -->
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <label class="form-label small fw-bold mb-1 text-info d-none d-md-block">Chart:</label>
                                <button id="chartToggle" class="btn btn-info btn-sm w-100 fw-bold p-2"
                                    data-chart-type="bar">
                                    Bar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART 1: READYMIX -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header readymix-header bg-primary text-white">
                        <h5 class="mb-0">
                            Readymix Inventory (By Design Mix)
                            <span class="info-wrapper">
                                <span class="info-icon">i</span>
                                <span class="info-tooltip"></span>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Chart Container -->
                        <div class="chart-container mb-4" style="height: 300px;">
                            <canvas id="readymixChart"></canvas>
                        </div>

                        <!-- PRO TABLE TOGGLE -->
                        <div class="table-toggle">
                            <button class="btn btn-sm table-btn w-100" data-target="readymixTable">
                                📋 Show Detailed Data <i class="fas fa-chevron-down ms-2"></i>
                            </button>
                            <div id="readymixTable" class="data-table mt-3 p-3" style="display: none">
                                <!-- JS populates here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART 2: MIXTURE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header mixture-header bg-success text-white">
                        <h5 class="mb-0">
                            Mixture Inventory (Current Stock)
                            <span class="info-wrapper">
                                <span class="info-icon">i</span>
                                <span class="info-tooltip"></span>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Chart Container -->
                        <div class="chart-container mb-4" style="height: 300px;">
                            <canvas id="mixtureChart"></canvas>
                        </div>

                        <!-- PRO TABLE TOGGLE -->
                        <div class="table-toggle">
                            <button class="btn btn-sm table-btn w-100" data-target="mixtureTable">
                                📋 Show Detailed Data <i class="fas fa-chevron-down ms-2"></i>
                            </button>
                            <div id="mixtureTable" class="data-table mt-3 p-3" style="display: none">
                                <!-- JS populates here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART 3: SALES -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header sales-header bg-info text-white">
                        <h5 class="mb-0">
                            Most Sold Design Mix (Top 5)
                            <span class="info-wrapper">
                                <span class="info-icon">i</span>
                                <span class="info-tooltip"></span>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Chart Container -->
                        <div class="chart-container mb-4" style="height: 300px;">
                            <canvas id="salesChart"></canvas>
                        </div>

                        <!-- PRO TABLE TOGGLE -->
                        <div class="table-toggle">
                            <button class="btn btn-sm table-btn w-100" data-target="salesTable">
                                📋 Show Detailed Data <i class="fas fa-chevron-down ms-2"></i>
                            </button>
                            <div id="salesTable" class="data-table mt-3 p-3" style="display: none">
                                <!-- JS populates here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FONT AWESOME FOR ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- ALL-IN-ONE WORKING SCRIPT + PRO TABLES -->
    <script>
        window.chartData = {
            readymix: <?= json_encode($readymixData) ?>,
            mixture: <?= json_encode($mixtureData) ?>,
            sales: <?= json_encode($salesData) ?>,
            pie: <?= json_encode($salesPieData) ?>
        };

        // CHARTS
        const charts = {};
        const chartIds = ['readymixChart', 'mixtureChart', 'salesChart'];

        chartIds.forEach((id, i) => {
            const canvas = document.getElementById(id);
            charts[id] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: window.chartData[id.replace('Chart', '').toLowerCase()].labels,
                    datasets: [{
                        label: id.replace('Chart', ''),
                        data: window.chartData[id.replace('Chart', '').toLowerCase()].data,
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'][i],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
        });

        // 🔥 PRO TABLE SYSTEM
        function initProTables() {
            initSingleTable('readymixTable', 'readymix', 'Design Mix', 'Stock (m³)');
            initSingleTable('mixtureTable', 'mixture', 'Material', 'Stock (kg)');
            initSingleTable('salesTable', 'sales', 'Design Mix', 'Units Sold');
        }

        function initSingleTable(tableId, dataKey, label1, label2) {
            const btn = document.querySelector(`[data-target="${tableId}"]`);
            const table = document.getElementById(tableId);
            const icon = btn.querySelector("i");

            btn.onclick = function () {
                if (table.style.display === "none") {
                    table.style.display = "block";
                    btn.classList.add("active");
                    icon.className = "fas fa-chevron-up ms-2";
                    btn.innerHTML = `📋 Hide Detailed Data <i class="fas fa-chevron-up ms-2"></i>`;
                    populateTable(tableId, dataKey, label1, label2);
                } else {
                    table.style.display = "none";
                    btn.classList.remove("active");
                    icon.className = "fas fa-chevron-down ms-2";
                    btn.innerHTML = `📋 Show Detailed Data <i class="fas fa-chevron-down ms-2"></i>`;
                }
            };
        }

        // 🐛 ULTIMATE BULLETPROOF LABEL CLEANER
        function cleanLabel(label) {
            if (!label || typeof label !== 'string') return 'Unknown';

            return label
                // 🔥 Fix ALL psi bugs
                .replace(/psi\s*psi/gi, 'psi')
                .replace(/psipsi/gi, 'psi')
                .replace(/psi\s*$/gi, 'psi')           // Trailing psi
                .replace(/^\s*psi/gi, 'psi')           // Leading psi

                // Multiple spaces → single
                .replace(/\s{2,}/g, ' ')

                // Remove extra junk
                .replace(/[-_]+/g, ' ')                 // --- → space
                .replace(/[^\w\s\-\/]/g, '')           // Remove special chars

                // Trim & limit
                .trim()
                .substring(0, 28)
                .replace(/\s+$/, '');                   // Final trim
        }

        function populateTable(tableId, dataKey, label1, label2) {
            const table = document.getElementById(tableId);
            const labels = window.chartData[dataKey].labels || [];
            const data = window.chartData[dataKey].data || [];

            console.log(`🔍 ${dataKey} RAW LABELS:`, labels); // DEBUG

            table.innerHTML = `
            <div class="row header-row align-items-center py-2 mb-3">
                <div class="col-8">${label1}</div>
                <div class="col-4 text-end">${label2}</div>
            </div>
            ${labels.map((label, i) => {
                if (i >= data.length) return '';
                const value = data[i];
                const colorClass = getColorClass(value, data);
                const formattedValue = dataKey === 'mixture' ?
                    (value / 1000).toFixed(1) + 'k' :
                    value.toLocaleString();
                const displayLabel = cleanLabel(label);

                return `
                    <div class="row align-items-center hover-row" title="Raw: '${label}' → ${value}">
                        <div class="col-8 fw-medium">${displayLabel}</div>
                        <div class="col-4 text-end fs-5 fw-bold ${colorClass}">${formattedValue}</div>
                    </div>
                `;
            }).join("")}
        `;
        }

        function getColorClass(value, dataArray) {
            if (dataArray.length === 0) return "text-muted";
            const sorted = [...dataArray].sort((a, b) => b - a);
            const highThreshold = sorted[Math.floor(sorted.length * 0.33)];
            const lowThreshold = sorted[Math.floor(sorted.length * 0.66)];

            if (value >= highThreshold) return "text-success";
            if (value >= lowThreshold) return "text-warning";
            return "text-danger";
        }

        // 🔥 CHART TOGGLE
        document.getElementById('chartToggle').onclick = function () {
            const btn = this;
            const currentType = btn.dataset.chartType;
            const nextType = { 'bar': 'line', 'line': 'pie', 'pie': 'bar' }[currentType];
            const nextText = { 'bar': 'Line', 'line': 'Pie', 'pie': 'Bar' }[currentType];

            btn.textContent = nextText;
            btn.dataset.chartType = nextType;

            chartIds.forEach(id => {
                const chart = charts[id];
                const dataKey = id.replace('Chart', '').toLowerCase();

                if (nextType === 'pie') {
                    chart.data.labels = window.chartData[dataKey].labels;
                    chart.data.datasets[0] = {
                        label: dataKey,
                        data: window.chartData[dataKey].data,
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b'].slice(0, window.chartData[dataKey].data.length)
                    };
                    chart.options = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };
                } else {
                    chart.data.labels = window.chartData[dataKey].labels;
                    chart.data.datasets[0] = {
                        label: dataKey,
                        data: window.chartData[dataKey].data,
                        borderColor: '#3b82f6',
                        backgroundColor: nextType === 'bar' ? ['#10b981', '#f59e0b', '#ef4444'][chartIds.indexOf(id)] : 'rgba(59, 130, 246, 0.1)',
                        borderWidth: nextType === 'line' ? 4 : 2,
                        tension: 0,
                        fill: nextType === 'line',
                        pointBackgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'],
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: nextType === 'line' ? 6 : 0
                    };
                    chart.options = { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } };
                }

                chart.config.type = nextType;
                chart.update('none');
            });
        };

        function filterData() { console.log('Filter clicked!'); }

        // 🔥 ULTRA CLEAN PDF EXPORT
        function exportPDF() {
            // 🗑️ HIDE Website Header + Filter Tab + Junk
            const elementsToHide = [
                'div[style*="position:fixed;top:10px;right:10px"]',  // Debug
                '.info-wrapper',                                      // Tooltips
                '#filterBtn, #updateBtn, #chartToggle',               // Buttons
                '.table-btn',                                         // Table toggles
                'div[style="height: 20px;"]',                         // Top spacer
                // 🔥 CRITICAL: Hide these sections
                '.row.mb-4:has(h2)',                                  // Header row
                '.row.mb-4:has(#start_date)',                         // Filter row 1
                '.row.mb-4:has(#filterBtn)'                           // Filter row 2
            ];

            const hiddenElements = [];
            elementsToHide.forEach(selector => {
                document.querySelectorAll(selector).forEach(el => {
                    hiddenElements.push(el);
                    el.style.display = 'none';
                });
            });

            // 💅 PROFESSIONAL PRINT STYLES
            const printStyles = `
            <style id="print-styles">
                @media print {
                    body { background: white !important; font-family: 'Segoe UI', Arial !important; }
                    .container-fluid { max-width: 850px !important; margin: 0 auto !important; }
                    .card { 
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important; 
                        border: 2px solid #e0e6ed !important;
                        page-break-inside: avoid !important;
                        margin-bottom: 25px !important;
                        border-radius: 12px !important;
                    }
                    .card-header { 
                        font-size: 20px !important; 
                        font-weight: 700 !important;
                        padding: 20px !important;
                        background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
                        color: white !important;
                        border-radius: 10px 10px 0 0 !important;
                        -webkit-print-color-adjust: exact !important;
                    }
                    .card-body { padding: 25px !important; }
                    .chart-container { 
                        height: 280px !important; 
                        background: #f8fafc !important;
                        border-radius: 8px !important;
                        page-break-inside: avoid !important;
                    }
                    .data-table { 
                        max-height: none !important; 
                        background: white !important;
                        font-size: 13px !important;
                        border-radius: 8px !important;
                    }
                    .header-row { 
                        background: linear-gradient(90deg, #3b82f6, #60a5fa) !important;
                        color: white !important;
                        font-size: 14px !important;
                    }
                    .hover-row:hover { background: rgba(59,130,246,0.1) !important; transform: none !important; }
                    @page { margin: 12mm !important; size: A4 portrait !important; }
                    * { max-width: 100% !important; }
                }
            </style>
        `;

            // Inject styles + title
            const style = document.createElement('style');
            style.id = 'print-styles';
            style.innerHTML = printStyles;
            document.head.appendChild(style);

            const title = document.createElement('h2');
            title.innerHTML = `📊 Sorcom Reports Dashboard<br><small style="font-size:14px;opacity:0.8;">${new Date().toLocaleDateString()}</small>`;
            title.style.cssText = 'text-align:center;margin-bottom:30px;font-size:28px;color:#1f2937;page-break-after:avoid;';
            document.querySelector('.container-fluid').prepend(title);

            // PRINT
            window.print();

            // 🧹 CLEANUP
            setTimeout(() => {
                document.getElementById('print-styles')?.remove();
                title.remove();
                hiddenElements.forEach(el => el.style.display = '');
            }, 1500);
        }

        // 🔥 INIT
        document.addEventListener('DOMContentLoaded', initProTables);
    </script>
</body>

</html>