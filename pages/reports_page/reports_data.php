<?php
// Database Connection
$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Date Range from URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// ==========================================
// Chart 1: Readymix Inventory (By Design Mix)
// ==========================================
$sql_readymix = "SELECT design_mix_id, SUM(stocks_in) as total 
                 FROM sorcom_readymix_inventory 
                 WHERE date_added BETWEEN '$start_date' AND '$end_date'
                 GROUP BY design_mix_id 
                 ORDER BY total DESC";
$result_readymix = $conn->query($sql_readymix);
$readymixData = ['labels' => [], 'data' => []];
if ($result_readymix) {
    while ($row = $result_readymix->fetch_assoc()) {
        $readymixData['labels'][] = $row['design_mix_id'];
        $readymixData['data'][] = $row['total'];
    }
}

// ==========================================
// Chart 2: Mixture Inventory (Latest Date)
// ==========================================
$sql_mixture = "SELECT cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air 
                FROM sorcom_mixture_inventory 
                WHERE date_added = (SELECT MAX(date_added) FROM sorcom_mixture_inventory)";
$result_mixture = $conn->query($sql_mixture);
$mixtureData = ['labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water', 'Air'], 'data' => []];
if ($result_mixture && $row = $result_mixture->fetch_assoc()) {
    $mixtureData['data'] = [
        floatval($row['cement']),
        floatval($row['fly_ash']),
        floatval($row['sand']),
        floatval($row['3_4']),
        floatval($row['3_8']),
        floatval($row['g1']),
        floatval($row['admix']),
        floatval($row['water']),
        floatval($row['air'])
    ];
}

// ==========================================
// Chart 3: Most Sold Design Mix (From statement_of_account)
// ==========================================
$sql_sales = "SELECT 
                CONCAT(concrete_psi, ' - ', curation_days, ' - ', concrete_type) as design, 
                SUM(cubic_meters) as total 
              FROM statement_of_account 
              WHERE order_date BETWEEN '$start_date' AND '$end_date'
              GROUP BY concrete_psi, curation_days, concrete_type 
              ORDER BY total DESC 
              LIMIT 5";
$result_sales = $conn->query($sql_sales);
$salesData = ['labels' => [], 'data' => []];
if ($result_sales) {
    while ($row = $result_sales->fetch_assoc()) {
        $salesData['labels'][] = $row['design'];
        $salesData['data'][] = $row['total'];
    }
}

$conn->close();
?>