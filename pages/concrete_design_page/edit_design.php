<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required files
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check DB connection
if (!isset($conn)) {
    die("Database connection not established.");
}

// Check for design ID
if (!isset($_GET['id'])) {
    die("No concrete design selected.");
}

$design_id = intval($_GET['id']); // sanitize input

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Main design fields
    $psi = $_POST['concrete_psi'] ?? '';
    $curation_days = $_POST['curation_days'] ?? '';
    $type = $_POST['concrete_type'] ?? '';
    $cubic = $_POST['cubic_meter'] ?? '';
    $slump = $_POST['concrete_slump'] ?? '';
    $wc = $_POST['wc_ratio'] ?? '';
    $cement_source = $_POST['cement_source'] ?? '';
    $admix_source = $_POST['admix_source'] ?? '';
    $cement_mix = $_POST['cement_mix'] ?? '';
    $concrete_price = $_POST['concrete_price'] ?? '';

    // Volume batch fields
    $fly_ash = $_POST['fly_ash'] ?? '';
    $sand = $_POST['sand'] ?? '';
    $three_eight = $_POST['three_eight'] ?? '';
    $three_fourth = $_POST['three_fourth'] ?? '';
    $g1 = $_POST['G1'] ?? '';
    $admix = $_POST['admix'] ?? '';
    $water = $_POST['water'] ?? '';
    $air = $_POST['air'] ?? '';
    $others = $_POST['others'] ?? '';
    $total = $_POST['total'] ?? '';

    // Update query — using your single CSV table
    $update_sql = "UPDATE sorcom_concrete_design_masterlist SET
        concrete_psi='$psi',
        curation_days='$curation_days',
        concrete_type='$type',
        cubic_meter='$cubic',
        concrete_slump='$slump',
        wc_ratio='$wc',
        cement_source='$cement_source',
        admix_source='$admix_source',
        cement_mix='$cement_mix',
        concrete_price='$concrete_price',
        fly_ash='$fly_ash',
        sand='$sand',
        three_eight='$three_eight',
        three_fourth='$three_fourth',
        G1='$g1',
        admix='$admix',
        water='$water',
        air='$air',
        others='$others',
        total='$total'
        WHERE id=$design_id";

    if ($conn->query($update_sql)) {
        echo "<script>alert('Design updated successfully!');window.location.href='concrete_design_list.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating design: {$conn->error}');</script>";
    }
}

// Fetch design data from your CSV-imported table
$design_result = $conn->query("SELECT * FROM sorcom_concrete_design_masterlist WHERE id = $design_id");
if (!$design_result || $design_result->num_rows == 0) {
    die("Concrete design not found.");
}
$design = $design_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Concrete Design</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e9ecef;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
        }

        form {
            max-width: 800px;
            margin: 20px auto;
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        fieldset {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
        }

        legend {
            font-weight: bold;
        }

        table {
            width: 100%;
            margin: 0 auto;
            text-align: center;
            border-collapse: collapse;
        }

        td {
            padding: 8px;
        }

        input[type="text"] {
            width: 90%;
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .buttons {
            text-align: center;
            margin-top: 15px;
        }

        .buttons button,
        .buttons a {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            margin: 0 5px;
        }

        .save {
            background: #4CAF50;
        }

        .back {
            background: #2196F3;
        }
    </style>
</head>

<body>
    <h2>Edit Concrete Design</h2>

    <form method="POST">
        <!-- Main Design -->
        <fieldset>
            <legend>Main Design</legend>
            <table>
                <tr>
                    <td>PSI</td>
                    <td><input type="text" name="concrete_psi"
                            value="<?= htmlspecialchars($design['concrete_psi'] ?? '') ?>" required></td>
                </tr>
                <tr>
                    <td>Curation Days</td>
                    <td><input type="text" name="curation_days"
                            value="<?= htmlspecialchars($design['curation_days'] ?? '') ?>" required></td>
                </tr>
                <tr>
                    <td>Concrete Type</td>
                    <td><input type="text" name="concrete_type"
                            value="<?= htmlspecialchars($design['concrete_type'] ?? '') ?>" required></td>
                </tr>
                <tr>
                    <td>Cubic Meter</td>
                    <td><input type="text" name="cubic_meter"
                            value="<?= htmlspecialchars($design['cubic_meter'] ?? '') ?>" required></td>
                </tr>
                <tr>
                    <td>Slump</td>
                    <td><input type="text" name="concrete_slump"
                            value="<?= htmlspecialchars($design['concrete_slump'] ?? '') ?>" required></td>
                </tr>
                <tr>
                    <td>W/C Ratio</td>
                    <td><input type="text" name="wc_ratio" value="<?= htmlspecialchars($design['wc_ratio'] ?? '') ?>"
                            required></td>
                </tr>
                <tr>
                    <td>Cement Source</td>
                    <td><input type="text" name="cement_source"
                            value="<?= htmlspecialchars($design['cement_source'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Admix Source</td>
                    <td><input type="text" name="admix_source"
                            value="<?= htmlspecialchars($design['admix_source'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Cement Mix</td>
                    <td><input type="text" name="cement_mix"
                            value="<?= htmlspecialchars($design['cement_mix'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Concrete Price</td>
                    <td><input type="text" name="concrete_price"
                            value="<?= htmlspecialchars($design['concrete_price'] ?? '') ?>"></td>
                </tr>
            </table>
        </fieldset>

        <!-- Volume Batch -->
        <fieldset>
            <legend>Volume Batch</legend>
            <table>
                <tr>
                    <td>Fly Ash</td>
                    <td><input type="text" name="fly_ash" value="<?= htmlspecialchars($design['fly_ash'] ?? '') ?>">
                    </td>
                </tr>
                <tr>
                    <td>Sand</td>
                    <td><input type="text" name="sand" value="<?= htmlspecialchars($design['sand'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>3/8"</td>
                    <td><input type="text" name="three_eight"
                            value="<?= htmlspecialchars($design['three_eight'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>3/4"</td>
                    <td><input type="text" name="three_fourth"
                            value="<?= htmlspecialchars($design['three_fourth'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>G1</td>
                    <td><input type="text" name="G1" value="<?= htmlspecialchars($design['G1'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Admix</td>
                    <td><input type="text" name="admix" value="<?= htmlspecialchars($design['admix'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Water</td>
                    <td><input type="text" name="water" value="<?= htmlspecialchars($design['water'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Air</td>
                    <td><input type="text" name="air" value="<?= htmlspecialchars($design['air'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Others</td>
                    <td><input type="text" name="others" value="<?= htmlspecialchars($design['others'] ?? '') ?>"></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><input type="text" name="total" value="<?= htmlspecialchars($design['total'] ?? '') ?>"></td>
                </tr>
            </table>
        </fieldset>

        <div class="buttons">
            <button type="submit" class="save">💾 Save Edited Design</button>
            <a href="concrete_design_list.php" class="back">← Back to List</a>
        </div>
    </form>

</body>

</html>