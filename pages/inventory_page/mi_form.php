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

// Auto-fill from URL parameters (for ADD from mixture inventory)
$prefill_psi = trim($_GET['psi'] ?? '');
$prefill_days = trim($_GET['days'] ?? '');
$prefill_type = trim($_GET['type'] ?? '');
$prefill_batch = trim($_GET['batch'] ?? '');
$prefill_mixture_id = intval($_GET['mixture_id'] ?? 0);

// Check if we're in EDIT mode
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $isEditMode = true;
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, remarks FROM sorcom_readymix_inventory WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($date_added, $design_mix, $curation_days, $concrete_type, $batch, $stocks_in, $stocks_out, $remarks);
        $stmt->fetch();
        $stmt->close();
    }
}

// --- Fetch all PSI options ---
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

foreach ($psiData as $psi => $data) {
    sort($psiData[$psi]['days']);
}

$psiOptions = array_keys($psiData);
sort($psiOptions);

// Helper function to extract numeric value from strings
function extractNumeric($value)
{
    if (is_numeric($value)) {
        return floatval($value);
    }
    preg_match('/\d+(?:\.\d+)?/', $value, $matches);
    return isset($matches[0]) ? floatval($matches[0]) : 0;
}

// Function to calculate maximum producible units based on mixture stock
function getMaxProducibleUnits($conn, $design_mix, $curation_days, $concrete_type, $prefill_mixture_id)
{
    if ($prefill_mixture_id <= 0) {
        return ['max_units' => 0, 'total_kgs' => 0, 'batch_weight' => 0];
    }

    $design_stmt = $conn->prepare("SELECT vb_cement_mix, vb_fly_ash, vb_sand, vb_three_fourth, vb_three_eight, vb_G1, vb_admix, vb_water, vb_air, vb_total FROM scd_vb_list WHERE vb_concrete_psi = ? AND vb_curation_days = ? AND vb_concrete_type = ?");
    $design_stmt->bind_param("sss", $design_mix, $curation_days, $concrete_type);
    $design_stmt->execute();
    $design_result = $design_stmt->get_result();
    $design = $design_result->fetch_assoc();
    $design_stmt->close();

    if (!$design) {
        return ['max_units' => 0, 'total_kgs' => 0, 'batch_weight' => 0];
    }

    $mix_stmt = $conn->prepare("SELECT cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air FROM sorcom_mixture_inventory WHERE id = ?");
    $mix_stmt->bind_param("i", $prefill_mixture_id);
    $mix_stmt->execute();
    $mix_result = $mix_stmt->get_result();
    $mixture = $mix_result->fetch_assoc();
    $mix_stmt->close();

    if (!$mixture) {
        return ['max_units' => 0, 'total_kgs' => 0, 'batch_weight' => 0];
    }

    $cement_req = extractNumeric($design['vb_cement_mix']);
    $fly_ash_req = extractNumeric($design['vb_fly_ash']);
    $sand_req = extractNumeric($design['vb_sand']);
    $three_fourth_req = extractNumeric($design['vb_three_fourth']);
    $three_eight_req = extractNumeric($design['vb_three_eight']);
    $g1_req = extractNumeric($design['vb_G1']);
    $admix_req = extractNumeric($design['vb_admix']);
    $water_req = extractNumeric($design['vb_water']);
    $air_req = extractNumeric($design['vb_air']);

    $max_by_cement = ($cement_req > 0) ? floor(floatval($mixture['cement']) / $cement_req) : PHP_INT_MAX;
    $max_by_fly_ash = ($fly_ash_req > 0) ? floor(floatval($mixture['fly_ash']) / $fly_ash_req) : PHP_INT_MAX;
    $max_by_sand = ($sand_req > 0) ? floor(floatval($mixture['sand']) / $sand_req) : PHP_INT_MAX;
    $max_by_3_4 = ($three_fourth_req > 0) ? floor(floatval($mixture['3_4']) / $three_fourth_req) : PHP_INT_MAX;
    $max_by_3_8 = ($three_eight_req > 0) ? floor(floatval($mixture['3_8']) / $three_eight_req) : PHP_INT_MAX;
    $max_by_g1 = ($g1_req > 0) ? floor(floatval($mixture['g1']) / $g1_req) : PHP_INT_MAX;
    $max_by_admix = ($admix_req > 0) ? floor(floatval($mixture['admix']) / $admix_req) : PHP_INT_MAX;
    $max_by_water = ($water_req > 0) ? floor(floatval($mixture['water']) / $water_req) : PHP_INT_MAX;
    $max_by_air = ($air_req > 0) ? floor(floatval($mixture['air']) / $air_req) : PHP_INT_MAX;

    $max_units = min($max_by_cement, $max_by_fly_ash, $max_by_sand, $max_by_3_4, $max_by_3_8, $max_by_g1, $max_by_admix, $max_by_water, $max_by_air);
    $max_units = max(0, $max_units);

    $batch_weight = floatval($design['vb_total']);
    $total_kgs = $max_units * $batch_weight;

    return [
        'max_units' => $max_units,
        'total_kgs' => $total_kgs,
        'batch_weight' => $batch_weight
    ];
}

// Calculate stock information based on mixture
$stock_info = getMaxProducibleUnits($conn, $prefill_psi, $prefill_days, $prefill_type, $prefill_mixture_id);

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
    $prefill_mixture_id = intval($_POST['mixture_id'] ?? 0);

    // Recalculate stock info based on POST values for validation
    $stock_info = getMaxProducibleUnits($conn, $design_mix, $curation_days, $concrete_type, $prefill_mixture_id);

    // Validation
    if (empty($date_added) || empty($design_mix) || empty($curation_days) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($stocks_in < 0 || $stocks_out < 0) {
        $error_message = "Stocks In and Stock Out cannot be negative.";
    } elseif ($prefill_mixture_id > 0 && !$isEditMode && $stocks_in > $stock_info['max_units']) {
        $error_message = "Cannot produce {$stocks_in} units. Only {$stock_info['max_units']} units available based on current mixture stock.";
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
                $stmt->bind_param("ssssdiidsi", $date_added, $design_mix, $curation_days, $concrete_type, $batch, $stocks_in, $stocks_out, $current_stock_batch, $remarks, $id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating inventory: " . $stmt->error);
                }
                $stmt->close();
            } else {
                // ADD MODE: Insert new record
                $stmt = $conn->prepare("INSERT INTO sorcom_readymix_inventory 
                    (date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, current_stock_batch, remarks) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssdiids", $date_added, $design_mix, $curation_days, $concrete_type, $batch, $stocks_in, $stocks_out, $current_stock_batch, $remarks);
                if (!$stmt->execute()) {
                    throw new Exception("Error saving inventory: " . $stmt->error);
                }
                $stmt->close();

                // GOAL 1 & 2: Deduct materials from mixture inventory
                if ($prefill_mixture_id > 0) {
                    $units_to_produce = $stocks_in;

                    if ($units_to_produce > 0) {
                        // Get design requirements
                        $design_stmt = $conn->prepare("SELECT vb_cement_mix, vb_fly_ash, vb_sand, vb_three_fourth, vb_three_eight, vb_G1, vb_admix, vb_water, vb_air FROM scd_vb_list WHERE vb_concrete_psi = ? AND vb_curation_days = ? AND vb_concrete_type = ?");
                        $design_stmt->bind_param("sss", $design_mix, $curation_days, $concrete_type);
                        $design_stmt->execute();
                        $design_result = $design_stmt->get_result();
                        $design = $design_result->fetch_assoc();
                        $design_stmt->close();

                        if (!$design) {
                            throw new Exception("Design not found in masterlist.");
                        }

                        // Get current mixture inventory
                        $mix_stmt = $conn->prepare("SELECT cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air, others, total FROM sorcom_mixture_inventory WHERE id = ?");
                        $mix_stmt->bind_param("i", $prefill_mixture_id);
                        $mix_stmt->execute();
                        $mix_result = $mix_stmt->get_result();
                        $mixture = $mix_result->fetch_assoc();
                        $mix_stmt->close();

                        if (!$mixture) {
                            throw new Exception("Mixture inventory record not found.");
                        }

                        // Extract numeric values
                        $cement_req = extractNumeric($design['vb_cement_mix']);
                        $fly_ash_req = extractNumeric($design['vb_fly_ash']);
                        $sand_req = extractNumeric($design['vb_sand']);
                        $three_fourth_req = extractNumeric($design['vb_three_fourth']);
                        $three_eight_req = extractNumeric($design['vb_three_eight']);
                        $g1_req = extractNumeric($design['vb_G1']);
                        $admix_req = extractNumeric($design['vb_admix']);
                        $water_req = extractNumeric($design['vb_water']);
                        $air_req = extractNumeric($design['vb_air']);

                        // Calculate new values
                        $new_cement = floatval($mixture['cement']) - ($cement_req * $units_to_produce);
                        $new_fly_ash = floatval($mixture['fly_ash']) - ($fly_ash_req * $units_to_produce);
                        $new_sand = floatval($mixture['sand']) - ($sand_req * $units_to_produce);
                        $new_3_4 = floatval($mixture['3_4']) - ($three_fourth_req * $units_to_produce);
                        $new_3_8 = floatval($mixture['3_8']) - ($three_eight_req * $units_to_produce);
                        $new_g1 = floatval($mixture['g1']) - ($g1_req * $units_to_produce);
                        $new_admix = floatval($mixture['admix']) - ($admix_req * $units_to_produce);
                        $new_water = floatval($mixture['water']) - ($water_req * $units_to_produce);
                        $new_air = floatval($mixture['air']) - ($air_req * $units_to_produce);
                        $new_total = $new_cement + $new_fly_ash + $new_sand + $new_3_4 + $new_3_8 + $new_g1 + $new_admix + $new_water + $new_air;

                        // Check for negative stock
                        if ($new_cement < 0 || $new_fly_ash < 0 || $new_sand < 0 || $new_3_4 < 0 || $new_3_8 < 0 || $new_g1 < 0 || $new_admix < 0 || $new_water < 0 || $new_air < 0) {
                            throw new Exception("Insufficient stock. Required for {$units_to_produce} unit(s).");
                        }

                        // Update mixture inventory (DEDUCT)
                        $update_mix = $conn->prepare("UPDATE sorcom_mixture_inventory SET cement = ?, fly_ash = ?, sand = ?, `3_4` = ?, `3_8` = ?, g1 = ?, admix = ?, water = ?, air = ?, total = ? WHERE id = ?");
                        $update_mix->bind_param("ddddddddddi", $new_cement, $new_fly_ash, $new_sand, $new_3_4, $new_3_8, $new_g1, $new_admix, $new_water, $new_air, $new_total, $prefill_mixture_id);

                        if (!$update_mix->execute()) {
                            throw new Exception("Error deducting raw materials: " . $update_mix->error);
                        }
                        $update_mix->close();
                    }
                }
            }

            $conn->commit();
            // GOAL 3: Redirect back to inventory_management.php
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
    <title>
        <?= $isEditMode ? 'Edit Ready-mix' : 'Add Ready-mix from Mixture' ?>
    </title>

    <!-- Bootstrap CSS -->
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Page Specific CSS -->
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
        <form method="POST" action="mi_form.php<?= $isEditMode ? '?id=' . $id : '' ?>">
            <h2>
                <?= $isEditMode ? 'Edit Ready-mix' : 'Add Ready-mix from Mixture' ?>
            </h2>

            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="mixture_id" value="<?= $prefill_mixture_id ?>">

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
                        <option value="<?= htmlspecialchars($psi) ?>" <?= ($prefill_psi === $psi || $design_mix === $psi) ? 'selected' : '' ?>>
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
                <input type="text" name="batch" id="batch" value="<?= htmlspecialchars($prefill_batch ?: $batch) ?>"
                    readonly style="width: 420px; max-width: 420px; background-color: #e0e0e0;">
            </div>

            <!-- GOAL 4: Stock Information Display (Based on Mixture) -->
            <?php if ($prefill_mixture_id > 0 && !$isEditMode): ?>
                <div class="form-row">
                    <div
                        style="background: #e8f4f8; padding: 12px; border-radius: 8px; width: 100%; margin-top: 5px; margin-bottom: 5px;">
                        <div style="color: #28a745; font-weight: bold; margin-bottom: 5px;">
                            ✅
                            <?php echo $stock_info['max_units']; ?> units available
                        </div>
                        <div style="color: #ff5722; font-weight: bold; margin-bottom: 5px;">
                            📦
                            <?php echo number_format($stock_info['total_kgs'], 1); ?> kgs total
                        </div>
                        <div style="color: #2e3590; font-weight: bold;">
                            ⚖️
                            <?php echo number_format($stock_info['batch_weight'], 1); ?> kgs/m³
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stocks In -->
            <div class="form-row">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in" id="stocks_in" value="<?= $stocks_in ?>" min="0"
                    max="<?php echo $stock_info['max_units']; ?>" oninput="validateStocksIn(this)"
                    style="width: 420px; max-width: 420px;">
                <?php if ($prefill_mixture_id > 0 && !$isEditMode): ?>
                    <small style="color: #666; margin-left: 10px;">Max:
                        <?php echo $stock_info['max_units']; ?>
                    </small>
                <?php endif; ?>
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
        var prefillDays = "<?php echo htmlspecialchars($prefill_days); ?>";
        var prefillType = "<?php echo htmlspecialchars($prefill_type); ?>";
        var editDays = "<?php echo htmlspecialchars($curation_days); ?>";
        var editType = "<?php echo htmlspecialchars($concrete_type); ?>";
        var isEditMode = <?php echo $isEditMode ? 'true' : 'false'; ?>;
        var maxUnits = <?php echo $stock_info['max_units']; ?>;

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
                    if ((!isEditMode && day == prefillDays) || (isEditMode && day == editDays)) {
                        option.selected = true;
                    }
                    curationSelect.appendChild(option);
                });

                types.forEach(function (type) {
                    var option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    if ((!isEditMode && type == prefillType) || (isEditMode && type == editType)) {
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

        // GOAL 5: Validation - prevents entering more than max units
        function validateStocksIn(input) {
            var value = parseInt(input.value) || 0;
            if (value > maxUnits) {
                alert("Maximum " + maxUnits + " units available based on mixture stock.");
                input.value = maxUnits;
                updateCurrentStock();
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