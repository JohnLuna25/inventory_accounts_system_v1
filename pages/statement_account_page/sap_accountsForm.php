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

// --- Initialize variables for NEW orders ---
$today = date('Y-m-d');
$order_date = $today;
$delivery_date = "";
$last_name = "";
$first_name = "";
$middle_name = "";
$suffix = "";
$house_block = "";
$street = "";
$barangay = "";
$city = "";
$province = "";
$region = "";
$full_address = "";
$concrete_psi = "";
$curation_days = "";
$concrete_type = "";
$order_status = "Pending";
$units = 0;
$sap_concrete_price = "";
$error_message = "";

// --- Status labels mapping ---
$statusLabels = [
    'Pending' => 'Pending',
    'In Transit' => 'In Transit',
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

        if (!isset($concreteData[$psi])) {
            $concreteData[$psi] = [
                'days' => [],
                'types' => []
            ];
        }

        if (!in_array($days, $concreteData[$psi]['days'])) {
            $concreteData[$psi]['days'][] = $days;
        }

        if (!in_array($type, $concreteData[$psi]['types'])) {
            $concreteData[$psi]['types'][] = $type;
        }
    }
    $result->free();
} else {
    $error_message = "Could not load concrete designs: " . $conn->error;
}

$psiOptions = array_keys($concreteData);
sort($psiOptions);

// ========== INVENTORY UPDATE FUNCTION ==========
function checkAndUpdateInventory($conn, $concrete_psi, $curation_days, $concrete_type, $units)
{
    $stmt = $conn->prepare("
        SELECT id, stocks_in, stocks_out, batch, current_stock_batch 
        FROM sorcom_readymix_inventory 
        WHERE design_mix_id = ? AND curation_days = ? AND concrete_type = ?
        ORDER BY date_added DESC LIMIT 1
    ");
    $stmt->bind_param("sss", $concrete_psi, $curation_days, $concrete_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $inventory = $result->fetch_assoc();
    $stmt->close();

    if (!$inventory) {
        return ['success' => false, 'message' => 'Inventory record not found'];
    }

    $current_stock = intval($inventory['stocks_in']) - intval($inventory['stocks_out']);

    if ($units > 0 && $units > $current_stock) {
        return ['success' => false, 'message' => "Only {$current_stock} units available"];
    }

    $batch_size = floatval($inventory['batch']);
    $total_kgs_change = $units * $batch_size;
    $new_current_stock = floatval($inventory['current_stock_batch']) - $total_kgs_change;
    $new_current_stock = max(0, $new_current_stock);

    $update_stmt = $conn->prepare("
        UPDATE sorcom_readymix_inventory SET
            stocks_in           = stocks_in - ?,
            stocks_out          = stocks_out + ?,
            current_stock_batch = ?
        WHERE id = ?
    ");
    $update_stmt->bind_param("iidi", $units, $units, $new_current_stock, $inventory['id']);
    $update_success = $update_stmt->execute();
    $update_stmt->close();

    if ($update_success) {
        return ['success' => true, 'deducted_units' => $units, 'deducted_kgs' => $total_kgs_change];
    }
    return ['success' => false, 'message' => 'Inventory update failed'];
}

// ========== POST HANDLER (NEW ORDER ONLY) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_date = $_POST['order_date'] ?? '';
    $delivery_date = $_POST['delivery_date'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $suffix = $_POST['suffix'] ?? '';

    $customer_name = trim("{$last_name} {$first_name} {$middle_name} {$suffix}");
    $customer_name = preg_replace('/\s+/', ' ', $customer_name);

    $house_block = $_POST['house_block'] ?? '';
    $street = $_POST['street'] ?? '';
    $barangay = $_POST['barangay'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';
    $region = $_POST['region'] ?? '';
    $full_address = trim("{$house_block}, {$street} || {$barangay} || {$city} || {$province} || {$region}");
    $full_address = preg_replace('/\s+/', ' ', $full_address);

    $concrete_psi = $_POST['concrete_psi'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $concrete_type = $_POST['concrete_type'] ?? '';
    $sap_units = intval($_POST['sap_units'] ?? 0);
    $sap_concrete_price = floatval($_POST['sap_concrete_price'] ?? 0);
    $order_status = $_POST['order_status'] ?? 'Pending';

    // --- Validate concrete type first; stop here if invalid ---
    if (empty($concrete_type) || $concrete_type === '0') {
        $error_message = "❌ Please select a valid Concrete Type";
    } elseif ($sap_units <= 0 || empty($concrete_psi) || empty($curation_days)) {
        $error_message = "❌ Please select concrete mix and enter units";
    } else {
        // --- All inputs valid: check & update inventory ---
        $inventory_result = checkAndUpdateInventory($conn, $concrete_psi, $curation_days, $concrete_type, $sap_units);

        if (!$inventory_result['success']) {
            $error_message = "❌ INVENTORY ERROR: " . $inventory_result['message'];
        } else {
            $sap_id = generateUniqueSapId($conn);

            $stmt = $conn->prepare("
                INSERT INTO statement_of_account 
                    (sap_id, order_date, delivery_date, customer_name, full_address, 
                     concrete_psi, curation_days, concrete_type, sap_units, sap_concrete_price, order_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            // FIX: correct type string
            // s  s           s              s               s             s             s              s              i          d                   s
            // id order_date  delivery_date  customer_name   full_address  concrete_psi  curation_days  concrete_type  sap_units  sap_concrete_price  order_status
            $stmt->bind_param(
                "ssssssssids",
                $sap_id,
                $order_date,
                $delivery_date,
                $customer_name,
                $full_address,
                $concrete_psi,
                $curation_days,
                $concrete_type,
                $sap_units,
                $sap_concrete_price,
                $order_status
            );

            if ($stmt->execute()) {
                $stmt->close();
                $success_msg = "✅ Order saved! Inventory deducted: "
                    . $inventory_result['deducted_units']
                    . " units (" . number_format($inventory_result['deducted_kgs'], 1) . " kgs)";
                header("Location: sap_accountsManagement.php?success=1&msg=" . urlencode($success_msg));
                exit;
            } else {
                $error_message = "❌ Order failed: " . $stmt->error;
                $stmt->close();
            }
        }
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
    <link rel="stylesheet" href="sap_accountsForm.css">
</head>

<body>
    <div class="main-content">
        <div class="form-container">
            <div class="form-header">
                <h2>Concrete Order</h2>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" id="concreteForm">

                <!-- SECTION 1: DATES -->
                <div class="form-section">
                    <h3>Date of Order</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Date of Order</label>
                            <input type="date" id="order_date" name="order_date" required
                                value="<?= htmlspecialchars($order_date) ?>">
                        </div>
                        <div class="form-group">
                            <label>Date of Delivery</label>
                            <input type="date" id="delivery_date" name="delivery_date" required
                                value="<?= htmlspecialchars($delivery_date) ?>">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: PERSONAL DETAILS -->
                <div class="form-section">
                    <h3>Personal Details</h3>
                    <div class="name-grid">
                        <div class="form-group">
                            <label>Last Name <span style="color: #f44336;">*</span></label>
                            <input type="text" id="last_name" name="last_name" required
                                value="<?= htmlspecialchars($last_name) ?>">
                        </div>
                        <div class="form-group">
                            <label>First Name <span style="color: #f44336;">*</span></label>
                            <input type="text" id="first_name" name="first_name" required
                                value="<?= htmlspecialchars($first_name) ?>">
                        </div>
                        <div class="form-group">
                            <label>Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name"
                                value="<?= htmlspecialchars($middle_name) ?>">
                        </div>
                        <div class="form-group">
                            <label>Suffix</label>
                            <input type="text" id="suffix" name="suffix" placeholder="Jr., Sr., III, etc."
                                value="<?= htmlspecialchars($suffix) ?>">
                        </div>
                    </div>

                    <div class="address-grid">
                        <div class="form-group">
                            <label>House/Block/Lot</label>
                            <input type="text" name="house_block" placeholder="e.g., Block 5 Lot 12"
                                value="<?= htmlspecialchars($house_block) ?>">
                        </div>
                        <div class="form-group">
                            <label>Street <span style="color: #f44336;">*</span></label>
                            <input type="text" name="street" required placeholder="e.g., Main Street"
                                value="<?= htmlspecialchars($street) ?>">
                        </div>
                        <div class="form-group">
                            <label>Barangay <span style="color: #f44336;">*</span></label>
                            <input type="text" name="barangay" required placeholder="e.g., Poblacion"
                                value="<?= htmlspecialchars($barangay) ?>">
                        </div>
                        <div class="form-group">
                            <label>City/Municipality <span style="color: #f44336;">*</span></label>
                            <input type="text" name="city" required placeholder="e.g., Quezon City"
                                value="<?= htmlspecialchars($city) ?>">
                        </div>
                        <div class="form-group">
                            <label>Province/District <span style="color: #f44336;">*</span></label>
                            <input type="text" name="province" required placeholder="e.g., Metro Manila"
                                value="<?= htmlspecialchars($province) ?>">
                        </div>
                        <div class="form-group address-grid-item">
                            <label>Region <span style="color: #f44336;">*</span></label>
                            <select name="region" id="region" required class="address-input">
                                <option value="">-- Select Region --</option>
                                <option value="I" <?= $region === 'I' ? 'selected' : '' ?>>I - Ilocos Region</option>
                                <option value="II" <?= $region === 'II' ? 'selected' : '' ?>>II - Cagayan Valley</option>
                                <option value="III" <?= $region === 'III' ? 'selected' : '' ?>>III - Central Luzon</option>
                                <option value="IVA" <?= $region === 'IVA' ? 'selected' : '' ?>>IVA - Calabarzon</option>
                                <option value="IVB" <?= $region === 'IVB' ? 'selected' : '' ?>>IVB - Mimaropa</option>
                                <option value="V" <?= $region === 'V' ? 'selected' : '' ?>>V - Bicol Region</option>
                                <option value="VI" <?= $region === 'VI' ? 'selected' : '' ?>>VI - Western Visayas</option>
                                <option value="VII" <?= $region === 'VII' ? 'selected' : '' ?>>VII - Central Visayas
                                </option>
                                <option value="VIII" <?= $region === 'VIII' ? 'selected' : '' ?>>VIII - Eastern Visayas
                                </option>
                                <option value="IX" <?= $region === 'IX' ? 'selected' : '' ?>>IX - Zamboanga Peninsula
                                </option>
                                <option value="X" <?= $region === 'X' ? 'selected' : '' ?>>X - Northern Mindanao</option>
                                <option value="XI" <?= $region === 'XI' ? 'selected' : '' ?>>XI - Davao Region</option>
                                <option value="XII" <?= $region === 'XII' ? 'selected' : '' ?>>XII - Soccsksargen</option>
                                <option value="XIII" <?= $region === 'XIII' ? 'selected' : '' ?>>XIII - Caraga</option>
                                <option value="NCR" <?= $region === 'NCR' ? 'selected' : '' ?>>NCR - National Capital
                                    Region</option>
                                <option value="CAR" <?= $region === 'CAR' ? 'selected' : '' ?>>CAR - Cordillera
                                    Administrative Region</option>
                                <option value="BARMM" <?= $region === 'BARMM' ? 'selected' : '' ?>>BARMM - Bangsamoro
                                    Autonomous Region</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: ORDER DETAILS -->
                <div class="form-section">
                    <h3>Order Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Concrete Design (PSI) <span style="color: #f44336;">*</span></label>
                            <select id="concrete_psi" name="concrete_psi" required>
                                <option value="">-- Select PSI --</option>
                                <?php foreach ($psiOptions as $psi): ?>
                                    <?php
                                    $display_psi = preg_replace('/\s*(psi|PSI)\s*/i', '', trim($psi));
                                    $normalized_psi = trim($display_psi);
                                    $selected = ($concrete_psi === $psi || $concrete_psi === $normalized_psi) ? 'selected' : '';
                                    ?>
                                    <option value="<?= htmlspecialchars($psi) ?>" <?= $selected ?>>
                                        <?= htmlspecialchars($normalized_psi) ?> PSI
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Curation Days <span style="color: #f44336;">*</span></label>
                            <select id="curation_days" name="curation_days" required>
                                <option value="">-- Select Days --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Concrete Type <span style="color: #f44336;">*</span></label>
                            <select id="concrete_type" name="concrete_type" required>
                                <option value="">-- Select Type --</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Units <span style="color: #f44336;">*</span></label>
                            <input type="number" id="sap_units" name="sap_units" step="1" min="0" required
                                placeholder="0" value="<?= intval($units) ?>"
                                oninput="this.value = Math.floor(this.value)">
                        </div>
                        <div class="form-group">
                            <label>Concrete Price (₱)</label>
                            <input type="number" id="sap_concrete_price" name="sap_concrete_price" step="0.01"
                                placeholder="0.00" value="<?= htmlspecialchars($sap_concrete_price) ?>">
                        </div>
                    </div>

                    <div id="inventory-status" style="display:none; margin-top:15px; padding:15px; border-radius:8px;">
                        <div id="inventory-info" style="font-weight:bold; font-size:14px;">
                            Select concrete mix to check availability...
                        </div>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Save Order</button>
                    <a href="sap_accountsManagement.php" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('input:not([type="hidden"])');
            inputs.forEach(input => {
                input.autocomplete = 'one-time-code';
                input.autocorrect = 'off';
                input.autocapitalize = 'off';
                input.spellcheck = false;
            });

            const concreteData = <?= json_encode($concreteData) ?>;

            const psiSelect = document.getElementById('concrete_psi');
            const daysSelect = document.getElementById('curation_days');
            const typeSelect = document.getElementById('concrete_type');

            psiSelect.addEventListener('change', function () {
                daysSelect.innerHTML = '<option value="">-- Select Days --</option>';
                typeSelect.innerHTML = '<option value="">-- Select Type --</option>';

                const selectedPsi = this.value;
                if (selectedPsi && concreteData[selectedPsi]) {
                    concreteData[selectedPsi].days.forEach(function (day) {
                        const option = document.createElement('option');
                        option.value = day;
                        option.textContent = day + ' days';
                        daysSelect.appendChild(option);
                    });

                    concreteData[selectedPsi].types.forEach(function (type) {
                        const option = document.createElement('option');
                        option.value = type; // "ORDINARY", "PUMPCRETE DESIGN", "TREMIE"
                        option.textContent = type;
                        typeSelect.appendChild(option);
                    });
                }

                checkInventory();
            });

            function checkInventory() {
                const psi = psiSelect.value;
                const days = daysSelect.value;
                const type = typeSelect.value;
                const statusDiv = document.getElementById('inventory-status');
                const infoDiv = document.getElementById('inventory-info');
                const unitsInput = document.getElementById('sap_units');

                if (psi && days && type) {
                    infoDiv.innerHTML = '🔄 Checking inventory...';
                    statusDiv.style.display = 'block';

                    fetch(`check_inventory.php?psi=${encodeURIComponent(psi)}&days=${encodeURIComponent(days)}&type=${encodeURIComponent(type)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.available) {
                                infoDiv.innerHTML = `
                                    <div style="color:#28a745; font-weight:bold; margin-bottom:6px;">
                                        ✅ ${data.stocks_in} units available
                                    </div>
                                    <div style="font-size:12px; color:#666; margin-bottom:2px;">
                                        📏 ${data.total_kgs} total
                                    </div>
                                    <div style="font-size:12px; color:#666;">
                                        ⚖️ ${data.batch_size.match(/[\d,]+\.?\d*/)[0]} kgs/m³
                                    </div>
                                `;
                                statusDiv.style.background = '#d4edda';
                                statusDiv.style.borderLeft = '4px solid #28a745';
                                unitsInput.max = data.stocks_in;
                                unitsInput.placeholder = `Max: ${data.stocks_in}`;
                            } else {
                                infoDiv.innerHTML = `
                                    <div style="color:#dc3545; font-weight:bold;">
                                        ❌ No stock available
                                    </div>
                                `;
                                statusDiv.style.background = '#f8d7da';
                                statusDiv.style.borderLeft = '4px solid #dc3545';
                                unitsInput.max = 0;
                                unitsInput.placeholder = 'Out of stock';
                            }
                        })
                        .catch(error => {
                            console.error('Inventory check failed:', error);
                            infoDiv.innerHTML = '❌ Check failed';
                            statusDiv.style.background = '#fff3cd';
                        });
                } else {
                    statusDiv.style.display = 'none';
                }
            }

            [daysSelect, typeSelect].forEach(select => {
                select.addEventListener('change', checkInventory);
            });

            const form = document.getElementById('concreteForm');
            const unitsInput = document.getElementById('sap_units');

            form.addEventListener('submit', function (e) {
                const units = parseInt(unitsInput.value);
                const maxUnits = parseInt(unitsInput.max) || 0;

                if (units > maxUnits) {
                    e.preventDefault();
                    alert(`Units exceed available stock! Max: ${maxUnits}`);
                    return false;
                }

                if (!this.checkValidity()) {
                    e.preventDefault();
                    this.reportValidity();
                }
            });

            const regionSelect = document.getElementById('region');
            if (regionSelect && '<?= $region ?>' !== '') {
                regionSelect.value = '<?= $region ?>';
            }
        });
    </script>
</body>

</html>