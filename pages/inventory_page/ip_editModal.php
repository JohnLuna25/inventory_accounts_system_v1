<?php
// ip_editModal.php - Modal content only (no full HTML page)

// Include database connection (if needed for data fetching)
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

if (!$conn || $conn->connect_errno) {
    die("Database connection not available: " . ($conn->connect_error ?? 'Unknown error'));
}

// Initialize variables
$error_message = "";
$date_added = $design_mix = $batch = $remarks = $concrete_type = "";
$stocks_in = $stocks_out = 0;

// Redirect if no ID from POST (this must happen before any output)
if (!isset($_POST['id'])) {
    header("Location: inventory_management.php");
    exit;
}

$id = intval($_POST['id']);

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
?>

<!-- Modal Content Only (No full HTML structure) -->
<div id="editModal"
    style="display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000;">
    <div class="modal-content">
        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="POST" action="inventory_management.php">
            <h2>Edit Inventory</h2>

            <div class="form-row">
                <label for="date_added">Date <span style="color: red;">*</span>:</label>
                <input type="date" id="date_added" name="date_added"
                    value="<?php echo htmlspecialchars($date_added); ?>" required>
            </div>

            <div class="form-row">
                <label for="design_mix">Design Mix (PSI -- Days) <span style="color: red;">*</span>:</label>
                <select id="design_mix" name="design_mix" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($concreteOptions as $opt):
                        $display = $opt['concrete_psi'] . " -- " . $opt['curration_days'];
                        $selected = ($design_mix === $display) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($display) . "' $selected>" . htmlspecialchars($display) . "</option>";
                    endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label for="concrete_type">Concrete Type <span style="color: red;">*</span>:</label>
                <select id="concrete_type" name="concrete_type" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($concreteTypeOptions as $type):
                        $selected = ($concrete_type === $type) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($type) . "' $selected>" . htmlspecialchars($type) . "</option>";
                    endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label for="batch">Batch:</label>
                <input type="text" id="batch" name="batch" value="<?php echo htmlspecialchars($batch); ?>">
            </div>

            <div class="form-row">
                <label for="stocks_in">Stocks In:</label>
                <input type="number" id="stocks_in" name="stocks_in" value="<?php echo intval($stocks_in); ?>" min="0">
            </div>

            <div class="form-row">
                <label for="stocks_out">Stocks Out:</label>
                <input type="number" id="stocks_out" name="stocks_out" value="<?php echo intval($stocks_out); ?>"
                    min="0">
            </div>

            <div class="form-row">
                <label for="remarks">Remarks:</label>
                <textarea id="remarks" name="remarks"><?php echo htmlspecialchars($remarks); ?></textarea>
            </div>

            <input type="hidden" name="update_id" value="<?php echo $id; ?>">

            <!-- Wrap buttons in a row for centering -->
            <div class="button-row">
                <button type="submit">Save Inventory</button>
                <button type="button" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
</script>