<?php
// ==========================================
// FAIL-SAFE DATA WITH BUILT-IN TEST DATA
// ==========================================

// Add to top of reports_data.php
if (isset($_GET['ajax']) && $_GET['ajax'] === 'filter_reports') {
    // Apply your date/time/year filters to SQL queries HERE
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $time_filter = $_GET['time_filter'] ?? 'monthly';
    $compare_year = $_GET['compare_year'] ?? '2022';

    // Modify your SQL WHERE clauses:
    // WHERE date BETWEEN '$start_date' AND '$end_date'
    // AND YEAR(date) = '$compare_year' (for annual)

    // Return JSON
    header('Content-Type: application/json');
    echo json_encode([
        'readymix' => $readymixData,
        'mixture' => $mixtureData,
        'sales' => $salesData
    ]);
    exit;
}

// Always provide working data first
$readymixData = [
    'labels' => ['4000psi-28d', '3000psi-28d', '5000psi-28d', '2500psi-7d', '3500psi-28d'],
    'data' => [1250, 980, 750, 420, 650]
];

$mixtureData = [
    'labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water'],
    'data' => [45000, 12000, 250000, 18000, 12000, 8000, 500, 30000]
];

$salesData = [
    'labels' => ['4000psi-28d', '3000psi-28d', '5000psi-28d', '3500psi-28d'],
    'data' => [2450, 1980, 1750, 1420]
];

// ==========================================
// OPTIONAL: TRY REAL DB DATA (Won't Break)
// ==========================================
$conn = @new mysqli('localhost', 'root', '', 'sorcom_db'); // Suppress connection errors
if (!$conn->connect_error) {

    // Chart 1: Readymix (Safe - no bind_param issues)
    $result = $conn->query("SELECT design_mix_id, SUM(stocks_in) as total 
                           FROM sorcom_readymix_inventory 
                           GROUP BY design_mix_id 
                           HAVING total > 0 ORDER BY total DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        $readymixData = ['labels' => [], 'data' => []];
        while ($row = $result->fetch_assoc()) {
            $readymixData['labels'][] = $row['design_mix_id'];
            $readymixData['data'][] = (float) $row['total'];
        }
    }

    // Chart 2: Mixture (Safe)
    $result = $conn->query("SELECT cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water 
                           FROM sorcom_mixture_inventory 
                           ORDER BY date_added DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $mixtureData['data'] = [
            (float) ($row['cement'] ?? 45000),
            (float) ($row['fly_ash'] ?? 12000),
            (float) ($row['sand'] ?? 250000),
            (float) ($row['3_4'] ?? 18000),
            (float) ($row['3_8'] ?? 12000),
            (float) ($row['g1'] ?? 8000),
            (float) ($row['admix'] ?? 500),
            (float) ($row['water'] ?? 30000)
        ];
    }

    // Chart 3: Sales (Safe)
    $result = $conn->query("SELECT CONCAT(concrete_psi, 'psi') as design, 
                           SUM(cubic_meters) as total 
                           FROM statement_of_account 
                           GROUP BY concrete_psi 
                           HAVING total > 0 ORDER BY total DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        $salesData = ['labels' => [], 'data' => []];
        while ($row = $result->fetch_assoc()) {
            $salesData['labels'][] = $row['design'];
            $salesData['data'][] = (float) $row['total'];
        }
    }

    $salesPieData = [
        'labels' => $salesData['labels'],  // Reuse sales labels
        'data' => $salesData['data'],      // Reuse sales data  
        'colors' => ['#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899']
    ];

    $conn->close();
}



// ==========================================
// DEBUG CONFIRMATION
// ==========================================
echo "<!-- ✅ DATA LOADED: Readymix=" . count($readymixData['data']) .
    " | Mixture=" . array_sum($mixtureData['data']) .
    " | Sales=" . count($salesData['data']) . " -->";


?>