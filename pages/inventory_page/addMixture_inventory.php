<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Add new record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_added = $_POST['date_added'] ?? date('Y-m-d');
    $cement = $_POST['cement'] ?? 0;
    $fly_ash = $_POST['fly_ash'] ?? 0;
    $sand = $_POST['sand'] ?? 0;
    $three_fourth = $_POST['three_fourth'] ?? 0;
    $three_eight = $_POST['three_eight'] ?? 0;
    $g1 = $_POST['g1'] ?? 0;
    $admix = $_POST['admix'] ?? 0;
    $water = $_POST['water'] ?? 0;
    $air = $_POST['air'] ?? 0;
    $others = $_POST['others'] ?? '';

    // Calculate total (Others is NOT included)
    $total = $cement + $fly_ash + $sand + $three_fourth + $three_eight + $g1 + $admix + $water + $air;

    $stmt = $conn->prepare("INSERT INTO sorcom_mixture_inventory (date_added, cement, fly_ash, sand, `3_4`, `3_8`, g1, admix, water, air, others, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdddddddddsd", $date_added, $cement, $fly_ash, $sand, $three_fourth, $three_eight, $g1, $admix, $water, $air, $others, $total);
    $stmt->execute();
    $stmt->close();

    header("Location: mixture_inventory.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Mixture</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../inventory_page/mixture_inventory.css">
    <link rel="stylesheet" href="../inventory_page/addMixture_inventory.css">
    <?php showHeader(); ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #bfc3aa;
            padding: 20px;
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
            font-family: Arial, sans-serif;
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
            font-family: Arial, sans-serif;
        }

        .form-row input[type="date"],
        .form-row input[type="number"],
        .form-row input[type="text"] {
            flex: 1;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }

        .form-row button,
        .form-row a {
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            font-family: Arial, sans-serif;
        }

        .form-row button[type="submit"] {
            background: #37dd37;
            color: white;
        }

        .form-row a {
            background: #ccc;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2 style="color: black; font-family: Arial, sans-serif;">Add New Mixture</h2>

        <form method="POST">
            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Date:</label>
                <input type="date" name="date_added" value="<?php echo date('Y-m-d'); ?>"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Cement:</label>
                <input type="number" step="0.01" min="0" name="cement" placeholder="0.00" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Fly Ash:</label>
                <input type="number" step="0.01" min="0" name="fly_ash" placeholder="0.00" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Sand:</label>
                <input type="number" step="0.01" min="0" name="sand" placeholder="0.00" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">3/4:</label>
                <input type="number" step="0.01" min="0" name="three_fourth" placeholder="0.00"
                    oninput="calculateTotal()" style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">3/8:</label>
                <input type="number" step="0.01" min="0" name="three_eight" placeholder="0.00"
                    oninput="calculateTotal()" style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">G1:</label>
                <input type="number" step="0.01" min="0" name="g1" placeholder="0.00" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Admix:</label>
                <input type="number" step="0.01" min="0" name="admix" placeholder="0.00" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Water:</label>
                <input type="number" step="0.01" min="0" name="water" placeholder="0.00" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Air:</label>
                <input type="number" step="0.001" min="0" name="air" placeholder="0.001" oninput="calculateTotal()"
                    style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Others:</label>
                <input type="text" name="others" placeholder="Enter others" style="font-family: Arial, sans-serif;">
            </div>

            <div class="form-row">
                <label style="font-family: Arial, sans-serif;">Total:</label>
                <input type="number" step="0.1" name="total" id="total" placeholder="0.0" readonly
                    style="background-color: #e0e0e0; font-family: Arial, sans-serif;">
            </div>

            <div class="form-row" style="justify-content: center;">
                <button type="submit" style="font-family: Arial, sans-serif;">Add</button>
                <a href="mixture_inventory.php" style="font-family: Arial, sans-serif;">Cancel</a>
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