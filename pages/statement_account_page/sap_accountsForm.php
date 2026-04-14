<?php
ob_start();

require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

showHeader();

// --- Function to generate unique sap_id ---
function generateUniqueSapId($conn)
{
    do {
        $random_number = str_pad(rand(700000, 999999), 6, '0', STR_PAD_LEFT);
        $sap_id = 'src-sap-' . $random_number;
        $check = $conn->query("SELECT sap_id FROM statement_of_account WHERE sap_id = '$sap_id'");
    } while ($check && $check->num_rows > 0);
    return $sap_id;
}

// --- Initialize variables ---
$order_date = $delivery_date = $customer_name = $full_address = $concrete_psi = $curation_days = $concrete_type = $order_status = "";
$cubic_meters = 0;
$error_message = "";
$sap_id = "";

// --- Status labels mapping ---
$statusLabels = [
    'Pending' => 'Pending',
    'In Progress' => 'In Progress',
    'Delivered' => 'Delivered',
    'Done' => 'Done'
];

// --- Fetch all concrete designs grouped by PSI ---
$concreteData = [];
$sql = "SELECT vb_concrete_psi, vb_curation_days, vb_concrete_type FROM scd_vb_list ORDER BY vb_concrete_psi ASC";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $psi = $row['vb_concrete_psi'];
        $days = $row['vb_curation_days'];
        $type = $row['vb_concrete_type'];

        // Group by PSI
        if (!isset($concreteData[$psi])) {
            $concreteData[$psi] = [
                'days' => [],
                'types' => []
            ];
        }

        // Add unique curation days
        if (!in_array($days, $concreteData[$psi]['days'])) {
            $concreteData[$psi]['days'][] = $days;
        }

        // Add unique concrete types
        if (!in_array($type, $concreteData[$psi]['types'])) {
            $concreteData[$psi]['types'][] = $type;
        }
    }
    $result->free();
} else {
    $error_message = "Could not load concrete designs: " . $conn->error;
}

// Get unique PSI values
$psiOptions = array_keys($concreteData);
sort($psiOptions);

// --- Load existing data if editing ---
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT sap_id, order_date, delivery_date, customer_name, full_address, concrete_psi, curation_days, concrete_type, cubic_meters, sap_concrete_price, order_status FROM statement_of_account WHERE sap_id=?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->bind_result($sap_id, $order_date, $delivery_date, $customer_name, $full_address, $concrete_psi, $curation_days, $concrete_type, $cubic_meters, $sap_concrete_price, $order_status);
    $stmt->fetch();
    $stmt->close();
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_date = $_POST['order_date'] ?? '';
    $delivery_date = $_POST['delivery_date'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $full_address = $_POST['full_address'] ?? '';
    $concrete_psi = $_POST['concrete_psi'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $cubic_meters = floatval($_POST['cubic_meters'] ?? 0);
    $sap_concrete_price = $_POST['sap_concrete_price'];
    $order_status = $_POST['order_status'] ?? '';

    if (!empty($_POST['edit_id'])) {
        // Update existing order
        $id = $_POST['edit_id'];
        $stmt = $conn->prepare("UPDATE statement_of_account 
    SET order_date=?, delivery_date=?, customer_name=?, full_address=?, concrete_psi=?, curation_days=?, concrete_type=?, cubic_meters=?, sap_concrete_price=?, order_status=? 
    WHERE sap_id=?");

        $stmt->bind_param(
            "sssssssssss",
            $order_date,
            $delivery_date,
            $customer_name,
            $full_address,
            $concrete_psi,
            $curation_days,
            $concrete_type,
            $cubic_meters,
            $sap_concrete_price,
            $order_status,
            $id
        );
    } else {
        // Generate unique sap_id for new order
        $sap_id = generateUniqueSapId($conn);

        // Insert new order with sap_id
        $stmt = $conn->prepare("INSERT INTO statement_of_account 
    (sap_id, order_date, delivery_date, customer_name, full_address, concrete_psi, curation_days, concrete_type, cubic_meters, sap_concrete_price, order_status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssss",
            $sap_id,
            $order_date,
            $delivery_date,
            $customer_name,
            $full_address,
            $concrete_psi,
            $curation_days,
            $concrete_type,
            $cubic_meters,
            $sap_concrete_price,
            $order_status
        );
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
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
        }

        .main-content {
            padding-top: 70px;
        }

        .form-container {
            background: white;
            margin: 15px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-weight: 300;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-row label {
            width: 150px;
            font-weight: bold;
            color: #333;
        }

        .form-row input,
        .form-row select {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-submit {
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-submit:hover {
            background: #45a049;
        }

        .btn-cancel {
            padding: 12px 30px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-cancel:hover {
            background: #d32f2f;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }

        @media (max-width: 576px) {
            .main-content {
                padding-top: 200px;
            }

            .form-container {
                margin: 10px;
                padding: 20px;
            }

            .form-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row label {
                width: 100%;
                margin-bottom: 5px;
            }

            .form-row input,
            .form-row select {
                width: 100%;
            }

            .form-buttons {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
                text-align: center;
            }


        }
    </style>
</head>

<body>

    <div class="main-content">
        <div class="form-container">
            <h2>Concrete Order Form</h2>

            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <form method="POST" id="concreteForm">
                <?php if (isset($_GET['id'])): ?>
                    <div class="form-row">
                        <label>Order ID:</label>
                        <input type="text" value="<?= htmlspecialchars($sap_id ?? '') ?>" readonly
                            style="background-color: #f5f5f5;">
                    </div>
                <?php endif; ?>

                <div class="form-row">
                    <label for="order_date">Date of Order:</label>
                    <input type="date" id="order_date" name="order_date" required
                        value="<?= htmlspecialchars($order_date) ?>">
                </div>

                <div class="form-row">
                    <label for="delivery_date">Delivery Date:</label>
                    <input type="date" id="delivery_date" name="delivery_date" required
                        value="<?= htmlspecialchars($delivery_date) ?>">
                </div>

                <div class="form-row">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" name="customer_name" placeholder="Enter full name" required
                        value="<?= htmlspecialchars($customer_name) ?>">
                </div>

                <div class="form-row">
                    <label for="full_address">Full Address:</label>
                    <input type="text" id="full_address" name="full_address"
                        placeholder="House/Blk/Lt, St., Subd., Brgy., City/Municipality, Prov." required
                        value="<?= htmlspecialchars($full_address) ?>">
                </div>

                <div class="form-row">
                    <label for="concrete_psi">Concrete Design (PSI):</label>
                    <select id="concrete_psi" name="concrete_psi" required>
                        <option value="">--Select PSI--</option>
                        <?php foreach ($psiOptions as $psi): ?>
                            <option value="<?= htmlspecialchars($psi) ?>" <?= $concrete_psi === $psi ? 'selected' : '' ?>>
                                <?= htmlspecialchars($psi) ?> PSI
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="curation_days">Curation Days:</label>
                    <select id="curation_days" name="curation_days" required>
                        <option value="">--Select Curation Days--</option>
                        <?php if (!empty($concrete_psi) && isset($concreteData[$concrete_psi])): ?>
                            <?php foreach ($concreteData[$concrete_psi]['days'] as $day): ?>
                                <option value="<?= htmlspecialchars($day) ?>" <?= $curation_days === $day ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($day) ?> days
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="concrete_type">Concrete Type:</label>
                    <select id="concrete_type" name="concrete_type" required>
                        <option value="">--Select Concrete Type--</option>
                        <?php if (!empty($concrete_psi) && isset($concreteData[$concrete_psi])): ?>
                            <?php foreach ($concreteData[$concrete_psi]['types'] as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>" <?= $concrete_type === $type ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label for="cubic_meters">Cubic Meters:</label>
                    <input type="number" id="cubic_meters" name="cubic_meters" min="0" step="0.1"
                        placeholder="Enter quantity" required value="<?= htmlspecialchars($cubic_meters) ?>">
                </div>

                <div class="form-row">
                    <label for="sap_concrete_price">Concrete Price:</label>
                    <input type="number" id="sap_concrete_price" name="sap_concrete_price" step="0.01"
                        value="<?= htmlspecialchars($sap_concrete_price ?? '') ?>">
                </div>

                <div class="form-row">
                    <label for="order_status">Order Status:</label>
                    <select id="order_status" name="order_status" required>
                        <option value="">--Select Status--</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $order_status === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($_GET['id'])): ?>
                    <input type="hidden" name="edit_id" value="<?= htmlspecialchars($_GET['id']) ?>">
                <?php endif; ?>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Save Order</button>
                    <a href="sap_accountsManagement.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Dynamic dropdown for Curation Days and Concrete Type based on PSI selection
        const concreteData = <?php echo json_encode($concreteData); ?>;

        document.getElementById('concrete_psi').addEventListener('change', function () {
            const selectedPsi = this.value;
            const daysSelect = document.getElementById('curation_days');
            const typeSelect = document.getElementById('concrete_type');

            // Clear existing options
            daysSelect.innerHTML = '<option value="">--Select Curation Days--</option>';
            typeSelect.innerHTML = '<option value="">--Select Concrete Type--</option>';

            if (selectedPsi && concreteData[selectedPsi]) {
                // Populate Curation Days
                concreteData[selectedPsi].days.forEach(function (day) {
                    const option = document.createElement('option');
                    option.value = day;
                    option.textContent = day;
                    daysSelect.appendChild(option);
                });

                // Populate Concrete Types
                concreteData[selectedPsi].types.forEach(function (type) {
                    const option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    typeSelect.appendChild(option);
                });
            }
        });
    </script>

</body>

</html>