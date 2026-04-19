<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
header('Content-Type: application/json');

$psi = $_GET['psi'] ?? '';
$days = $_GET['days'] ?? '';
$type = $_GET['type'] ?? '';

error_log("INVENTORY CHECK: $psi | $days | $type"); // Debug log

$stmt = $conn->prepare("
    SELECT stocks_in, batch, current_stock_batch 
    FROM sorcom_readymix_inventory 
    WHERE design_mix_id = ? AND curation_days = ? AND concrete_type = ?
    ORDER BY date_added DESC LIMIT 1
");
$stmt->bind_param("sss", $psi, $days, $type);
$stmt->execute();
$result = $stmt->get_result();
$inventory = $result->fetch_assoc();
$stmt->close();

if ($inventory && intval($inventory['stocks_in']) > 0) {
    echo json_encode([
        'available' => true,
        'stocks_in' => $inventory['stocks_in'],
        'total_kgs' => number_format(floatval($inventory['current_stock_batch']), 1) . ' kgs',
        'batch_size' => number_format(floatval($inventory['batch']), 1) . ' kgs\/unit'
    ]);
} else {
    echo json_encode(['available' => false, 'found' => $inventory ? true : false]);
}
$conn->close();
?>