<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

// --- Initialize variables ---
$order_date = $delivery_date = $customer_name = $full_address = $concrete_type = $order_status = "";
$cubic_meters = 0;
$error_message = "";

// --- Status labels mapping ---
$statusLabels = [
    'Pending' => 'Pending',
    'In Progress' => 'In Progress',
    'Delivered' => 'Delivered',
    'Done' => 'Done'
];

// --- Fetch concrete PSI options from database ---
$concreteOptions = [];
$sql = "SELECT concrete_psi, curration_days FROM scd_vb_list";
$concreteOptions = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $concreteOptions[] = $row;
    }
    $result->free();
} else {
    $error_message = "Could not load concrete designs: " . $conn->error;
}

// --- Fetch unique description options from database ---
$descriptionOptions = [];
$sqlDesc = "SELECT DISTINCT concrete_type FROM scd_vb_list";
if ($resultDesc = $conn->query($sqlDesc)) {
    while ($row = $resultDesc->fetch_assoc()) {
        $descriptionOptions[] = $row['concrete_type'];
    }
    $resultDesc->free();
} else {
    $error_message = "Could not load description options: " . $conn->error;
}

// --- Load existing data if editing ---
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT order_date, delivery_date, customer_name, full_address, concrete_type, cubic_meters, order_status FROM statement_of_account WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($order_date, $delivery_date, $customer_name, $full_address, $concrete_type, $cubic_meters, $order_status);
    $stmt->fetch();
    $stmt->close();
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_date = $_POST['order_date'] ?? '';
    $delivery_date = $_POST['delivery_date'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $full_address = $_POST['full_address'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $cubic_meters = floatval($_POST['cubic_meters'] ?? 0);
    $order_status = $_POST['order_status'] ?? '';

    if (!empty($_POST['edit_id'])) {
        $id = intval($_POST['edit_id']);
        $stmt = $conn->prepare("UPDATE statement_of_account 
            SET order_date=?, delivery_date=?, customer_name=?, full_address=?, concrete_type=?, cubic_meters=?, order_status=? 
            WHERE id=?");
        $stmt->bind_param("sssssdsi", $order_date, $delivery_date, $customer_name, $full_address, $concrete_type, $cubic_meters, $order_status, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO statement_of_account 
            (order_date, delivery_date, customer_name, full_address, concrete_type, cubic_meters, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssds", $order_date, $delivery_date, $customer_name, $full_address, $concrete_type, $cubic_meters, $order_status);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: sap_accountsManagement.php?success=1");
        exit;
    } else {
        $error_message = "Error saving order: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Order Form</title>
</head>

<body
    style="font-family: Arial, sans-serif; background-color: #bfc3aa; display: flex; justify-content: center; padding-top: 50px; margin: 0;">

    <?php if (!empty($error_message)): ?>
        <p style="color: red; text-align: center;"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form method="POST"
        style="background-color: #fff; padding: 25px 30px; border-radius: 8px; display: grid; grid-template-columns: 1fr 1fr; column-gap: 20px; row-gap: 15px; width: 800px; max-width: 90%;">
        <h2 style="grid-column: 1 / -1; text-align: center; margin-bottom: 20px;">Concrete Order Form</h2>

        <div>
            <label for="order_date">Date of Order:</label>
            <input type="date" id="order_date" name="order_date" required value="<?= htmlspecialchars($order_date) ?>"
                style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="delivery_date">Delivery Date:</label>
            <input type="date" id="delivery_date" name="delivery_date" required
                value="<?= htmlspecialchars($delivery_date) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" placeholder="Enter full name" required
                value="<?= htmlspecialchars($customer_name) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="full_address">Full Address:</label>
            <input type="text" id="full_address" name="full_address"
                placeholder="House/Blk/Lt, St., Subd., Brgy., City/Municipality, Prov." required
                value="<?= htmlspecialchars($full_address) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="concrete_type">Concrete Design (PSI):</label>
            <select id="concrete_type" name="concrete_type" required style="width: 100%; padding: 8px;">
                <option value="">--Select Concrete PSI--</option>
                <?php foreach ($concreteOptions as $opt):
                    $display = $opt['concrete_psi'] . " -- " . $opt['curration_days'] . " days";
                    ?>
                    <option value="<?= htmlspecialchars($display) ?>" <?= $concrete_type === $display ? 'selected' : '' ?>>
                        <?= htmlspecialchars($display) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="cubic_meters">Cubic Meters:</label>
            <input type="number" id="cubic_meters" name="cubic_meters" min="0" step="0.1" placeholder="Enter quantity"
                required value="<?= htmlspecialchars($cubic_meters) ?>" style="width: 100%; padding: 8px;">
        </div>

        <div>
            <label for="description">Description:</label>
            <select id="description" name="description" required style="width: 100%; padding: 8px;">
                <option value="">--Select Description--</option>
                <?php foreach ($descriptionOptions as $desc): ?>
                    <option value="<?= htmlspecialchars($desc) ?>" <?= (isset($description) && $description === $desc) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($desc) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="order_status">Order Status:</label>
            <select id="order_status" name="order_status" required style="width: 100%; padding: 8px;">
                <option value="">--Select Status--</option>
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $order_status === $key ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($_GET['id'])): ?>
            <input type="hidden" name="edit_id" value="<?= intval($_GET['id']) ?>">
        <?php endif; ?>

        <div style="grid-column: 1 / -1; display: flex; justify-content: center; margin-top: 20px;">
            <input type="submit" value="Save Order"
                style="background-color: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 5px; margin-right: 10px; cursor: pointer;">
            <input type="button" value="Cancel" onclick="window.history.back();"
                style="background-color: #f44336; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
        </div>
    </form>

</body>

</html>