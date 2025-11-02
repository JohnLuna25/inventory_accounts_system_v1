<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../FunctionAndDB/db_connect.php';
include __DIR__ . '/../FunctionAndDB/Web_Functions.php';

if (!isset($conn))
    die("Database connection not established.");
if (!isset($_GET['id']))
    die("No concrete design selected.");

$design_id = intval($_GET['id']); // sanitize input

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Main design fields
    $psi = $_POST['concrete_psi'];
    $curration = $_POST['curration_type'];
    $type = $_POST['concrete_type'];
    $cubic = $_POST['cubic_meter'];
    $slump = $_POST['concrete_slump'];
    $wc = $_POST['wc_ratio'];

    // Volume batch fields, cast to float and default to 0
    $cement = floatval($_POST['cement'] ?? 0);
    $fly_ash = floatval($_POST['fly_ash'] ?? 0);
    $sand = floatval($_POST['sand'] ?? 0);
    $three_eighth = floatval($_POST['three_eighth'] ?? 0);
    $three_quarter = floatval($_POST['three_quarter'] ?? 0);
    $g1 = floatval($_POST['g1'] ?? 0);
    $admix = floatval($_POST['admix'] ?? 0);
    $water = floatval($_POST['water'] ?? 0);
    $air = floatval($_POST['air'] ?? 0);
    $others = floatval($_POST['others'] ?? 0);

    // Auto-calculate total safely
    $total = $cement + $fly_ash + $sand + $three_eighth + $three_quarter + $g1 + $admix + $water + $air + $others;


    // Update main design
    $update_design_sql = "UPDATE concrete_designs SET
        concrete_psi='$psi',
        curration_type='$curration',
        concrete_type='$type',
        cubic_meter='$cubic',
        concrete_slump='$slump',
        wc_ratio='$wc'
        WHERE id=$design_id";

    // Update volume batch
    $update_batch_sql = "UPDATE concrete_volume_batch SET
        cement='$cement',
        fly_ash='$fly_ash',
        sand='$sand',
        three_eighth='$three_eighth',
        three_quarter='$three_quarter',
        g1='$g1',
        admix='$admix',
        water='$water',
        air='$air',
        others='$others',
        total='$total'
        WHERE design_id=$design_id";

    if ($conn->query($update_design_sql) && $conn->query($update_batch_sql)) {
        echo "<script>alert('Design updated successfully!');window.location.href='concrete_design_list.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating design: {$conn->error}');</script>";
    }
}

// Fetch main design
$design_result = $conn->query("SELECT * FROM concrete_designs WHERE id=$design_id");
if (!$design_result || $design_result->num_rows == 0)
    die("Concrete design not found.");
$design = $design_result->fetch_assoc();

// Fetch volume batch
$batch_result = $conn->query("SELECT * FROM concrete_volume_batch WHERE design_id=$design_id");
$batch = $batch_result->fetch_assoc() ?? []; // fallback empty array if no batch yet

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
            max-width: 600px;
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
            margin-top: 10px;
        }

        .buttons button,
        .buttons a {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
        }

        .save {
            background: #4CAF50;
        }

        .back {
            background: #2196F3;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <?php showHeader(); ?>

    <h2>Concrete Design</h2>

    <form method="POST">
        <!-- Main Design Section -->
        <fieldset>
            <legend>Main Design</legend>
            <table>
                <tr>
                    <td>PSI</td>
                    <td><input type="text" name="concrete_psi" value="<?php echo $design['concrete_psi'] ?? ''; ?>"
                            required></td>
                </tr>
                <tr>
                    <td>Curration Type</td>
                    <td><input type="text" name="curration_type" value="<?php echo $design['curration_type'] ?? ''; ?>"
                            required></td>
                </tr>
                <tr>
                    <td>Concrete Type</td>
                    <td><input type="text" name="concrete_type" value="<?php echo $design['concrete_type'] ?? ''; ?>"
                            required></td>
                </tr>
                <tr>
                    <td>Cubic Meter</td>
                    <td><input type="text" name="cubic_meter" value="<?php echo $design['cubic_meter'] ?? ''; ?>"
                            required></td>
                </tr>
                <tr>
                    <td>Slump</td>
                    <td><input type="text" name="concrete_slump" value="<?php echo $design['concrete_slump'] ?? ''; ?>"
                            required></td>
                </tr>
                <tr>
                    <td>W/C Ratio</td>
                    <td><input type="text" name="wc_ratio" value="<?php echo $design['wc_ratio'] ?? ''; ?>" required>
                    </td>
                </tr>
            </table>
        </fieldset>

        <!-- Volume Batch Section -->
        <fieldset>
            <legend>Volume Batch</legend>
            <table>
                <tr>
                    <td>Cement</td>
                    <td><input type="text" name="cement" value="<?php echo $batch['cement'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Fly Ash</td>
                    <td><input type="text" name="fly_ash" value="<?php echo $batch['fly_ash'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Sand</td>
                    <td><input type="text" name="sand" value="<?php echo $batch['sand'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>3/8"</td>
                    <td><input type="text" name="three_eighth" value="<?php echo $batch['three_eighth'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>3/4"</td>
                    <td><input type="text" name="three_quarter" value="<?php echo $batch['three_quarter'] ?? ''; ?>">
                    </td>
                </tr>
                <tr>
                    <td>G-1</td>
                    <td><input type="text" name="g1" value="<?php echo $batch['g1'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Admix</td>
                    <td><input type="text" name="admix" value="<?php echo $batch['admix'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Water</td>
                    <td><input type="text" name="water" value="<?php echo $batch['water'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Air</td>
                    <td><input type="text" name="air" value="<?php echo $batch['air'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Others</td>
                    <td><input type="text" name="others" value="<?php echo $batch['others'] ?? ''; ?>"></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td><input type="text" name="total" value="<?php echo $batch['total'] ?? ''; ?>" readonly></td>
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