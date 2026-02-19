<?php
// ip_editForm.php - Full page edit form

require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

if (!$conn || $conn->connect_errno) {
    die("Database connection not available: " . ($conn->connect_error ?? 'Unknown error'));
}

// Initialize variables
$error_message = "";
$date_added = $design_mix = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;

// Check for ID using GET
if (!isset($_GET['id'])) {
    header("Location: inventory_management.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch unique PSI + curing days options
$concreteOptions = [];
$result = $conn->query("SELECT DISTINCT concrete_psi, curration_days FROM sorcom_concrete_design_masterlist");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $concreteOptions[] = $row;
    }
    $result->free();
}

// Fetch unique concrete_type options
$concreteTypeOptions = [];
$result = $conn->query("SELECT DISTINCT concrete_type FROM sorcom_concrete_design_masterlist ORDER BY concrete_type");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $concreteTypeOptions[] = $row['concrete_type'];
    }
    $result->free();
}

// Load existing inventory data
$stmt = $conn->prepare("SELECT date_added, design_mix_id, batch, stocks_in, stocks_out, remarks, concrete_type FROM sorcom_readymix_inventory WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($date_added, $design_mix, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type);
    $stmt->fetch();
    $stmt->close();
} else {
    $error_message = "Error preparing statement: " . $conn->error;
}

// Close database connection
$conn->close();

// Handle form submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get ID from hidden field OR from GET
    $id = intval($_POST['update_id'] ?? $_GET['id'] ?? 0);

    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = $_POST['remarks'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';

    $conn2 = new mysqli('localhost', 'root', '', 'sorcom_db');

    // Direct query
    $sql = "UPDATE sorcom_readymix_inventory 
        SET date_added='$date_added', 
            design_mix_id='$design_mix', 
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
        <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <!-- Form with modal-content class for CSS -->
    <div class="modal-content">
        <form method="POST" action="ip_editForm.php?id=<?= $id ?>">
            <h2>Edit Inventory</h2>

            <input type="hidden" name="update_id" value="<?= $id ?>">

            <!-- Date -->
            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="date_added" value="<?= htmlspecialchars($date_added) ?>" required>
            </div>

            <!-- Design Mix -->
            <div class="form-row">
                <label>Design Mix:</label>
                <select name="design_mix" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($concreteOptions as $opt):
                        $display = $opt['concrete_psi'] . " -- " . $opt['curration_days']; ?>
                        <option value="<?= htmlspecialchars($display) ?>" <?= $design_mix === $display ? 'selected' : '' ?>>
                            <?= htmlspecialchars($display) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Concrete Type -->
            <div class="form-row">
                <label>Concrete Type:</label>
                <select name="concrete_type" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($concreteTypeOptions as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $concrete_type === $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
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

</body>

</html>