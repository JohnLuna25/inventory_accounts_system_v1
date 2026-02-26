<?php
// ip_editForm.php

require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check database connection
if (!$conn || $conn->connect_errno) {
    die("Database connection not available: " . ($conn->connect_error ?? 'Unknown error'));
}

// Initialize variables
$error_message = "";
$date_added = $design_mix = $curation_days = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;

// Redirect if no ID is provided
if (!isset($_GET['id'])) {
    header("Location: inventory_management.php");
    exit;
}

$id = intval($_GET['id']);

// --- Fetch all PSI options with their corresponding curation_days, concrete_type, and total ---
$psiData = [];
$sql = "SELECT vb_concrete_psi, vb_curation_days, vb_concrete_type, vb_total FROM scd_vb_list";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $psi = $row['vb_concrete_psi'];
        $days = preg_replace('/\s+days$/i', '', $row['vb_curation_days']);
        $type = $row['vb_concrete_type'];
        $total = floatval($row['vb_total']);

        if (!isset($psiData[$psi])) {
            $psiData[$psi] = ['days' => [], 'types' => [], 'totals' => []];
        }

        if (!in_array($days, $psiData[$psi]['days'])) {
            $psiData[$psi]['days'][] = $days;
        }

        if (!in_array($type, $psiData[$psi]['types'])) {
            $psiData[$psi]['types'][] = $type;
        }

        $psiData[$psi]['totals'][$days . '-' . $type] = $total;
    }
    $result->free();
}

// Sort curation days for each PSI
foreach ($psiData as $psi => $data) {
    sort($psiData[$psi]['days']);
}

// Sorted list of PSI options
$psiOptions = array_keys($psiData);
sort($psiOptions);

// --- Load existing inventory data ---
$stmt = $conn->prepare("
    SELECT date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, remarks
    FROM sorcom_readymix_inventory
    WHERE id = ?
");

if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result(
        $date_added,
        $design_mix,
        $curation_days,
        $concrete_type,
        $batch,
        $stocks_in,
        $stocks_out,
        $remarks
    );
    $stmt->fetch();
    $stmt->close();
} else {
    $error_message = "Error preparing statement: " . $conn->error;
}

// --- Get the batch for the current record ---
$currentBatch = '';
if ($design_mix && $curation_days && $concrete_type) {
    $key = $curation_days . '-' . $concrete_type;
    if (isset($psiData[$design_mix]['totals'][$key])) {
        $currentBatch = $psiData[$design_mix]['totals'][$key];
    }
}

$conn->close();

// --- Handle form submission (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['update_id'] ?? $_GET['id'] ?? 0);
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $batch = floatval($_POST['batch'] ?? 0);
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);

    // Calculate current stock for this batch
    $current_stock_batch = ($stocks_in - $stocks_out) * $batch;

    $remarks = $_POST['remarks'] ?? '';

    // Create new connection for update
    $conn2 = new mysqli('localhost', 'root', '', 'sorcom_db');

    // Use prepared statement to prevent SQL errors
    $stmt2 = $conn2->prepare("
        UPDATE sorcom_readymix_inventory
        SET date_added = ?, design_mix_id = ?, curation_days = ?, concrete_type = ?, 
            batch = ?, stocks_in = ?, stocks_out = ?, current_stock_batch = ?, remarks = ?
        WHERE id = ?
    ");

    // Bind parameters
    // Note: 's' used for strings, 'd' for double, 'i' for integer
    $stmt2->bind_param(
        "ssssdiiissi",
        $date_added,
        $design_mix,
        $curation_days,
        $concrete_type,
        $batch,
        $stocks_in,
        $stocks_out,
        $current_stock_batch,
        $remarks,
        $id
    );

    if ($stmt2->execute()) {
        $stmt2->close();
        $conn2->close();
        header("Location: inventory_management.php");
        exit;
    } else {
        $error_message = "Error updating inventory: " . $stmt2->error;
        $stmt2->close();
        $conn2->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Inventory</title>
    <link rel="stylesheet" href="ip_editForm.css">
    <?php showHeader(); ?>
</head>

<body style="background-color: #bfc3aa; font-family: Arial; padding: 20px;">

    <?php if ($error_message): ?>
        <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <div class="modal-content">
        <form method="POST" action="ip_editForm.php?id=<?= $id ?>">
            <h2>Edit Inventory</h2>

            <input type="hidden" name="update_id" value="<?= $id ?>">

            <!-- Date -->
            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="date_added" value="<?= htmlspecialchars($date_added) ?>" required>
            </div>

            <!-- Design Mix (PSI) -->
            <div class="form-row">
                <label>Design Mix (PSI):</label>
                <select name="design_mix" id="design_mix" required onchange="updateDropdowns()">
                    <option value="">-- Select PSI --</option>
                    <?php foreach ($psiOptions as $psi): ?>
                        <option value="<?= htmlspecialchars($psi) ?>" <?= $design_mix === $psi ? 'selected' : '' ?>>
                            <?= htmlspecialchars($psi) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Curation Days -->
            <div class="form-row">
                <label>Curation Days:</label>
                <select name="curation_days" id="curation_days" required onchange="autoFillBatch()">
                    <option value="">-- Select Days --</option>
                    <?php if (!empty($curation_days)): ?>
                        <option value="<?= htmlspecialchars($curation_days) ?>" selected>
                            <?= htmlspecialchars($curation_days) ?> days
                        </option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Concrete Type -->
            <div class="form-row">
                <label>Concrete Type:</label>
                <select name="concrete_type" id="concrete_type" required onchange="autoFillBatch()">
                    <option value="">-- Select --</option>
                    <?php if (!empty($concrete_type)): ?>
                        <option value="<?= htmlspecialchars($concrete_type) ?>" selected>
                            <?= htmlspecialchars($concrete_type) ?>
                        </option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Batch (Auto-filled) -->
            <div class="form-row">
                <label>Batch (kgs/m³):</label>
                <input type="text" name="batch" id="batch" value="<?= htmlspecialchars($currentBatch) ?>" readonly
                    style="background-color: #e0e0e0;">
            </div>

            <!-- Stocks In -->
            <div class="form-row">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in" value="<?= $stocks_in ?>" min="0">
            </div>

            <!-- Stocks Out -->
            <div class="form-row">
                <label>Stock Out:</label>
                <input type="number" name="stocks_out" value="<?= $stocks_out ?>" min="0">
            </div>

            <!-- Remarks -->
            <div class="form-row">
                <label>Remarks:</label>
                <textarea name="remarks" rows="4"><?= htmlspecialchars($remarks) ?></textarea>
            </div>

            <!-- Buttons -->
            <div class="form-row" style="justify-content: center;">
                <button type="submit">Save</button>
                <a href="inventory_management.php">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        var psiData = <?php echo json_encode($psiData); ?>;
        var selectedPSI = "<?php echo htmlspecialchars($design_mix); ?>";
        var selectedDays