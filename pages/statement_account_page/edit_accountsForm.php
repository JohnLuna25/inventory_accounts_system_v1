<?php
ob_start();

require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

$error_message = "";
$success_message = "";

// --- Get sap_id from URL ---
$sap_id = trim($_GET['sap_id'] ?? '');

if (empty($sap_id)) {
    die("❌ No order ID provided.");
}

// --- Load existing order ---
$stmt = $conn->prepare("
    SELECT sap_id, order_date, delivery_date, customer_name, full_address,
           concrete_psi, curation_days, concrete_type, sap_units, sap_concrete_price, order_status
    FROM statement_of_account
    WHERE sap_id = ?
");
$stmt->bind_param("s", $sap_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("❌ Order not found.");
}

// --- Assign loaded values ---
$order_date = $order['order_date'];
$delivery_date = $order['delivery_date'];
$customer_name = $order['customer_name'];
$full_address = $order['full_address'];
$concrete_psi = $order['concrete_psi'];
$curation_days = $order['curation_days'];
$concrete_type = $order['concrete_type'];
$sap_units = $order['sap_units'];
$sap_concrete_price = $order['sap_concrete_price'];
$order_status = $order['order_status'];

// --- Status options ---
$statusLabels = [
    'Pending' => 'Pending',
    'In Transit' => 'In Transit',
    'Done' => 'Done'
];

// ========== POST HANDLER ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_date = trim($_POST['delivery_date'] ?? '');
    $order_status = trim($_POST['order_status'] ?? '');

    if (empty($delivery_date)) {
        $error_message = "❌ Please provide a delivery date.";
    } elseif (!array_key_exists($order_status, $statusLabels)) {
        $error_message = "❌ Invalid status selected.";
    } else {
        $update = $conn->prepare("
            UPDATE statement_of_account
            SET delivery_date = ?,
                order_status  = ?
            WHERE sap_id = ?
        ");
        $update->bind_param("sss", $delivery_date, $order_status, $sap_id);

        if ($update->execute()) {
            $update->close();
            $success_msg = "✅ Order updated successfully!";
            header("Location: sap_accountsManagement.php?success=1&msg=" . urlencode($success_msg));
            exit;
        } else {
            $error_message = "❌ Update failed: " . $update->error;
            $update->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Concrete Order</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="edit_accountsForm.css">
</head>

<body>
    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2>Update Concrete Order</h2>
                <div class="sap-id-badge">
                    <?= htmlspecialchars($sap_id) ?>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" id="editForm">

                <!-- SECTION 1: DATES -->
                <div class="form-section">
                    <h3>Date of Order</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Date of Order</label>
                            <input type="date" value="<?= htmlspecialchars($order_date) ?>" class="readonly-input"
                                disabled>
                        </div>
                        <div class="form-group">
                            <label>Date of Delivery <span style="color:#f44336;">*</span></label>
                            <input type="date" id="delivery_date" name="delivery_date" required
                                value="<?= htmlspecialchars($delivery_date) ?>">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PERSONAL DETAILS -->
                <div class="form-section">
                    <h3>Personal Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <input type="text" value="<?= htmlspecialchars($customer_name) ?>" class="readonly-input"
                                disabled>
                        </div>
                        <div class="form-group">
                            <label>Full Address</label>
                            <input type="text" value="<?= htmlspecialchars($full_address) ?>" class="readonly-input"
                                disabled>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: ORDER DETAILS -->
                <div class="form-section">
                    <h3>Order Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Concrete Design (PSI)</label>
                            <input type="text" value="<?= htmlspecialchars($concrete_psi) ?>" class="readonly-input"
                                disabled>
                        </div>
                        <div class="form-group">
                            <label>Curation Days</label>
                            <input type="text" value="<?= htmlspecialchars($curation_days) ?> days"
                                class="readonly-input" disabled>
                        </div>
                        <div class="form-group">
                            <label>Concrete Type</label>
                            <input type="text" value="<?= htmlspecialchars($concrete_type) ?>" class="readonly-input"
                                disabled>
                        </div>
                        <div class="form-group">
                            <label>Units</label>
                            <input type="text" value="<?= htmlspecialchars($sap_units) ?>" class="readonly-input"
                                disabled>
                        </div>
                        <div class="form-group">
                            <label>Concrete Price (₱)</label>
                            <input type="text" value="<?= number_format(floatval($sap_concrete_price), 2) ?>"
                                class="readonly-input" disabled>
                        </div>
                        <div class="form-group">
                            <label>Status <span style="color:#f44336;">*</span></label>
                            <select id="order_status" name="order_status" required>
                                <?php foreach ($statusLabels as $value => $label): ?>
                                    <option value="<?= htmlspecialchars($value) ?>" <?= $order_status === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Update Order</button>
                    <a href="sap_accountsManagement.php" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('editForm');

            form.addEventListener('submit', function (e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    this.reportValidity();
                }
            });
        });
    </script>
</body>

</html>