<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);

$psi = $_GET['psi'] ?? '';
$days = $_GET['days'] ?? '';
$type = $_GET['type'] ?? '';

if (empty($psi) || empty($days) || empty($type)) {
    echo json_encode(['available' => false, 'message' => 'Missing parameters']);
    exit;
}

$stmt = $conn->prepare("
    SELECT SUM(stocks_in) - SUM(stocks_out) as net_stock,
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
$conn->close();

$net_stock = ($row && $row['net_stock'] !== null) ? floatval($row['net_stock']) : 0;
$batch = ($row && $row['batch'] !== null) ? floatval($row['batch']) : 0;
$total_kgs = $net_stock * $batch;

echo json_encode([
    'available' => $net_stock > 0,
    'available_units' => (int) $net_stock,   // ✅ renamed from 'stocks_in'
    'batch_size' => number_format($batch, 1) . ' kgs/m³',
    'total_kgs' => number_format($total_kgs, 1) . ' kgs'
]);
?>