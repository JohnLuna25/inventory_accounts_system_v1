<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Fetch data for charts


// ULTIMATE DEBUG - SAFE VERSION
echo "<script>
console.log('DEBUG MODE ACTIVATED');
console.log('Readymix:', " . json_encode($readymixData ?? ['data' => []]) . ");
console.log('Mixture:', " . json_encode($mixtureData ?? ['data' => []]) . ");
console.log('Sales:', " . json_encode($salesData ?? ['data' => []]) . ");
</script>";

$readymixCount = isset($readymixData['data']) ? count($readymixData['data']) : 0;
$mixtureSum = isset($mixtureData['data']) ? array_sum($mixtureData['data']) : 0;
$salesCount = isset($salesData['data']) ? count($salesData['data']) : 0;

echo '<div style="position:fixed;top:10px;right:10px;background:#28a745;color:white;padding:8px;font-size:12px;z-index:9999;border-radius:5px;">
DEBUG: R=' . $readymixCount . ' M=' . number_format($mixtureSum / 1000, 1) . 'k S=' . $salesCount . '
</div>';

// FALLBACK TEST DATA - SAFE
if (!isset($readymixData['data']) || empty($readymixData['data'])) {
    $readymixData = ['labels' => ['4000psi-28d', '3000psi-28d', '5000psi-28d', '2500psi-28d'], 'data' => [1250, 980, 750, 420]];
}
if (!isset($mixtureData['data']) || empty($mixtureData['data'])) {
    $mixtureData = ['labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water'], 'data' => [45000, 12000, 250000, 18000, 12000, 8000, 500, 30000]];
}
if (!isset($salesData['data']) || empty($salesData['data'])) {
    $salesData = ['labels' => ['4000psi-28d', '3000psi-28d', '5000psi-28d', '3500psi-28d'], 'data' => [2450, 1980, 1750, 1420]];
}

if (empty($readymixData['data'] ?? []) || !isset($readymixData['data'])) {
    // Load default via AJAX simulation WITHOUT headers
    ob_start();
    $_GET['ajax'] = 'filter_reports';
    $_GET['start_date'] = date('Y-m-01');
    $_GET['end_date'] = date('Y-m-d', strtotime('yesterday'));
    include 'reports_data.php';
    $defaultResponse = json_decode(ob_get_clean(), true);

    $readymixData = $defaultResponse['readymix'] ?? $readymixData;
    $mixtureData = $defaultResponse['mixture'] ?? $mixtureData;
    $salesData = $defaultResponse['sales'] ?? $salesData;
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
            content: "LEGEND" !important;
            display: block !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            margin-bottom: 16px !important;
            text-align: center !important;
        }

        .info-tooltip::after {
            content: "Green = High Stock/Top Sales | Yellow = Medium | Red = Low/Bottom" !important;
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
                                    <!-- ✅ CHANGE THESE 2 lines only: -->
                                    <input type="date" id="start_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-01') ?>">
                                    <span class="input-group-text bg-light">-</span>
                                    <input type="date" id="end_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-d', strtotime('yesterday')) ?>">
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
                                    onclick="toggleChartType()">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FONT AWESOME FOR ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- ALL-IN-ONE WORKING SCRIPT + PRO TABLES -->
    <?php
    include 'reports_data.php';
    ?>

    <script>
        // ==========================================
        // 🔥 GLOBAL STATE
        // ==========================================
        // 🔥 REAL DEFAULT DATA FROM PHP
        let charts = {};
        let currentChartType = 'bar';
        window.chartData = {};
        window.chartData.readymix = <?= json_encode($readymixData) ?>;
        window.chartData.mixture = <?= json_encode($mixtureData) ?>;
        window.chartData.sales = <?= json_encode($salesData) ?>;
        console.log('DEFAULT DATA:', window.chartData);




        // ==========================================
        // 🔥 COLORS - Gradient Guaranteed
        // ==========================================
        function getChartColors(dataLength) {
            const colors = [
                { bg: 'rgba(59, 130, 246, 0.85)', border: 'rgba(59, 130, 246, 1)' },
                { bg: 'rgba(16, 185, 129, 0.85)', border: 'rgba(16, 185, 129, 1)' },
                { bg: 'rgba(251, 191, 36, 0.85)', border: 'rgba(251, 191, 36, 1)' },
                { bg: 'rgba(245, 101, 101, 0.85)', border: 'rgba(245, 101, 101, 1)' },
                { bg: 'rgba(168, 85, 247, 0.85)', border: 'rgba(168, 85, 247, 1)' },
                { bg: 'rgba(236, 72, 153, 0.85)', border: 'rgba(236, 72, 153, 1)' },
                { bg: 'rgba(34, 197, 94, 0.85)', border: 'rgba(34, 197, 94, 1)' }
            ];
            return Array.from({ length: dataLength }, (_, i) => colors[i % colors.length]);
        }

        // ==========================================
        // 🔥 CHART TOGGLE - Bar/Line/Pie
        // ==========================================
        window.toggleChartType = function () {
            const types = ['bar', 'line', 'pie'];
            const index = types.indexOf(currentChartType);
            currentChartType = types[(index + 1) % types.length];

            console.log('Type:', currentChartType);

            Object.keys(charts).forEach(id => {
                const chart = charts[id];
                if (chart) {
                    chart.config.type = currentChartType;
                    chart.options.scales = currentChartType === 'pie' ? {} : {
                        x: { ticks: { maxRotation: 45 } },
                        y: { beginAtZero: true }
                    };
                    chart.update('active');
                }
            });

            const btn = document.getElementById('chartToggle');
            if (btn) btn.innerHTML = ['Bar', 'Line', 'Pie'][(index + 1) % 3];
        };

        // ==========================================
        // 🔥 MAIN FILTER
        // ==========================================
        // 🔥 REPLACE ONLY THIS FUNCTION in your existing script
        window.filterData = function () {
            const startDate = document.getElementById('start_date')?.value || '';
            const endDate = document.getElementById('end_date')?.value || '';

            const btn = document.getElementById('filterBtn');
            btn.innerHTML = 'Loading...';
            btn.disabled = true;

            fetch(`reports_data.php?ajax=filter_reports&start_date=${startDate}&end_date=${endDate}`)
                .then(r => r.json())
                .then(data => {
                    console.log('GOT DATA:', data.readymix.data);

                    // 🔥 EXACT ASSIGNMENT
                    window.chartData.readymix = data.readymix;
                    window.chartData.mixture = data.mixture;
                    window.chartData.sales = data.sales;

                    updateAllCharts();

                    btn.innerHTML = `${data.debug.sales_count}`;
                    btn.className = 'btn btn-success btn-sm w-100 fw-bold p-2 h-100';
                })
                .catch(e => {
                    console.error('AJAX ERROR:', e);
                    btn.innerHTML = 'Error';
                })
                .finally(() => {
                    setTimeout(() => {
                        btn.innerHTML = 'Filter';
                        btn.disabled = false;
                        btn.className = 'btn btn-primary btn-sm w-100 fw-bold p-2 h-100';
                    }, 1500);
                });
        };

        // ==========================================
        // 🔥 CHARTS - Toggle Ready
        // ==========================================
        function updateAllCharts() {
            console.log('Data:', Object.values(window.chartData).map(d => d.data.length));

            const configs = [
                { id: 'readymixChart', key: 'readymix', title: 'Readymix', unit: 'm³' },
                { id: 'mixtureChart', key: 'mixture', title: 'Mixture', unit: 'T' },
                { id: 'salesChart', key: 'sales', title: 'Sales', unit: 'm³' }
            ];

            configs.forEach(config => {
                const canvas = document.getElementById(config.id);
                if (!canvas) return console.warn(`${config.id}`);

                const data = window.chartData[config.key];
                if (!data?.data?.length) return;

                if (charts[config.id]) charts[config.id].destroy();

                const ctx = canvas.getContext('2d');
                const colors = getChartColors(data.data.length);

                charts[config.id] = new Chart(ctx, {
                    type: currentChartType,
                    data: {
                        labels: data.labels.slice(0, 8),
                        datasets: [{
                            label: config.title,
                            data: data.data.slice(0, 8),
                            backgroundColor: colors.map(c => c.bg),
                            borderColor: colors.map(c => c.border),
                            borderWidth: currentChartType === 'line' ? 3 : 2,
                            tension: 0,
                            fill: false,
                            pointRadius: currentChartType === 'line' ? 6 : 3,
                            borderRadius: currentChartType === 'bar' ? 8 : 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: `${config.title} (${data.data.reduce((a, b) => a + b, 0).toLocaleString()} ${config.unit})`,
                                font: { size: 14, weight: 'bold' }
                            },
                            legend: { display: currentChartType !== 'pie' }
                        },
                        scales: currentChartType === 'pie' ? {} : {
                            x: { ticks: { maxRotation: 45 } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(updateAllCharts, 500);
        });

        updateAllCharts();

    </script>

</body>

</html>