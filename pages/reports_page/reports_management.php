<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Fetch data for charts

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <?php showHeader() ?>

    <!-- PRO TABLE STYLES -->
    <style>
        /* CLEAN INFO TOOLTIP - NO GLOW */
        .info-wrapper {
            position: relative !important;
            display: inline-flex !important;
            align-items: center !important;
        }

        .info-icon {
            background: #0059ff !important;
            color: #ffffff !important;
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
            top: 0vh !important;
            left: 22vw !important;
            background: rgba(59, 59, 59, 0.95) !important;
            color: white !important;
            padding: 12px !important;
            border-radius: 8px !important;
            font-size: 12px !important;
            white-space: normal !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: opacity 0.2s ease !important;
            z-index: 999999 !important;
            pointer-events: none !important;
            max-width: 200px !important;
        }

        .info-wrapper:hover .info-tooltip {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* PDF Export temporary wrapper styles */
        .pdf-export-wrapper {
            background: white;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .pdf-export-wrapper .card {
            margin-bottom: 30px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pdf-export-wrapper .card-header {
            background: #3b82f6 !important;
            color: #fef3c7 !important;
            padding: 15px;
        }

        .pdf-export-wrapper .chart-container {
            height: 300px !important;
            margin-bottom: 20px;
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

        <!-- PERFECT FILTER BAR - CENTERED CLEAN LAYOUT -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3">
                        <div class="row align-items-end g-3 justify-content-center">
                            <!-- DATE RANGE -->
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <label class="form-label small fw-bold mb-1 text-primary">Date:</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="start_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-01') ?>">
                                    <span class="input-group-text bg-light">-</span>
                                    <input type="date" id="end_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-d', strtotime('yesterday')) ?>">
                                </div>
                            </div>

                            <!-- FILTER + SUMMARY STACKED (NO LABELS) -->
                            <div class="col-lg-2 col-md-2 col-sm-3">
                                <div class="d-grid gap-1 h-100">
                                    <button id="filterBtn" class="btn btn-primary btn-sm w-100 fw-bold p-2 flex-fill"
                                        onclick="filterData()">
                                        Filter
                                    </button>
                                    <button id="summaryBtn" class="btn btn-warning btn-sm w-100 fw-bold p-2 flex-fill"
                                        onclick="showSummaryReport()">
                                        Summary Report
                                    </button>
                                </div>
                            </div>

                            <!-- ACTIONS STACK (NO LABEL) -->
                            <div class="col-lg-2 col-md-2 col-sm-3">
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
                            <div class="col-lg-2 col-md-2 col-sm-3">
                                <label class="form-label small fw-bold mb-1 text-info d-none d-md-block">Chart
                                    toggle:</label>
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
                                    <p style="color: #00ff88 !important;">Green = High Stock / Most Sold</p>
                                    <p style="color: #ffeb3b !important;">Yellow = Medium Stock / Average Sold</p>
                                    <p style="color: #ff5722 !important;">Red = Low Stock / Least Sold</p>
                                </span>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
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
                                    <p style="color: #00ff88 !important;">Green = High Stock / Most Sold</p>
                                    <p style="color: #ffeb3b !important;">Yellow = Medium Stock / Average Sold</p>
                                    <p style="color: #ff5722 !important;">Red = Low Stock / Least Sold</p>
                                </span>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
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
                                    <p style="color: #00ff88 !important;">Green = High Stock / Most Sold</p>
                                    <p style="color: #ffeb3b !important;">Yellow = Medium Stock / Average Sold</p>
                                    <p style="color: #ff5722 !important;">Red = Low Stock / Least Sold</p>
                                </span>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body p-4">
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
                if (value >= highThreshold) return { bg: '#00ff88', border: '#00ff88' };
                if (value >= lowThreshold) return { bg: '#ffeb3b', border: '#ffeb3b' };
                return { bg: '#ff5722', border: '#ff5722' };
            });
        }

        // ==========================================
        // 🔥 CHART TOGGLE - Bar/Line/Pie
        // ==========================================
        window.toggleChartType = function () {
            const types = ['bar', 'line', 'pie'];
            const index = types.indexOf(currentChartType);
            currentChartType = types[(index + 1) % types.length];

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
        window.filterData = function () {
            const startDate = document.getElementById('start_date')?.value || '';
            const endDate = document.getElementById('end_date')?.value || '';

            const btn = document.getElementById('filterBtn');
            btn.innerHTML = 'Loading...';
            btn.disabled = true;

            fetch(`reports_data.php?ajax=filter_reports&start_date=${startDate}&end_date=${endDate}`)
                .then(r => r.json())
                .then(data => {
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
        // 🔥 UPDATE ALL CHARTS
        // ==========================================
        function updateAllCharts() {
            const configs = [
                { id: 'readymixChart', key: 'readymix', title: 'Readymix', unit: 'kg/m³' },
                { id: 'mixtureChart', key: 'mixture', title: 'Mixture', unit: 'kg/m³' },
                { id: 'salesChart', key: 'sales', title: 'Sales', unit: 'm³' }
            ];

            configs.forEach(config => {
                const canvas = document.getElementById(config.id);
                if (!canvas) return;

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

        // ==========================================
        // 🔥 PDF EXPORT FUNCTION - MULTI-PAGE SUPPORT
        // ==========================================
        window.exportPDF = async function () {
            const btn = document.getElementById('pdfBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
            btn.disabled = true;

            try {
                const startDate = document.getElementById('start_date')?.value || '';
                const endDate = document.getElementById('end_date')?.value || '';

                // Create a temporary container
                const wrapper = document.createElement('div');
                wrapper.style.cssText = `
            position: absolute;
            top: -9999px;
            left: 0;
            width: 210mm;
            background: white;
            padding: 20px;
            font-family: Arial, sans-serif;
        `;

                // Add header
                const header = document.createElement('div');
                header.style.cssText = `
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3b82f6;
        `;
                header.innerHTML = `
            <h1 style="color: #2c3e50; margin-bottom: 5px;">Sorcom Reports Dashboard</h1>
            <p style="color: #666;">Period: ${startDate} - ${endDate}</p>
            <p style="color: #666;">Generated: ${new Date().toLocaleString()}</p>
        `;
                wrapper.appendChild(header);

                // Get all cards
                const cards = document.querySelectorAll('.card.shadow-lg');
                console.log('Found cards:', cards.length);

                for (let i = 0; i < cards.length; i++) {
                    const card = cards[i];
                    const cardClone = card.cloneNode(true);

                    // Find canvas in this card
                    const originalCanvas = card.querySelector('canvas');
                    const cloneCanvas = cardClone.querySelector('canvas');

                    if (originalCanvas && cloneCanvas) {
                        const canvasId = originalCanvas.id;

                        if (charts[canvasId]) {
                            // Create an image from the existing chart
                            const chartImage = charts[canvasId].canvas.toDataURL('image/png');
                            const img = document.createElement('img');
                            img.src = chartImage;
                            img.style.width = '100%';
                            img.style.height = '300px';
                            img.style.objectFit = 'contain';

                            // Replace canvas with image
                            cloneCanvas.parentNode.replaceChild(img, cloneCanvas);
                        }
                    }

                    cardClone.style.marginBottom = '30px';
                    cardClone.style.pageBreakInside = 'avoid';
                    wrapper.appendChild(cardClone);
                }

                document.body.appendChild(wrapper);

                // Wait for rendering
                await new Promise(r => setTimeout(r, 500));

                // Capture the entire wrapper as canvas
                const canvas = await html2canvas(wrapper, {
                    scale: 2.5,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true,
                    allowTaint: false
                });

                document.body.removeChild(wrapper);

                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                const imgData = canvas.toDataURL('image/jpeg', 0.95);
                const imgWidth = 190; // mm (A4 width minus margins)
                const pageHeight = 277; // mm (A4 height minus margins)
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                let heightLeft = imgHeight;
                let position = 10;

                // Add first page
                pdf.addImage(imgData, 'JPEG', 10, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // Add additional pages if content overflows
                let pageCount = 1;
                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'JPEG', 10, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                    pageCount++;
                }

                console.log(`PDF generated with ${pageCount} page(s)`);
                pdf.save(`sorcom_report_${startDate}_to_${endDate}.pdf`);

            } catch (error) {
                console.error('PDF Error:', error);
                alert('Failed to generate PDF: ' + error.message);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        // ==========================================
        // 🔥 SUMMARY REPORT
        // ==========================================
        window.showSummaryReport = function () {
            const startDate = document.getElementById('start_date')?.value || '';
            const endDate = document.getElementById('end_date')?.value || '';
            window.location.href = `summary_reports.html?start_date=${startDate}&end_date=${endDate}`;
        };

        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(updateAllCharts, 500);
        });

        updateAllCharts();
    </script>

</body>

</html>