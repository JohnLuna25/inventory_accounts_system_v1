<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';


// --- Initialize variables ---
$date_added = $design_mix = $curation_days = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;
$error_message = "";

// --- Fetch all PSI options with their corresponding curation_days and concrete_type ---
$psiData = [];
$sql = "SELECT DISTINCT concrete_psi, curation_days, concrete_type FROM sorcom_concrete_design_masterlist";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $psi = $row['concrete_psi'];
        $days = $row['curation_days'];
        $type = $row['concrete_type'];

        if (!isset($psiData[$psi]['days'])) {
            $psiData[$psi]['days'] = [];
        }
        if (!in_array($days, $psiData[$psi]['days'])) {
            $psiData[$psi]['days'][] = $days;
        }

        if (!isset($psiData[$psi]['types'])) {
            $psiData[$psi]['types'] = [];
        }
        if (!in_array($type, $psiData[$psi]['types'])) {
            $psiData[$psi]['types'][] = $type;
        }
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
    $batch = $_POST['batch'] ?? '';
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = $_POST['remarks'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';

    if (empty($date_added) || empty($design_mix) || empty($curation_days) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $sql = "INSERT INTO sorcom_readymix_inventory 
            (date_added, design_mix_id, curation_days, batch, stocks_in, stocks_out, remarks, concrete_type) 
            VALUES ('$date_added', '$design_mix', '$curation_days', '$batch', $stocks_in, $stocks_out, '$remarks', '$concrete_type')";

        if ($conn->query($sql)) {
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
                <input type="date" name="date_added" value="<?= htmlspecialchars($date_added ?: date('Y-m-d')) ?>"
                    required>
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
                <select name="curation_days" id="curation_days" required>
                    <option value="">-- Select Days --</option>
                </select>
            </div>

            <!-- Concrete Type -->
            <div class="form-row">
                <label>Concrete Type:</label>
                <select name="concrete_type" id="concrete_type" required>
                    <option value="">-- Select --</option>
                </select>
            </div>

            <!-- Batch -->
            <div class="form-row">
                <label>Batch:</label>
                <input type="text" name="batch" value="<?= htmlspecialchars($batch) ?>">
            </div>

            <!-- Stocks In -->
            <div class="form-row">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in" value="<?= $stocks_in ?>" min="0">
            </div>

            <!-- Stocks Out -->
            <div class="form-row">
                <label>Stocks Out:</label>
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

        function updateDropdowns() {
            var selectedPSI = document.getElementById('design_mix').value;
            var curationSelect = document.getElementById('curation_days');
            var typeSelect = document.getElementById('concrete_type');

            curationSelect.innerHTML = '<option value="">-- Select Days --</option>';
            typeSelect.innerHTML = '<option value="">-- Select --</option>';

            if (selectedPSI && psiData[selectedPSI]) {
                var days = psiData[selectedPSI]['days'] || [];
                days.forEach(function (day) {
                    var option = document.createElement('option');
                    option.value = day;
                    option.textContent = day + ' days';
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

        document.addEventListener('DOMContentLoaded', function () {
            var selectedPSI = document.getElementById('design_mix').value;
            if (selectedPSI) {
                updateDropdowns();
            }
        });
    </script>

</body>

</html>