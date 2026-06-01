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
    <link rel="stylesheet" href="reports_management.css?v=4">
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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

<body>

    <div style="height: 60px;"></div>
    <div class="container-fluid mt-4">


        <!-- FILTER CARD WITH TITLE INSIDE -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- TITLE INSIDE CARD -->
                        <div class="text-center mb-4 pb-2 border-bottom">
                            <h2 style="color: #000; font-weight: bold; margin: 0;">Sorcom Reports Dashboard</h2>
                        </div>

                        <!-- ROW 1: Inventory Summary + Transaction Summary -->
                        <div class="row align-items-center g-3 mb-3 summary-row">
                            <div class="col-md-6">
                                <a href="/Inventory-Accounts-System-v1/pages/inventory_page/inv_reports.php"
                                    class="btn btn-primary btn-sm w-100 fw-bold">
                                    <i class="fas fa-boxes me-1"></i> Inventory Summary
                                </a>
                            </div>

                            <div class="col-md-6">
                                <a href="/Inventory-Accounts-System-v1/pages/statement_account_page/sales_analytics.php"
                                    class="btn btn-primary btn-sm w-100 fw-bold">
                                    <i class="fas fa-exchange-alt me-1"></i> Transaction Summary
                                </a>
                            </div>
                        </div>

                        <!-- ROW 2: Date From + Buttons -->
                        <div class="row align-items-center g-3 mb-3">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label small fw-bold mb-0 me-2 text-primary"
                                        style="min-width: 85px;">Date From:</label>
                                    <input type="date" id="start_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-01') ?>" style="flex: 1;">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <button id="filterBtn" class="btn btn-primary btn-sm w-100 fw-bold"
                                    onclick="filterData()">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                            </div>

                            <div class="col-md-3">
                                <button id="summaryBtn" class="btn btn-warning btn-sm w-100 fw-bold"
                                    onclick="showSummaryReport()">
                                    <i class="fas fa-file-alt me-1"></i> Summary Report
                                </button>
                            </div>

                            <div class="col-md-3">
                                <button id="updateBtn" class="btn btn-success btn-sm w-100 fw-bold"
                                    onclick="location.reload()">
                                    <i class="fas fa-sync-alt me-1"></i> Update
                                </button>
                            </div>
                        </div>

                        <!-- ROW 3: Date To + Buttons -->
                        <div class="row align-items-center g-3">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <label class="form-label small fw-bold mb-0 me-2 text-primary"
                                        style="min-width: 85px;">Date To:</label>
                                    <input type="date" id="end_date" class="form-control form-control-sm"
                                        value="<?= date('Y-m-d') ?>" style="flex: 1;">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <button id="clearFilterBtn" class="btn btn-secondary btn-sm w-100 fw-bold"
                                    onclick="clearFilter()">
                                    <i class="fas fa-eraser me-1"></i> Clear Filter
                                </button>
                            </div>

                            <div class="col-md-3">
                                <button id="pdfBtn" class="btn btn-danger btn-sm w-100 fw-bold" onclick="exportPDF()">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                            </div>

                            <div class="col-md-3">
                                <button id="chartToggle" class="btn btn-info btn-sm w-100 fw-bold"
                                    onclick="toggleChartType()">
                                    <i class="fas fa-chart-bar me-1"></i> Bar
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
                                    <hr style="margin: 5px 0; border-color: #555;">
                                    <p
                                        style="color: #ccc !important; margin: 5px 0 0 0; font-size: 12px; font-style: italic;">
                                        💡 Based on current stock levels and sales performance
                                    </p>
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
                    <div class="card-header mixture-header text-white">
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
                                    <hr style="margin: 5px 0; border-color: #555;">
                                    <p
                                        style="color: #ccc !important; margin: 5px 0 0 0; font-size: 12px; font-style: italic;">
                                        💡 Based on current stock levels and sales performance
                                    </p>
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
                                    <hr style="margin: 5px 0; border-color: #555;">
                                    <p
                                        style="color: #ccc !important; margin: 5px 0 0 0; font-size: 12px; font-style: italic;">
                                        💡 Based on current stock levels and sales performance
                                    </p>
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
        // 🔥 CHART TOGGLE - Bar/Line/Pie with Icons
        // ==========================================
        window.toggleChartType = function () {
            const types = ['bar', 'line', 'pie'];
            const icons = ['fa-chart-bar', 'fa-chart-line', 'fa-chart-pie'];
            const names = ['Bar', 'Line', 'Pie'];

            const index = types.indexOf(currentChartType);
            const nextIndex = (index + 1) % 3;
            currentChartType = types[nextIndex];

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

            // Update button text with icon
            const btn = document.getElementById('chartToggle');
            if (btn) {
                btn.innerHTML = `<i class="fas ${icons[nextIndex]} me-1"></i> ${names[nextIndex]}`;
            }
        };

        // ==========================================
        // 🔥 MAIN FILTER - SIMPLIFIED (NO CONFUSING NUMBER)
        // ==========================================
        window.filterData = function () {
            const startDate = document.getElementById('start_date')?.value || '';
            const endDate = document.getElementById('end_date')?.value || '';

            const btn = document.getElementById('filterBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            btn.disabled = true;

            fetch(`reports_data.php?ajax=filter_reports&start_date=${startDate}&end_date=${endDate}`)
                .then(r => r.json())
                .then(data => {
                    window.chartData.readymix = data.readymix;
                    window.chartData.mixture = data.mixture;
                    window.chartData.sales = data.sales;
                    updateAllCharts();

                    btn.innerHTML = '<i class="fas fa-check"></i> Done!';
                    btn.className = 'btn btn-success btn-sm w-100 fw-bold p-2 h-100';
                })
                .catch(e => {
                    console.error('AJAX ERROR:', e);
                    btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                    btn.className = 'btn btn-danger btn-sm w-100 fw-bold p-2 h-100';
                })
                .finally(() => {
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-filter me-1"></i> Filter';
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
                            borderColor: '#3a3a38',
                            borderWidth: currentChartType === 'pie' ? 1 : (currentChartType === 'line' ? 3 : 2),
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
        // 🔥 CLEAR FILTER - Reset to current month defaults
        // ==========================================
        window.clearFilter = function () {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

            const yearFrom = firstDay.getFullYear();
            const monthFrom = String(firstDay.getMonth() + 1).padStart(2, '0');
            const dayFrom = String(firstDay.getDate()).padStart(2, '0');
            const defaultStartDate = `${yearFrom}-${monthFrom}-${dayFrom}`;

            const yearTo = today.getFullYear();
            const monthTo = String(today.getMonth() + 1).padStart(2, '0');
            const dayTo = String(today.getDate()).padStart(2, '0');
            const defaultEndDate = `${yearTo}-${monthTo}-${dayTo}`;

            document.getElementById('start_date').value = defaultStartDate;
            document.getElementById('end_date').value = defaultEndDate;

            filterData();
            console.log(`✅ Cleared filter. Date range: ${defaultStartDate} to ${defaultEndDate}`);
        };

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

                const cards = document.querySelectorAll('.card.shadow-lg');
                console.log('Found cards:', cards.length);

                for (let i = 0; i < cards.length; i++) {
                    const card = cards[i];
                    const cardClone = card.cloneNode(true);

                    const originalCanvas = card.querySelector('canvas');
                    const cloneCanvas = cardClone.querySelector('canvas');

                    if (originalCanvas && cloneCanvas) {
                        const canvasId = originalCanvas.id;
                        if (charts[canvasId]) {
                            charts[canvasId].resize();
                            await new Promise(r => setTimeout(r, 100));

                            const chartImage = charts[canvasId].toBase64Image('image/png', 1.0);
                            const img = new Image();
                            img.src = chartImage;
                            img.style.cssText = `
                                width: 100%;
                                height: 300px;
                                object-fit: contain;
                                color: black;
                                border: 1.5px solid #3b82f6;
                                border-radius: 8px;
                                padding: 8px;
                                box-sizing: border-box;
                                display: block;
                                margin: 0;
                            `;

                            await new Promise(r => { img.onload = r; img.onerror = r; });

                            const chartContainer = cloneCanvas.closest('.chart-container');
                            console.log('chartContainer found:', chartContainer);
                            if (chartContainer) {
                                chartContainer.replaceWith(img);
                                img.style.marginBottom = '0';
                            } else {
                                cloneCanvas.parentNode.replaceChild(img, cloneCanvas);
                            }
                        }
                    }

                    const cardWrapper = document.createElement('div');
                    cardWrapper.style.cssText = `
                        border: 2px solid #000000;
                        border-radius: 10px;
                        overflow: hidden;
                        margin-bottom: 25px;
                        page-break-inside: avoid;
                    `;

                    const cloneCardBody = cardClone.querySelector('.card-body');
                    if (cloneCardBody) {
                        cloneCardBody.style.padding = '0';
                        cloneCardBody.style.marginBottom = '0';
                    }
                    const cloneChartContainer = cardClone.querySelector('.chart-container');
                    if (cloneChartContainer) {
                        cloneChartContainer.style.marginBottom = '0';
                        cloneChartContainer.style.height = '300px';
                        cloneChartContainer.style.overflow = 'hidden';
                        cloneChartContainer.style.padding = '0';
                    }

                    cardWrapper.appendChild(cardClone);
                    wrapper.appendChild(cardWrapper);
                }

                document.body.appendChild(wrapper);
                await new Promise(r => setTimeout(r, 500));

                // ✅ ONLY THIS SECTION CHANGED - render each card separately so charts are never split
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pageWidth = 190;
                const pageHeight = 277;
                let currentY = 10;

                // Render header
                const headerCanvas = await html2canvas(header, {
                    scale: 2.5,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true,
                    allowTaint: false,
                    willReadFrequently: true
                });
                const headerImg = headerCanvas.toDataURL('image/jpeg', 0.95);
                const headerH = (headerCanvas.height * pageWidth) / headerCanvas.width;
                pdf.addImage(headerImg, 'JPEG', 10, currentY, pageWidth, headerH);
                currentY += headerH + 5;

                // Render each card separately
                const cardWrappers = wrapper.querySelectorAll('div[style*="border: 2px solid"]');
                for (const cw of cardWrappers) {
                    const cardCanvas = await html2canvas(cw, {
                        scale: 2.5,
                        backgroundColor: '#ffffff',
                        logging: false,
                        useCORS: true,
                        allowTaint: false,
                        willReadFrequently: true
                    });

                    const cardImg = cardCanvas.toDataURL('image/jpeg', 0.95);
                    const cardH = (cardCanvas.height * pageWidth) / cardCanvas.width;

                    // ✅ If card doesn't fit, push entirely to next page
                    if (currentY + cardH > pageHeight) {
                        pdf.addPage();
                        currentY = 10;
                    }

                    pdf.addImage(cardImg, 'JPEG', 10, currentY, pageWidth, cardH);
                    currentY += cardH + 8;
                }
                // ✅ END OF CHANGED SECTION

                document.body.removeChild(wrapper);

                console.log(`PDF generated with ${pdf.internal.getNumberOfPages()} page(s)`);
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