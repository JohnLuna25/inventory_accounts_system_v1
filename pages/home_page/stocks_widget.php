<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error) {
    echo '<div class="error-msg">Database connection failed</div>';
    return;
}

// Ready-mix Data (Bar Chart)
$readymixData = ['labels' => [], 'data' => []];
$readymix_sql = "SELECT design_mix_id as psi, SUM(stocks_in - stocks_out) as current_stock 
                 FROM sorcom_readymix_inventory 
                 GROUP BY design_mix_id 
                 ORDER BY current_stock DESC 
                 LIMIT 8";
$readymix_result = $conn->query($readymix_sql);
if ($readymix_result && $readymix_result->num_rows > 0) {
    while ($row = $readymix_result->fetch_assoc()) {
        $psi_clean = preg_replace('/[^0-9]/', '', $row['psi']);
        $readymixData['labels'][] = ($psi_clean ?: '0') . ' psi';
        $readymixData['data'][] = round((float) $row['current_stock'], 1);
    }
}
if (empty($readymixData['data'])) {
    $readymixData = ['labels' => ['4000 psi', '3000 psi', '5000 psi', '2500 psi'], 'data' => [1250, 980, 750, 420]];
}

// Mixture Data (Pie Chart)
$mixtureData = ['labels' => [], 'data' => []];
$mixture_sql = "
    SELECT 'Cement' as material, SUM(cement) as total FROM sorcom_mixture_inventory
    UNION ALL
    SELECT 'Fly Ash', SUM(fly_ash) FROM sorcom_mixture_inventory
    UNION ALL
    SELECT 'Sand', SUM(sand) FROM sorcom_mixture_inventory
    UNION ALL
    SELECT '3/4', SUM(`3_4`) FROM sorcom_mixture_inventory
    UNION ALL
    SELECT '3/8', SUM(`3_8`) FROM sorcom_mixture_inventory
    UNION ALL
    SELECT 'G1', SUM(g1) FROM sorcom_mixture_inventory
    UNION ALL
    SELECT 'Admix', SUM(admix) FROM sorcom_mixture_inventory
    UNION ALL
    SELECT 'Water', SUM(water) FROM sorcom_mixture_inventory
";
$mixture_result = $conn->query($mixture_sql);
if ($mixture_result && $mixture_result->num_rows > 0) {
    while ($row = $mixture_result->fetch_assoc()) {
        $mixtureData['labels'][] = $row['material'];
        $mixtureData['data'][] = round((float) $row['total'] / 1000, 1);
    }
}
if (empty($mixtureData['data'])) {
    $mixtureData = ['labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water'], 'data' => [45, 12, 250, 18, 12, 8, 0.5, 30]];
}
$conn->close();
?>

<style>
    .stocks-wrapper {
        width: 100%;
        margin-left: 100px;
        margin-top: -20px;
    }

    .stock-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .stock-header {
        background: #2e3590;
        color: white;
        padding: 15px 20px;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 18px;
    }

    .stock-header i {
        margin-right: 10px;
    }

    .chart-container {
        display: flex;
        justify-content: center;
        margin-left: 50px;
        max-width: 85%;
        max-height: 85%;
    }
</style>

<div class="stocks-wrapper">
    <!-- Ready-mix Bar Chart -->
    <div class="stock-card">
        <div class="stock-header"
            onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_page/inventory_management.php'"
            style="cursor: pointer;">
            <i class="fas fa-chart-bar"></i> Ready-mix Inventory Stock
        </div>
        <div class="chart-container">
            <canvas id="readymixChart" style="width:100%; height:250px;"></canvas>
        </div>
    </div>

    <!-- Mixture Pie Chart -->
    <div class="stock-card">
        <div class="stock-header"
            onclick="window.location.href='/Inventory-Accounts-System-v1/pages/inventory_page/mixture_inventory.php'"
            style="cursor: pointer;">
            <i class="fas fa-chart-pie"></i> Mixture Inventory Stock
        </div>
        <div class="chart-container">
            <canvas id="mixtureChart" style="width:100%; height:250px;"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ready-mix Bar Chart
        const barLabels = <?php echo json_encode($readymixData['labels']); ?>;
        const barData = <?php echo json_encode($readymixData['data']); ?>;

        if (barLabels.length > 0) {
            new Chart(document.getElementById('readymixChart'), {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Stock (m³)',
                        data: barData,
                        backgroundColor: '#00ff88',
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Stock (m³)' } } }
                }
            });
        }

        // Mixture Pie Chart
        const pieLabels = <?php echo json_encode($mixtureData['labels']); ?>;
        const pieData = <?php echo json_encode($mixtureData['data']); ?>;
        const pieColors = ['#00ff88', '#2ecc71', '#27ae60', '#ffeb3b', '#f1c40f', '#ff5722', '#e74c3c', '#c0392b'];

        if (pieLabels.length > 0) {
            new Chart(document.getElementById('mixtureChart'), {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieColors.slice(0, pieLabels.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: { legend: { position: 'right' } }
                }
            });
        }
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">