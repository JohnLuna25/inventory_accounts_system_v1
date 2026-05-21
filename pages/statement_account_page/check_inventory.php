<?php
// Absolute path to avoid confusion
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
header('Content-Type: application/json');

// Enable error logging but not output
error_reporting(E_ALL);
ini_set('display_errors', 0);

$psi = $_GET['psi'] ?? '';
$days = $_GET['days'] ?? '';
$type = $_GET['type'] ?? '';

if (empty($psi) || empty($days) || empty($type)) {
    echo json_encode(['available' => false, 'message' => 'Missing parameters']);
    exit;
}

// Net stock from inventory
$stmt = $conn->prepare("
    SELECT SUM(stocks_in) - SUM(stocks_out) as total_stock,
           batch
    FROM sorcom_readymix_inventory
    WHERE design_mix_id = ? AND curation_days = ? AND concrete_type = ?
");
if (!$stmt) {
    echo json_encode(['available' => false, 'message' => 'SQL prepare failed']);
    exit;
}
$stmt->bind_param("sss", $psi, $days, $type);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$total_stock = $row ? floatval($row['total_stock']) : 0;
$batch = $row ? floatval($row['batch']) : 0;

// Total ordered but not Done
$stmt2 = $conn->prepare("
    SELECT SUM(sap_units) as total_ordered
    FROM statement_of_account
    WHERE concrete_psi = ? AND curation_days = ? AND concrete_type = ?
    AND order_status != 'Done'
");
if (!$stmt2) {
    echo json_encode(['available' => false, 'message' => 'Second SQL prepare failed']);
    exit;
}
$stmt2->bind_param("sss", $psi, $days, $type);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$stmt2->close();

$total_ordered = $row2 ? floatval($row2['total_ordered']) : 0;
$available = max(0, $total_stock - $total_ordered);
$total_kgs = $available * $batch;

echo json_encode([
    'available' => $available > 0,
    'stocks_in' => $available,
    'batch_size' => number_format($batch, 1) . ' kgs/m³',
    'total_kgs' => number_format($total_kgs, 1) . ' kgs'
]);

$conn->close();
?>