<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

if (!$conn || $conn->connect_errno) {
    die("Database connection not available: " . ($conn->connect_error ?? ''));
}

$error_message = "";
$date_added = $design_mix = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;

// --- Get inventory ID ---
if (!isset($_GET['id']))
    die("No inventory ID specified");
$id = intval($_GET['id']);

// --- Fetch unique PSI + curing days options ---
$concreteOptions = [];
$res = $conn->query("SELECT DISTINCT concrete_psi, curration_days FROM sorcom_concrete_design_masterlist");
if ($res) {
    while ($row = $res->fetch_assoc())
        $concreteOptions[] = $row;
    $res->free();
}

// --- Fetch unique concrete_type options ---
$concreteTypeOptions = [];
$res = $conn->query("SELECT DISTINCT concrete_type FROM sorcom_concrete_design_masterlist ORDER BY concrete_type");
if ($res) {
    while ($row = $res->fetch_assoc())
        $concreteTypeOptions[] = $row['concrete_type'];
    $res->free();
}

// --- Load existing inventory ---
$stmt = $conn->prepare("SELECT date_added, design_mix_id, batch, stocks_in, stocks_out, remarks, concrete_type FROM sorcom_readymix_inventory WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($date_added, $design_mix, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type);
$stmt->fetch();
$stmt->close();

// --- Handle update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $design_mix = $_POST['design_mix'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = $_POST['remarks'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $edit_id = intval($_POST['edit_id']);

    $stmt = $conn->prepare("UPDATE sorcom_readymix_inventory 
        SET date_added=?, design_mix_id=?, batch=?, stocks_in=?, stocks_out=?, remarks=?, concrete_type=? 
        WHERE id=?");
    $stmt->bind_param("sssiiisi", $date_added, $design_mix, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type, $edit_id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: inventory_management.php"); // redirect back to table display
        exit;
    } else {
        $error_message = "Error updating inventory: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Inventory</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f0f0f0;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            max-width: 700px;
            margin: auto;
            display: grid;
            grid-gap: 15px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"] {
            background: #4caf50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>

<body>

    <?php if ($error_message): ?>
        <p class="error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <h2>Edit Inventory</h2>

        <label>Date:</label>
        <input type="date" name="date_added" value="<?= htmlspecialchars($date_added) ?>" required>

        <label>Design Mix (PSI -- Days):</label>
        <select name="design_mix" required>
            <option value="">--Select--</option>
            <?php foreach ($concreteOptions as $opt):
                $display = $opt['concrete_psi'] . " -- " . $opt['curration_days']; ?>
                <option value="<?= htmlspecialchars($display) ?>" <?= $design_mix === $display ? 'selected' : '' ?>>
                    <?= htmlspecialchars($display) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Concrete Type:</label>
        <select name="concrete_type" required>
            <option value="">--Select--</option>
            <?php foreach ($concreteTypeOptions as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $concrete_type === $t ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Batch:</label>
        <input type="text" name="batch" value="<?= htmlspecialchars($batch) ?>">

        <label>Stocks In:</label>
        <input type="number" name="stocks_in" value="<?= intval($stocks_in) ?>" min="0">

        <label>Stocks Out:</label>
        <input type="number" name="stocks_out" value="<?= intval($stocks_out) ?>" min="0">

        <label>Remarks:</label>
        <textarea name="remarks"><?= htmlspecialchars($remarks) ?></textarea>

        <input type="hidden" name="edit_id" value="<?= $id ?>">

        <input type="submit" value="Save Inventory">
    </form>

</body>

</html>