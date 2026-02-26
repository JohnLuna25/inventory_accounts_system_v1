<?php
// Include necessary files
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
include_once __DIR__ . '/ip_postHandler.php';

// Initialize error message
$error_message = "";

// --- Handle POST edit BEFORE ANY OUTPUT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $edit_id = intval($_POST['update_id']);
    $date_added = trim($_POST['date_added'] ?? date('Y-m-d'));
    $design_mix = trim($_POST['design_mix'] ?? '');
    $curation_days = intval($_POST['curation_days'] ?? 0);
    $concrete_type = trim($_POST['concrete_type'] ?? '');
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    // Basic validation
    if (empty($date_added) || empty($design_mix) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE scd_vb_list 
            SET date_added = ?, design_mix_id = ?, curation_days = ?, concrete_type = ?, stocks_in = ?, stocks_out = ?, remarks = ? 
            WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssiiisi", $date_added, $design_mix, $curation_days, $concrete_type, $stocks_in, $stocks_out, $remarks, $edit_id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: inventory_management.php");
                exit;
            } else {
                $error_message = "Error updating inventory: " . $stmt->error;
            }
        } else {
            $error_message = "Error preparing update statement: " . $conn->error;
        }
    }
}

// Close DB connection if not needed further
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="inventory_management.css">

    <style>
        /* Inline styles for simplicity; move to CSS file if preferred */
        body {
            font-family: Arial, sans-serif;
            background-color: #BFC3AA;
            margin: 0;
            padding: 0;
        }

        h2 {
            font-size: 24px;
            text-align: center;
            margin-top: 65px;
            color: black;
        }

        .inventory-controls {
            width: 95%;
            margin: auto;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .add-inventory-btn,
        .open-mixture-btn {
            padding: 10px 15px;
            background-color: #0b63c4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .add-inventory-btn:hover,
        .open-mixture-btn:hover {
            background-color: #0e4a8b;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>Ready-Mix Inventory</h2>

    <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <div class="inventory-controls">
        <a href="ip_addForm.php" class="add-inventory-btn">+ Add Inventory</a>
        <a href="mixture_inventory.php" class="open-mixture-btn">Open Mixture Inventory</a>
    </div>

    <!-- TABLE DISPLAY -->
    <?php include __DIR__ . '/ip_tableDisplay.php'; ?>

</body>

</html>