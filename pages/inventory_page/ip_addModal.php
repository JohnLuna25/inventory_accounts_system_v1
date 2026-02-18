<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// --- Initialize variables ---
$date_added = $design_mix = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;
$error_message = "";

// --- Fetch unique PSI + curing days combinations ---
// --- Fetch unique PSI + curing days combinations ---
$concreteOptions = [];
$sql = "SELECT DISTINCT concrete_psi, curration_days FROM sorcom_concrete_design_masterlist";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $concreteOptions[] = $row;
    }
    $result->free();

    // --- Sort: first by concrete_psi ascending, then by curration_days in order 7, 14, 28 ---
    usort($concreteOptions, function ($a, $b) {
        if ($a['concrete_psi'] != $b['concrete_psi']) {
            return $a['concrete_psi'] <=> $b['concrete_psi'];
        }
        $order = [7, 14, 28];
        $posA = array_search((int) $a['curration_days'], $order);
        $posB = array_search((int) $b['curration_days'], $order);
        return $posA <=> $posB;
    });
} else {
    $error_message = "Could not load concrete designs: " . $conn->error;
}


// --- Fetch unique concrete_type options ---
$concreteTypeOptions = [];
$sqlType = "SELECT DISTINCT concrete_type FROM sorcom_concrete_design_masterlist ORDER BY concrete_type";
if ($resultType = $conn->query($sqlType)) {
    while ($row = $resultType->fetch_assoc()) {
        $concreteTypeOptions[] = $row['concrete_type'];
    }
    $resultType->free();
}

// --- Load existing inventory if editing ---
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT date_added, design_mix_id, batch, stocks_in, stocks_out, remarks, concrete_type 
                            FROM sorcom_readymix_inventory WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($date_added, $design_mix, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type);
    $stmt->fetch();
    $stmt->close();
}

// --- Handle form submission ---
// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = $_POST['remarks'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';

    $stmt = $conn->prepare("INSERT INTO sorcom_readymix_inventory 
        (date_added, design_mix_id, batch, stocks_in, stocks_out, remarks, concrete_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiiis", $date_added, $design_mix, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type);

    if ($stmt->execute()) {
        $stmt->close();
        // ✅ Redirect to inventory_management.php instead of ip_tableDisplay.php
        header("Location: inventory_management.php");
        exit;
    } else {
        $error_message = "Error saving inventory: " . $stmt->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ready-Mix Inventory Form</title>
</head>

<body
    style="font-family: Arial, sans-serif; background-color: #bfc3aa; display: flex; justify-content: center; padding-top: 50px; margin: 0;">

    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST"
        style="background-color: #fff; padding: 25px 30px; border-radius: 8px; display: grid; grid-template-columns: 1fr 1fr; column-gap: 20px; row-gap: 15px; width: 800px; max-width: 90%;">
        <h2 style="grid-column: 1 / -1; text-align: center; margin-bottom: 20px;">
            <?= isset($_GET['id']) ? "Edit Inventory" : "Add Inventory" ?>
        </h2>

        <!-- Date -->
        <div>
            <label for="date_added">Date:</label>
            <input type="date" id="date_added" name="date_added" required
                value="<?= htmlspecialchars($date_added ?: date('Y-m-d')) ?>" style="width: 100%; padding: 8px;">
        </div>

        <!-- Design Mix Dropdown -->
        <div>
            <label for="design_mix">Design Mix (PSI -- Days):</label>
            <select id="design_mix" name="design_mix" required style="width: 100%; padding: 8px;">
                <option value="">--Select PSI and Days--</option>
                <?php foreach ($concreteOptions as $opt):
                    $display = $opt['concrete_psi'] . "  -- " . $opt['curration_days'] . " ";
                    ?>
                    <option value="<?= htmlspecialchars($display) ?>" <?= $design_mix === $display ? 'selected' : '' ?>>
                        <?= htmlspecialchars($display) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <!-- Concrete Type Dropdown -->
        <div>
            <label for="concrete_type">Concrete Type:</label>
            <select id="concrete_type" name="concrete_type" required style="width: 100%; padding: 8px;">
                <option value="">--Select Type--</option>
                <?php foreach ($concreteTypeOptions as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= $concrete_type === $type ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Batch -->
        <div>
            <label for="batch">Batch:</label>
            <input type="text" id="batch" name="batch" value="<?= htmlspecialchars($batch) ?>"
                style="width: 100%; padding: 8px;">
        </div>

        <!-- Stocks In -->
        <div>
            <label for="stocks_in">Stocks In:</label>
            <input type="number" id="stocks_in" name="stocks_in" min="0" value="<?= intval($stocks_in) ?>"
                style="width: 100%; padding: 8px;">
        </div>

        <!-- Stocks Out -->
        <div>
            <label for="stocks_out">Stocks Out:</label>
            <input type="number" id="stocks_out" name="stocks_out" min="0" value="<?= intval($stocks_out) ?>"
                style="width: 100%; padding: 8px;">
        </div>

        <!-- Remarks -->
        <div style="grid-column: 1 / -1;">
            <label for="remarks">Remarks:</label>
            <textarea id="remarks" name="remarks"
                style="width: 100%; padding: 8px;"><?= htmlspecialchars($remarks) ?></textarea>
        </div>

        <?php if (isset($_GET['id'])): ?>
            <input type="hidden" name="edit_id" value="<?= intval($_GET['id']) ?>">
        <?php endif; ?>

        <!-- Submit Buttons -->
        <div style="grid-column: 1 / -1; display: flex; justify-content: center; margin-top: 20px;">
            <input type="submit" value="Save Inventory"
                style="background-color: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 5px; margin-right: 10px; cursor: pointer;">
            <input type="button" value="Cancel" onclick="window.history.back();"
                style="background-color: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
        </div>
    </form>

</body>

</html>