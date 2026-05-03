<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// --- Initialize variables ---
$id = 0;
$date_added = date('Y-m-d');
$design_mix = "";
$curation_days = "";
$concrete_type = "";
$batch = 0;
$stocks_in = 0;
$stocks_out = 0;
$remarks = "";
$error_message = "";
$isEditMode = false;

// --- Check if we're in EDIT mode (has ?id= in URL) ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $id = intval($_GET['id']);

    // Load existing data
    $stmt = $conn->prepare("SELECT date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, remarks FROM sorcom_readymix_inventory WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($date_added, $design_mix, $curation_days, $concrete_type, $batch, $stocks_in, $stocks_out, $remarks);
        $stmt->fetch();
        $stmt->close();
    }
}

// --- Fetch all PSI options with their corresponding curation_days, concrete_type, and batch ---
$psiData = [];
$sql = "SELECT vb_concrete_psi, vb_curation_days, vb_concrete_type, vb_total FROM scd_vb_list";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $psi = trim($row['vb_concrete_psi']);
        $days = trim($row['vb_curation_days']);
        $type = trim($row['vb_concrete_type']);
        $total = floatval($row['vb_total']);

        if (!isset($psiData[$psi])) {
            $psiData[$psi] = ['days' => [], 'types' => [], 'totals' => []];
        }

        if (!in_array($days, $psiData[$psi]['days'], true)) {
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

// Get all unique PSI values sorted
$psiOptions = array_keys($psiData);
sort($psiOptions);

// --- Handle form submission (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $batch = floatval($_POST['batch'] ?? 0);
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $current_stock_batch = ($stocks_in - $stocks_out) * $batch;
    $remarks = $_POST['remarks'] ?? '';

    // Validation
    if (empty($date_added) || empty($design_mix) || empty($curation_days) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($stocks_in < 0 || $stocks_out < 0) {
        $error_message = "Stocks In and Stock Out cannot be negative.";
    } else {
        // Start Transaction
        $conn->begin_transaction();

        try {
            if ($id > 0 && $isEditMode) {
                // EDIT MODE: Update existing record
                $stmt = $conn->prepare("UPDATE sorcom_readymix_inventory 
                    SET date_added = ?, design_mix_id = ?, curation_days = ?, concrete_type = ?, 
                        batch = ?, stocks_in = ?, stocks_out = ?, current_stock_batch = ?, remarks = ? 
                    WHERE id = ?");
                $stmt->bind_param(
                    "ssssdiidsi",
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

                if (!$stmt->execute()) {
                    throw new Exception("Error updating inventory: " . $stmt->error);
                }
                $stmt->close();
            } else {
                // ADD MODE: Insert new record
                $stmt = $conn->prepare("INSERT INTO sorcom_readymix_inventory 
                    (date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, current_stock_batch, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "ssssdiids",
                    $date_added,
                    $design_mix,
                    $curation_days,
                    $concrete_type,
                    $batch,
                    $stocks_in,
                    $stocks_out,
                    $current_stock_batch,
                    $remarks
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error saving inventory: " . $stmt->error);
                }
                $stmt->close();
            }

            $conn->commit();
            header("Location: inventory_management.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEditMode ? 'Edit Inventory' : 'Add Inventory' ?></title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="ip_form.css">
    <?php showHeader(); ?>
</head>

<body style="background-color: #bfc3aa; font-family: Arial; padding: 20px;">

    <?php if ($error_message): ?>
        <p style="color: red; text-align: center; font-weight: bold;">
            <?= htmlspecialchars($error_message) ?>
        </p>
    <?php endif; ?>

    <div class="modal-content">
        <form method="POST" action="ip_form.php<?= $isEditMode ? '?id=' . $id : '' ?>">
            <h2>
                <?= $isEditMode ? 'Edit Inventory' : 'Add Inventory' ?>
            </h2>

            <!-- Hidden fields -->
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- Date -->
            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="date_added" value="<?= htmlspecialchars($date_added) ?>" required
                    style="width: 420px; max-width: 420px;">
            </div>

            <!-- Design Mix (PSI) -->
            <div class="form-row">
                <label>Design Mix (PSI):</label>
                <select name="design_mix" id="design_mix" required onchange="updateDropdowns()"
                    style="width: 420px; max-width: 420px;">
                    <option value="">-- Select PSI --</option>
                    <?php foreach ($psiOptions as $psi): ?>
                        <option value="<?= htmlspecialchars($psi) ?>" <?= ($design_mix === $psi) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($psi) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Curation Days -->
            <div class="form-row">
                <label>Curation Days:</label>
                <select name="curation_days" id="curation_days" required onchange="autoFillBatch()"
                    style="width: 420px; max-width: 420px;">
                    <option value="">-- Select Days --</option>
                </select>
            </div>

            <!-- Concrete Type -->
            <div class="form-row">
                <label>Concrete Type:</label>
                <select name="concrete_type" id="concrete_type" required onchange="autoFillBatch()"
                    style="width: 420px; max-width: 420px;">
                    <option value="">-- Select --</option>
                </select>
            </div>

            <!-- Batch (Auto-filled) -->
            <div class="form-row">
                <label>Batch (kgs/m³):</label>
                <input type="text" name="batch" id="batch" value="<?= htmlspecialchars($batch) ?>" readonly
                    style="width: 420px; max-width: 420px; background-color: #e0e0e0;">
            </div>

            <!-- Stocks In -->
            <div class="form-row">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in" id="stocks_in" value="<?= $stocks_in ?>" min="0"
                    oninput="updateCurrentStock()" style="width: 420px; max-width: 420px;">
            </div>

            <!-- Stocks Out -->
            <div class="form-row">
                <label>Stock Out:</label>
                <input type="number" name="stocks_out" id="stocks_out" value="<?= $stocks_out ?>" min="0"
                    oninput="updateCurrentStock()" style="width: 420px; max-width: 420px;">
                <div id="stock_warning" style="color: red; font-size: 11px; margin-left: 10px;"></div>
            </div>

            <!-- Current Stock (calculated) -->
            <div class="form-row">
                <label>Current Stock (kgs):</label>
                <input type="text" id="current_stock_display" readonly
                    style="width: 420px; max-width: 420px; background-color: #e0e0e0;">
            </div>

            <!-- Remarks -->
            <div class="form-row">
                <label>Remarks:</label>
                <textarea name="remarks" rows="4"
                    style="width: 420px; max-width: 420px;"><?= htmlspecialchars($remarks) ?></textarea>
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
        var editDays = "<?php echo htmlspecialchars($curation_days); ?>";
        var editType = "<?php echo htmlspecialchars($concrete_type); ?>";
        var isEditMode = <?php echo $isEditMode ? 'true' : 'false'; ?>;

        function updateDropdowns() {
            var selectedPSI = document.getElementById('design_mix').value;
            var curationSelect = document.getElementById('curation_days');
            var typeSelect = document.getElementById('concrete_type');
            var batchInput = document.getElementById('batch');

            curationSelect.innerHTML = '<option value="">-- Select Days --</option>';
            typeSelect.innerHTML = '<option value="">-- Select --</option>';
            batchInput.value = '';

            if (selectedPSI && psiData[selectedPSI]) {
                var days = psiData[selectedPSI]['days'] || [];
                var types = psiData[selectedPSI]['types'] || [];

                days.forEach(function (day) {
                    var option = document.createElement('option');
                    option.value = day;
                    option.textContent = day + ' days';
                    if (isEditMode && day == editDays) {
                        option.selected = true;
                    }
                    curationSelect.appendChild(option);
                });

                types.forEach(function (type) {
                    var option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    if (isEditMode && type == editType) {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
            }
            autoFillBatch();
        }

        function autoFillBatch() {
            var selectedPSI = document.getElementById('design_mix').value;
            var selectedDays = document.getElementById('curation_days').value;
            var selectedType = document.getElementById('concrete_type').value;
            var batchInput = document.getElementById('batch');

            if (selectedPSI && selectedDays && selectedType && psiData[selectedPSI] && psiData[selectedPSI]['totals']) {
                var key = selectedDays + '-' + selectedType;
                if (psiData[selectedPSI]['totals'][key]) {
                    batchInput.value = psiData[selectedPSI]['totals'][key];
                } else {
                    batchInput.value = '';
                }
            } else {
                batchInput.value = '';
            }
            updateCurrentStock();
        }

        function updateCurrentStock() {
            var stocksIn = parseFloat(document.getElementById('stocks_in').value) || 0;
            var stocksOut = parseFloat(document.getElementById('stocks_out').value) || 0;
            var batch = parseFloat(document.getElementById('batch').value) || 0;
            var currentStock = (stocksIn - stocksOut) * batch;
            document.getElementById('current_stock_display').value = currentStock.toFixed(1) + ' kgs';
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateDropdowns();
            updateCurrentStock();
        });
    </script>
</body>

</html>