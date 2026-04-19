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
        /* CLEAN INFO TOOLTIP */
        .info-wrapper {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        .info-icon {
            background: #fef3c7 !important;
            color: #d97706 !important;
            font-weight: bold !important;
            font-size: 16px !important;
            width: 24px !important;
            height: 24px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: help !important;
            margin-left: 5px !important;
        }

        .info-tooltip {
            position: fixed !important;
            /* ← ADD THIS */
            top: 0vh !important;
            /* ← Top positioning */
            left: 22vw !important;
            /* ← Left positioning */
            transform: none !important;
            /* ← No transform */
            background: rgba(59, 59, 59, 0.95) !important;
            color: white !important;
            padding: 12px !important;
            border-radius: 8px !important;
            font-size: 12px !important;
            white-space: normal !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: all 0.2s !important;
            z-index: 999999 !important;
            /* ← ADD Z-INDEX */
            pointer-events: none !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5) !important;
            max-width: 200px !important;
        }

        .info-wrapper:hover .info-tooltip {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* REMOVED ::before ::after - Text in HTML now */
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
                                        onclick="location.reload()"> Update </button>
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
                                <span class="info-tooltip" style="
                                    background: rgba(59, 59, 59, 0.95) !important;
                                    color: white !important;
                                ">
                                    <p style="color: #00ff88 !important; text-shadow: 0 0 4px #00ff00;">Green = High
                                        Stock</p>
                                    <p style="color: #ffeb3b !important; text-shadow: 0 0 4px #ffff00;">Yellow =
                                        Medium</p>
                                    <p style="color: #ff5722 !important; text-shadow: 0 0 4px #ff4444;">Red =
                                        Low/Bottom</p>
                                </span>
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
                                <span class="info-tooltip" style="
                                    background: rgba(59, 59, 59, 0.95) !important;
                                    color: white !important;
                                ">
                                    <p style="color: #00ff88 !important; text-shadow: 0 0 4px #00ff00;">Green = High
                                        Stock</p>
                                    <p style="color: #ffeb3b !important; text-shadow: 0 0 4px #ffff00;">Yellow =
                                        Medium</p>
                                    <p style="color: #ff5722 !important; text-shadow: 0 0 4px #ff4444;">Red =
                                        Low/Bottom</p>
                                </span>
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
                                <span class="info-tooltip" style="
                                    background: rgba(59, 59, 59, 0.95) !important;
                                    color: white !important;
                                ">
                                    <p style="color: #00ff88 !important; text-shadow: 0 0 4px #00ff00;">Green = High
                                        Stock</p>
                                    <p style="color: #ffeb3b !important; text-shadow: 0 0 4px #ffff00;">Yellow =
                                        Medium</p>
                                    <p style="color: #ff5722 !important; text-shadow: 0 0 4px #ff4444;">Red =
                                        Low/Bottom</p>
                                </span>
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




        function getChartColors(data) {
            const max = Math.max(...data);
            const min = Math.min(...data);
            const range = max - min;
            const lowThreshold = min + range * 0.33;
            const highThreshold = min + range * 0.66;

            return data.map(value => {
                if (value >= highThreshold) return { bg: '#00ff88', border: '#00ff88' };      // Green
                if (value >= lowThreshold) return { bg: '#ffeb3b', border: '#ffeb3b' };       // Yellow
                return { bg: '#ff5722', border: '#ff5722' };                                  // Red
            });
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
                const colors = getChartColors(data.data);

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