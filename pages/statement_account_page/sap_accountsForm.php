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
$order_status = "";
$units = 0;
$sap_id = "";
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
    $stmt->bind_result($sap_id, $order_date, $delivery_date, $customer_name, $full_address, $concrete_psi, $curation_days, $concrete_type, $units, $sap_concrete_price, $order_status);
    $stmt->fetch();
    $stmt->close();

    // Split full name into components for editing
    if ($customer_name) {
        $name_parts = preg_split('/\s+/', trim($customer_name), 4);
        $last_name = $name_parts[0] ?? '';
        $first_name = $name_parts[1] ?? '';
        $middle_name = $name_parts[2] ?? '';
        $suffix = $name_parts[3] ?? '';
    }

    // 🐛 FIX #1: ROBUST ADDRESS SPLITTING
    if ($full_address) {
        $address_parts = preg_split('/\s*\|\|\s*/', trim($full_address));

        // House/Block/Street (first part before first ||) - SPLIT BY COMMA
        if (!empty($address_parts[0])) {
            $house_street_parts = preg_split('/\s*,\s*/', trim($address_parts[0]), 2);
            $house_block = trim($house_street_parts[0] ?? '');
            $street = !empty($house_street_parts[1]) ? trim($house_street_parts[1]) : trim($address_parts[0]);
        }

        $barangay = trim($address_parts[1] ?? '');
        $city = trim($address_parts[2] ?? '');
        $province = trim($address_parts[3] ?? '');
        $region = trim($address_parts[4] ?? '');
    }
} else {
    // NEW ORDER: Auto-set today's date
    $order_date = date('Y-m-d');
}

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_date = $_POST['order_date'] ?? '';
    $delivery_date = $_POST['delivery_date'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $middle_name = $_POST['middle_name'] ?? '';
    $suffix = $_POST['suffix'] ?? '';
    $customer_name = trim("{$last_name} {$first_name} {$middle_name} {$suffix}");
    $customer_name = preg_replace('/\s+/', ' ', $customer_name);

    // Handle address fields
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
    $sap_units = floatval($_POST['units'] ?? 0);
    $sap_concrete_price = $_POST['sap_concrete_price'] ?? '';
    $order_status = $_POST['order_status'] ?? '';

    if (!empty($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $stmt = $conn->prepare("UPDATE statement_of_account 
            SET order_date=?, delivery_date=?, customer_name=?, full_address=?, concrete_psi=?, curation_days=?, concrete_type=?, sap_units=?, sap_concrete_price=?, order_status=? 
            WHERE sap_id=?");
        $stmt->bind_param("sssssssssss", $order_date, $delivery_date, $customer_name, $full_address, $concrete_psi, $curation_days, $concrete_type, $cubic_meters, $sap_concrete_price, $order_status, $id);
    } else {
        $sap_id = generateUniqueSapId($conn);
        $stmt = $conn->prepare("INSERT INTO statement_of_account 
            (sap_id, order_date, delivery_date, customer_name, full_address, concrete_psi, curation_days, concrete_type, cubic_meters, sap_concrete_price, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $sap_id, $order_date, $delivery_date, $customer_name, $full_address, $concrete_psi, $curation_days, $concrete_type, $cubic_meters, $sap_concrete_price, $order_status);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: sap_accountsManagement.php?success=1");
        exit;
    } else {
        $error_message = "Error saving order: " . $stmt->error;
        $stmt->close();
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
                <h2>🏗️ Concrete Order</h2>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="POST" id="concreteForm">
                <?php if (isset($_GET['id'])): ?>
                    <div class="form-group readonly-field">
                        <label>Order ID</label>
                        <input type="text" value="<?= htmlspecialchars($sap_id ?? '') ?>" readonly>
                    </div>
                <?php endif; ?>

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
                                <option value="I" <?= $region === 'I' ? 'selected' : '' ?>>I - Ilocos Region (CAR)</option>
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
                                    // 🐛 FIX #3: Normalize PSI display
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
                            <input type="text" id="sap_units" name="sap_units" placeholder="3/4" required
                                value="<?= htmlspecialchars($sap_units) ?>">
                        </div>
                        <div class="form-group">
                            <label>Concrete Price (₱)</label>
                            <input type="number" id="sap_concrete_price" name="sap_concrete_price" step="0.01"
                                placeholder="0.00" value="<?= htmlspecialchars($sap_concrete_price ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Order Status <span style="color: #f44336;">*</span></label>
                            <select id="order_status" name="order_status" required>
                                <option value="">-- Select Status --</option>
                                <?php foreach ($statusLabels as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $order_status === $key ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
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
        // 1. KILL AUTOCOMPLETE FIRST
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"])');
            inputs.forEach(input => {
                input.autocomplete = 'one-time-code';
                input.autocorrect = 'off';
                input.autocapitalize = 'off';
                input.spellcheck = false;
            });
        });

        // 2. CONCRETE DROPDOWN LOGIC
        const concreteData = <?= json_encode($concreteData) ?>;

        document.getElementById('concrete_psi').addEventListener('change', function () {
            const selectedPsi = this.value;
            const daysSelect = document.getElementById('curation_days');
            const typeSelect = document.getElementById('concrete_type');

            // Clear options
            daysSelect.innerHTML = '<option value="">-- Select Curation Days --</option>';
            typeSelect.innerHTML = '<option value="">-- Select Concrete Type --</option>';

            if (selectedPsi && concreteData[selectedPsi]) {
                // Populate days
                concreteData[selectedPsi].days.forEach(function (day) {
                    const option = document.createElement('option');
                    option.value = day;
                    option.textContent = day;
                    daysSelect.appendChild(option);
                });

                // Populate types
                concreteData[selectedPsi].types.forEach(function (type) {
                    const option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    typeSelect.appendChild(option);
                });
            }
        });

        // 🐛 FIX #2: PROPER EDIT MODE INITIALIZATION
        document.addEventListener('DOMContentLoaded', function () {
            const psiSelect = document.getElementById('concrete_psi');
            const savedDays = '<?= addslashes($curation_days) ?>';
            const savedType = '<?= addslashes($concrete_type) ?>';

            if (psiSelect && psiSelect.value) {
                // Trigger PSI change to populate dropdowns
                psiSelect.dispatchEvent(new Event('change'));

                // Set saved values AFTER dropdowns are populated
                setTimeout(function () {
                    if (savedDays) {
                        document.getElementById('curation_days').value = savedDays;
                    }
                    if (savedType) {
                        document.getElementById('concrete_type').value = savedType;
                    }
                }, 200); // Give time for dropdown population
            }

            // Set region if editing
            const regionSelect = document.getElementById('region');
            if (regionSelect && '<?= $region ?>' !== '') {
                regionSelect.value = '<?= $region ?>';
            }
        });

        // 4. FORM VALIDATION
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('concreteForm');
            form.addEventListener('submit', function (e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    this.reportValidity();
                    return false;
                }
                // Form is valid, allow normal submission
            });
        });
    </script>
</body>

</html>