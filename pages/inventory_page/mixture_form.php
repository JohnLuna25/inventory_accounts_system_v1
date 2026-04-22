<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// --- Initialize variables ---
$id = 0;
$date_added = date('Y-m-d');
$cement = 0;
$fly_ash = 0;
$sand = 0;
$three_fourth = 0;
$three_eight = 0;
$g1 = 0;
$admix = 0;
$water = 0;
$air = 0;
$others = '';
$total = 0;
$error_message = "";
$isEditMode = false;

// --- Check if we're in EDIT mode (has ?id= in URL) ---
if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id'])) {
    $isEditMode = true;
    $id = intval($_GET['id']);

    // Load existing data
    $stmt = $conn->prepare("SELECT * FROM sorcom_mixture_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $date_added = $row['date_added'];
        $cement = $row['cement'];
        $fly_ash = $row['fly_ash'];
        $sand = $row['sand'];
        $three_fourth = $row['3_4'];
        $three_eight = $row['3_8'];
        $g1 = $row['g1'];
        $admix = $row['admix'];
        $water = $row['water'];
        $air = $row['air'];
        $others = $row['others'];
        $total = $row['total'];
    }
}

// --- Handle DELETE action ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM sorcom_mixture_inventory WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: mixture_inventory.php");
    exit;
}

// --- Handle POST (ADD or EDIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $cement = floatval($_POST['cement'] ?? 0);
    $fly_ash = floatval($_POST['fly_ash'] ?? 0);
    $sand = floatval($_POST['sand'] ?? 0);
    $three_fourth = floatval($_POST['three_fourth'] ?? 0);
    $three_eight = floatval($_POST['three_eight'] ?? 0);
    $g1 = floatval($_POST['g1'] ?? 0);
    $admix = floatval($_POST['admix'] ?? 0);
    $water = floatval($_POST['water'] ?? 0);
    $air = floatval($_POST['air'] ?? 0);
    $others = $_POST['others'] ?? '';

    // Calculate total (Others is NOT included)
    $total = $cement + $fly_ash + $sand + $three_fourth + $three_eight + $g1 + $admix + $water + $air;

    $edit_id = intval($_POST['id'] ?? 0);

    if ($edit_id > 0) {
        // EDIT MODE
        $stmt = $conn->prepare("UPDATE sorcom_mixture_inventory SET 
            date_added = ?, cement = ?, fly_ash = ?, sand = ?, `3_4` = ?, `3_8` = ?, 
            g1 = ?, admix = ?, water = ?, air = ?, others = ?, total = ? 
            WHERE id = ?");
        $stmt->bind_param(
            "sdddddddddsdi",
            $date_added,
            $cement,
            $fly_ash,
            $sand,
            $three_fourth,
            $three_eight,
            $g1,
            $admix,
            $water,
            $air,
            $others,
            $total,
            $edit_id
        );
    } else {
        // ADD MODE
        $stmt = $conn->prepare("INSERT INTO sorcom_mixture_inventory 
            (date_added, cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air, others, total) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sdddddddddsd",
            $date_added,
            $cement,
            $fly_ash,
            $sand,
            $three_fourth,
            $three_eight,
            $g1,
            $admix,
            $water,
            $air,
            $others,
            $total
        );
    }

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: mixture_inventory.php");
        exit;
    } else {
        $error_message = "Error: " . $stmt->error;
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $isEditMode ? 'Edit Mixture' : 'Add Mixture' ?></title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../inventory_page/mixture_inventory.css">
    <link rel="stylesheet" href="../inventory_page/mixture_form.css">
    <?php showHeader(); ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
        }

        .form-container {
            background: white;
            width: 450px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 5px;
        }

        .form-container h2 {
            text-align: center;
            margin-top: 0;
            color: black;
        }

        .form-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-row label {
            width: 120px;
            text-align: left;
            font-weight: bold;
        }

        .form-row input[type="date"],
        .form-row input[type="number"],
        .form-row input[type="text"] {
            flex: 1;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-row button,
        .form-row a {
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }

        .form-row button[type="submit"] {
            background: #37dd37;
            color: white;
        }

        .form-row a {
            background: #ccc;
            color: #333;
        }

        .error-message {
            color: red;
            text-align: center;
            font-weight: bold;
            margin: 10px;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2><?= $isEditMode ? 'Edit Mixture' : 'Add New Mixture' ?></h2>

        <?php if ($error_message): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="form-row">
                <label>Date:</label>
                <input type="date" name="date_added" value="<?= htmlspecialchars($date_added) ?>">
            </div>

            <div class="form-row">
                <label>Cement:</label>
                <input type="number" step="0.01" min="0" name="cement" value="<?= $cement ?>" placeholder="0.00"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>Fly Ash:</label>
                <input type="number" step="0.01" min="0" name="fly_ash" value="<?= $fly_ash ?>" placeholder="0.00"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>Sand:</label>
                <input type="number" step="0.01" min="0" name="sand" value="<?= $sand ?>" placeholder="0.00"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>3/4:</label>
                <input type="number" step="0.01" min="0" name="three_fourth" value="<?= $three_fourth ?>"
                    placeholder="0.00" oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>3/8:</label>
                <input type="number" step="0.01" min="0" name="three_eight" value="<?= $three_eight ?>"
                    placeholder="0.00" oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>G1:</label>
                <input type="number" step="0.01" min="0" name="g1" value="<?= $g1 ?>" placeholder="0.00"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>Admix:</label>
                <input type="number" step="0.01" min="0" name="admix" value="<?= $admix ?>" placeholder="0.00"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>Water:</label>
                <input type="number" step="0.01" min="0" name="water" value="<?= $water ?>" placeholder="0.00"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>Air:</label>
                <input type="number" step="0.001" min="0" name="air" value="<?= $air ?>" placeholder="0.001"
                    oninput="calculateTotal()">
            </div>

            <div class="form-row">
                <label>Others:</label>
                <input type="text" name="others" value="<?= htmlspecialchars($others) ?>" placeholder="Enter others">
            </div>

            <div class="form-row">
                <label>Total:</label>
                <input type="number" step="0.1" name="total" id="total" value="<?= $total ?>" placeholder="0.0" readonly
                    style="background-color: #e0e0e0;">
            </div>

            <div class="form-row" style="justify-content: center;">
                <button type="submit"><?= $isEditMode ? 'Update' : 'Add' ?></button>
                <a href="mixture_inventory.php">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function calculateTotal() {
            var cement = parseFloat(document.querySelector('input[name="cement"]').value) || 0;
            var fly_ash = parseFloat(document.querySelector('input[name="fly_ash"]').value) || 0;
            var sand = parseFloat(document.querySelector('input[name="sand"]').value) || 0;
            var three_fourth = parseFloat(document.querySelector('input[name="three_fourth"]').value) || 0;
            var three_eight = parseFloat(document.querySelector('input[name="three_eight"]').value) || 0;
            var g1 = parseFloat(document.querySelector('input[name="g1"]').value) || 0;
            var admix = parseFloat(document.querySelector('input[name="admix"]').value) || 0;
            var water = parseFloat(document.querySelector('input[name="water"]').value) || 0;
            var air = parseFloat(document.querySelector('input[name="air"]').value) || 0;

            var total = cement + fly_ash + sand + three_fourth + three_eight + g1 + admix + water + air;
            document.getElementById('total').value = total.toFixed(1);
        }
    </script>

</body>

</html>