<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';


// --- Initialize variables ---
$date_added = $design_mix = $curation_days = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;
$error_message = "";

// Auto-fill from URL parameters
$prefill_psi = $_GET['psi'] ?? '';
$prefill_days = $_GET['days'] ?? '';
$prefill_type = $_GET['type'] ?? '';
$prefill_batch = $_GET['batch'] ?? '';
$prefill_mixture_id = intval($_GET['mixture_id'] ?? 0);

// --- Fetch all PSI options with their corresponding curation_days, concrete_type, and batch ---
$psiData = [];
$sql = "SELECT vb_concrete_psi, vb_curation_days, vb_concrete_type, vb_total FROM scd_vb_list";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $psi = $row['vb_concrete_psi'];
        $days = $row['vb_curation_days'];
        $type = $row['vb_concrete_type'];
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

        // Store batch with key of days-type
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

    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $batch = floatval($_POST['batch'] ?? 0);
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $current_stock_batch = ($stocks_in - $stocks_out) * $batch;
    $remarks = $_POST['remarks'] ?? '';

    // Basic validation
    if (empty($date_added) || empty($design_mix) || empty($curation_days) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } else {

        // Insert into ready-mix inventory
        $sql = "INSERT INTO sorcom_readymix_inventory 
            (date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, current_stock_batch, remarks) 
            VALUES ('$date_added', '$design_mix', '$curation_days', '$concrete_type', $batch, $stocks_in, $stocks_out, $current_stock_batch, '$remarks')";

        if ($conn->query($sql)) {

            // If mixture_id is provided, deduct materials from mixture inventory
            if ($prefill_mixture_id > 0) {

                // Get design requirements from scd_vb_list
                $design_stmt = $conn->prepare("SELECT vb_cement_mix, vb_fly_ash, vb_sand, vb_three_fourth, vb_three_eight, vb_G1, vb_admix, vb_water, vb_air FROM scd_vb_list WHERE vb_concrete_psi = ? AND vb_curation_days = ? AND vb_concrete_type = ?");
                $design_stmt->bind_param("sss", $design_mix, $curation_days, $concrete_type);
                $design_stmt->execute();
                $design_result = $design_stmt->get_result();
                $design = $design_result->fetch_assoc();
                $design_stmt->close();

                if ($design) {
                    // Get current mixture inventory
                    $mix_stmt = $conn->prepare("SELECT cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air, others, total FROM sorcom_mixture_inventory WHERE id = ?");
                    $mix_stmt->bind_param("i", $prefill_mixture_id);
                    $mix_stmt->execute();
                    $mix_result = $mix_stmt->get_result();
                    $mixture = $mix_result->fetch_assoc();
                    $mix_stmt->close();

                    if ($mixture) {
                        // Calculate new values (deduct design requirements)
                        $new_cement = floatval($mixture['cement']) - floatval($design['vb_cement_mix']);
                        $new_fly_ash = floatval($mixture['fly_ash']) - floatval($design['vb_fly_ash']);
                        $new_sand = floatval($mixture['sand']) - floatval($design['vb_sand']);
                        $new_3_4 = floatval($mixture['3_4']) - floatval($design['vb_three_fourth']);
                        $new_3_8 = floatval($mixture['3_8']) - floatval($design['vb_three_eight']);
                        $new_g1 = floatval($mixture['g1']) - floatval($design['vb_G1']);
                        $new_admix = floatval($mixture['admix']) - floatval($design['vb_admix']);
                        $new_water = floatval($mixture['water']) - floatval($design['vb_water']);
                        $new_air = floatval($mixture['air']) - floatval($design['vb_air']);

                        // Recalculate total (others not included)
                        $new_total = $new_cement + $new_fly_ash + $new_sand + $new_3_4 + $new_3_8 + $new_g1 + $new_admix + $new_water + $new_air;

                        // Update mixture inventory
                        $update_mix = $conn->prepare("UPDATE sorcom_mixture_inventory SET cement = ?, fly_ash = ?, sand = ?, `3_4` = ?, `3_8` = ?, g1 = ?, admix = ?, water = ?, air = ?, total = ? WHERE id = ?");
                        $update_mix->bind_param("ddddddddddi", $new_cement, $new_fly_ash, $new_sand, $new_3_4, $new_3_8, $new_g1, $new_admix, $new_water, $new_air, $new_total, $prefill_mixture_id);
                        $update_mix->execute();
                        $update_mix->close();
                    }
                }
            }

            header("Location: inventory_management.php");
            exit;
        } else {
            $error_message = "Error saving inventory: " . $conn->error;
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
    <title>Add Inventory</title>
    <link rel="stylesheet" href="ip_editForm.css">
    <?php showHeader(); ?>
</head>

<body style="background-color: #bfc3aa; font-family: Arial; padding: 20px;">

    <?php if ($error_message): ?>
            <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <div class="modal-content">
        <form method="POST" action="ip_addForm.php">
            <h2>Add Inventory</h2>

            <!-- Date -->
            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="date_added" value="<?= htmlspecialchars($date_added ?: date('Y-m-d')) ?>" required style="width: 420px; max-width: 420px;">
            </div>

            <!-- Design Mix (PSI) -->
            <div class="form-row">
                <label>Design Mix (PSI):</label>
                <select name="design_mix" id="design_mix" required onchange="updateDropdowns()" style="width: 420px; max-width: 420px;">
                    <option value="">-- Select PSI --</option>
                    <?php foreach ($psiOptions as $psi): ?>
                            <option value="<?= htmlspecialchars($psi) ?>" <?= ($prefill_psi === $psi) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($psi) ?>
                            </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Curation Days -->
            <div class="form-row">
                <label>Curation Days:</label>
                <select name="curation_days" id="curation_days" required onchange="autoFillBatch()" style="width: 420px; max-width: 420px;">
                    <option value="">-- Select Days --</option>
                    <?php if (!empty($prefill_days)): ?>
                            <option value="<?= htmlspecialchars($prefill_days) ?>" selected>
                                <?= htmlspecialchars($prefill_days) ?>
                            </option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Concrete Type -->
            <div class="form-row">
                <label>Concrete Type:</label>
                <select name="concrete_type" id="concrete_type" required onchange="autoFillBatch()" style="width: 420px; max-width: 420px;">
                    <option value="">-- Select --</option>
                    <?php if (!empty($prefill_type)): ?>
                            <option value="<?= htmlspecialchars($prefill_type) ?>" selected>
                                <?= htmlspecialchars($prefill_type) ?>
                            </option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Batch (Auto-filled) -->
            <div class="form-row">
                <label>Batch (kgs/m³):</label>
                <input type="text" name="batch" id="batch" value="<?= htmlspecialchars($prefill_batch) ?>" readonly style="width: 420px; max-width: 420px; background-color: #e0e0e0;">
            </div>

            <!-- Stocks In -->
            <div class="form-row">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in" value="<?= $stocks_in ?>" min="0" style="width: 420px; max-width: 420px;">
            </div>

            <!-- Stocks Out -->
            <div class="form-row">
                <label>Stock Out:</label>
                <input type="number" name="stocks_out" value="<?= $stocks_out ?>" min="0" style="width: 420px; max-width: 420px;">
            </div>

            <!-- Remarks -->
            <div class="form-row">
                <label>Remarks:</label>
                <textarea name="remarks" rows="4" style="width: 420px; max-width: 420px;"><?= htmlspecialchars($remarks) ?></textarea>
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
                days.forEach(function (day) {
                    var option = document.createElement('option');
                    option.value = day;
                    option.textContent = day + (day.toString().includes('days') ? '' : ' days');
                    curationSelect.appendChild(option);
                });

                var types = psiData[selectedPSI]['types'] || [];
                types.forEach(function (type) {
                    var option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    typeSelect.appendChild(option);
                });
            }
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
        }

        document.addEventListener('DOMContentLoaded', function () {
            var selectedPSI = document.getElementById('design_mix').value;
            if (selectedPSI) {
                updateDropdowns();
                
                setTimeout(function () {
                    var selectedDays = "<?php echo htmlspecialchars($prefill_days); ?>";
                    var selectedType = "<?php echo htmlspecialchars($prefill_type); ?>";

                    var daysSelect = document.getElementById('curation_days');
                    var typeSelect = document.getElementById('concrete_type');

                    for (var i = 0; i < daysSelect.options.length; i++) {
                        if (daysSelect.options[i].value === selectedDays) {
                            daysSelect.selectedIndex = i;
                            break;
                        }
                    }

                    for (var i = 0; i < typeSelect.options.length; i++) {
                        if (typeSelect.options[i].value === selectedType) {
                            typeSelect.selectedIndex = i;
                            break;
                        }
                    }

                    autoFillBatch();
                }, 100);
            }
        });
    </script>

</body>

</html>