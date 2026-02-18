<?php
// Include necessary files
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
include_once __DIR__ . '/ip_postHandler.php';  // Assuming this handles POST logic

// Initialize error message
$error_message = "";

// --- Handle POST edit BEFORE ANY OUTPUT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {  // Changed from 'edit_id' to 'update_id'
    $edit_id = intval($_POST['update_id']);
    $date_added = trim($_POST['date_added'] ?? date('Y-m-d'));
    $design_mix = trim($_POST['design_mix'] ?? '');
    $batch = trim($_POST['batch'] ?? '');
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');
    $concrete_type = trim($_POST['concrete_type'] ?? '');

    // Basic validation
    if (empty($date_added) || empty($design_mix) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE sorcom_readymix_inventory 
            SET date_added = ?, design_mix_id = ?, batch = ?, stocks_in = ?, stocks_out = ?, remarks = ?, concrete_type = ? 
            WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssiiisi", $date_added, $design_mix, $batch, $stocks_in, $stocks_out, $remarks, $concrete_type, $edit_id);
            if ($stmt->execute()) {
                $stmt->close();
                // Add logging for success
                error_log("Inventory updated successfully for ID: $edit_id");
                header("Location: inventory_management.php");
                exit;
            } else {
                $error_message = "Error updating inventory: " . $stmt->error;
                error_log("Update failed for ID $edit_id: " . $stmt->error);
            }
        } else {
            $error_message = "Error preparing update statement: " . $conn->error;
            error_log("Prepare failed: " . $conn->error);
        }
    }
}

// Close DB connection if not needed further
$conn->close();
?>
<?php showHeader(); ?>
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
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .add-inventory-btn:hover,
        .open-mixture-btn:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php showHeader(); ?>

    <h2>Ready-Mix Inventory</h2>

    <?php if ($error_message): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <div class="inventory-controls">
        <a href="ip_addModal.php" class="add-inventory-btn">+ Add Inventory</a>
        <a href="mixture_inventory.php" class="open-mixture-btn">Open Mixture Inventory</a>
    </div>

    <!-- TABLE DISPLAY -->
    <?php include __DIR__ . '/ip_tableDisplay.php'; ?>

    <!-- EDIT MODAL (Inline Content - Extracted from ip_editModal.php) -->
    <div id="editModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
        <div
            style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; max-width: 700px; width: 90%;">
            <!-- Insert the form content from ip_editModal.php here, without DOCTYPE/head/body -->
            <!-- Example: Copy the <form> and related PHP from ip_editModal.php -->
            <p>Modal content goes here. Replace with actual form from ip_editModal.php.</p>
            <button onclick="closeModal()">Close</button>
        </div>
    </div>

    <!-- FUNCTION SOURCE -->
    <script src="ip_functions.js"></script>
    <script>
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        // Add logic to open modal, e.g., from table rows
    </script>
</body>

</html>