<?php
// ip_editForm.php - Full page edit form

require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

if (!$conn || $conn->connect_errno) {
    die("Database connection not available: " . ($conn->connect_error ?? 'Unknown error'));
}

// Initialize variables
$error_message = "";
$date_added = $design_mix = $curation_days = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;

if (!isset($_GET['id'])) {
    header("Location: inventory_management.php");
    exit;
}

$id = intval($_GET['id']);

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

// Load existing inventory data
$stmt = $conn->prepare("SELECT date_added, design_mix_id, curation_days, batch, stocks_in, stocks_out, remarks, concrete_type FROM sorcom_readymix_inventory WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($date_added, $design_mix, $curation_days, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type);
    $stmt->fetch();
    $stmt->close();
} else {
    $error_message = "Error preparing statement: " . $conn->error;
}

$conn->close();

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['update_id'] ?? $_GET['id'] ?? 0);

    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = $_POST['remarks'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';

    $conn2 = new mysqli('localhost', 'root', '', 'sorcom_db');

    $sql = "UPDATE sorcom_readymix_inventory 
        SET date_added='$date_added', 
            design_mix_id='$design_mix', 
            curation_days='$curation_days', 
            batch='$batch', 
            stocks_in=$stocks_in, 
            stocks_out=$stocks_out, 
            remarks='$remarks', 
            concrete_type='$concrete_type' 
        WHERE id=$id";

    $conn2->query($sql);
    $conn2->close();

    header("Location: inventory_management.php");
    exit;
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
                <select name="curation_days" id="curation_days" required>
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
                <select name="concrete_type" id="concrete_type" required>
                    <option value="">-- Select --</option>
                    <?php if (!empty($concrete_type)): ?>
                        <option value="<?= htmlspecialchars($concrete_type) ?>" selected>
                            <?= htmlspecialchars($concrete_type) ?>
                        </option>
                    <?php endif; ?>
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
        var selectedPSI = "<?php echo htmlspecialchars($design_mix); ?>";
        var selectedDays = "<?php echo htmlspecialchars($curation_days); ?>";
        var selectedType = "<?php echo htmlspecialchars($concrete_type); ?>";

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

        // Initialize dropdowns on page load
        document.addEventListener('DOMContentLoaded', function () {
            if (selectedPSI && psiData[selectedPSI]) {
                updateDropdowns();
                // Set selected values after populating
                var curationSelect = document.getElementById('curation_days');
                var typeSelect = document.getElementById('concrete_type');

                if (selectedDays) {
                    curationSelect.value = selectedDays;
                }
                if (selectedType) {
                    typeSelect.value = selectedType;
                }
            }
        });
    </script>

</body>

</html>