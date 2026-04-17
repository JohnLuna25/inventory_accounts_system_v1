<?php
// Database Connection
$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get Date Range from URL (with validation)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate dates
if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
    $start_date = date('Y-m-d', strtotime('-30 days'));
    $end_date = date('Y-m-d');
}

// ==========================================
// Chart 1: Readymix Inventory (SECURE)
// ==========================================
$stmt = $conn->prepare("SELECT design_mix_id, SUM(stocks_in) as total 
                       FROM sorcom_readymix_inventory 
                       WHERE date_added BETWEEN ? AND ?
                       GROUP BY design_mix_id 
                       ORDER BY total DESC");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result_readymix = $stmt->get_result();

$readymixData = ['labels' => [], 'data' => []];
while ($row = $result_readymix->fetch_assoc()) {
    $readymixData['labels'][] = $row['design_mix_id'] ?: 'Unknown';
    $readymixData['data'][] = (float) ($row['total'] ?? 0);
}
$stmt->close();

// ==========================================
// Chart 2: Mixture Inventory (Latest Date) - SECURE
// ==========================================
$stmt = $conn->prepare("SELECT cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air 
                       FROM sorcom_mixture_inventory 
                       WHERE date_added = (SELECT MAX(date_added) FROM sorcom_mixture_inventory)
                       LIMIT 1");
$stmt->execute();
$result_mixture = $stmt->get_result();

$mixtureData = ['labels' => ['Cement', 'Fly Ash', 'Sand', '3/4', '3/8', 'G1', 'Admix', 'Water', 'Air'], 'data' => [0, 0, 0, 0, 0, 0, 0, 0, 0]];
if ($row = $result_mixture->fetch_assoc()) {
    $mixtureData['data'] = [
        floatval($row['cement'] ?? 0),
        floatval($row['fly_ash'] ?? 0),
        floatval($row['sand'] ?? 0),
        floatval($row['3_4'] ?? 0),
        floatval($row['3_8'] ?? 0),
        floatval($row['g1'] ?? 0),
        floatval($row['admix'] ?? 0),
        floatval($row['water'] ?? 0),
        floatval($row['air'] ?? 0)
    ];
}
$stmt->close();

// ==========================================
// Chart 3: Most Sold Design Mix (SECURE)
// ==========================================
$stmt = $conn->prepare("SELECT 
                          CONCAT(COALESCE(concrete_psi, ''), ' - ', COALESCE(curation_days, ''), ' - ', COALESCE(concrete_type, '')) as design, 
                          SUM(cubic_meters) as total 
                        FROM statement_of_account 
                        WHERE order_date BETWEEN ? AND ?
                        GROUP BY concrete_psi, curation_days, concrete_type 
                        ORDER BY total DESC 
                        LIMIT 5");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result_sales = $stmt->get_result();

$salesData = ['labels' => [], 'data' => []];
while ($row = $result_sales->fetch_assoc()) {
    $salesData['labels'][] = $row['design'] ?: 'Unknown Mix';
    $salesData['data'][] = (float) ($row['total'] ?? 0);
}
$stmt->close();

$conn->close();
?>