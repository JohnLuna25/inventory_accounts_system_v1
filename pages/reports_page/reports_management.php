<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Fetch data for charts
include 'reports_data.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- 1. Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sorcom Reports Dashboard">

    <!-- 2. Page Title -->
    <title>Sorcom Reports Dashboard</title>

    <!-- 3. Bootstrap CSS -->
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">

    <!-- 4. Custom CSS (Fixed Path) -->
    <link rel="stylesheet" href="reports_management.css">

    <!-- 5. Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- 6. html2pdf.js (For PDF Export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <!-- 7. Custom JavaScript (Fixed Path) -->
    <script src="reports_logic.js"></script>

    <!-- 8. Your Custom Header Function -->
    <?php showHeader() ?>
</head>

<body style="background-color: #f5f5f5; font-family: Arial, sans-serif;">

    <!-- Spacer for Fixed Header -->
    <div style="height: 80px;"></div>

    <!-- Page Header -->
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4" style="color: #0b63c4; font-weight: bold;">📊 Sorcom Reports Dashboard</h2>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="dateFilterForm" method="GET" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Start Date:</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control"
                                        value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date:</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control"
                                        value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-success w-100" onclick="exportPDF()">📄 Export
                                        PDF</button>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-secondary w-100" onclick="window.print()">🖨️
                                        Print</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 1: Readymix Inventory -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">📦 Chart 1</h5>
                            <h5 class="mb-0">Readymix Inventory (By Design Mix)</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="readymixChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Mixture Inventory -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🧱 Chart 2</h5>
                            <h5 class="mb-0">Mixture Inventory (Current Stock)</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="mixtureChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 3: Most Sold Design Mix -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🏆 Chart 3</h5>
                            <h5 class="mb-0">Most Sold Design Mix (Top 5)</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Auto-refresh Indicator -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <small class="text-muted">🔄 Auto-refreshing every 30 seconds...</small>
            </div>
        </div>
    </div>

    <!-- Chart Initialization Script -->
    <script>
        // Define data variables FIRST (Fix for ReferenceError)
        const readymixData = <?= json_encode($readymixData ?? ['labels' => [], 'data' => []]) ?>;
        const mixtureData = <?= json_encode($mixtureData ?? ['labels' => [], 'data' => []]) ?>;
        const salesData = <?= json_encode($salesData ?? ['labels' => [], 'data' => []]) ?>;

        // Debug: Check if data is loaded
        console.log('=== DEBUG: Data Loading ===');
        console.log('Readymix Data:', readymixData);
        console.log('Mixture Data:', mixtureData);
        console.log('Sales Data:', salesData);
        console.log('Chart.js Available:', typeof Chart !== 'undefined');
        console.log('=== END DEBUG ===');

        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function () {
            console.log('=== DEBUG: Chart Initialization ===');
            console.log('Initializing Readymix Chart...');
            initReadymixChart(readymixData);
            console.log('Initializing Mixture Chart...');
            initMixtureChart(mixtureData);
            console.log('Initializing Sales Chart...');
            initSalesChart(salesData);
            console.log('=== END DEBUG ===');
        });
    </script>

</body>

</html>