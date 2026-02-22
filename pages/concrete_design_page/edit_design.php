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

$design_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $update_sql = "UPDATE sorcom_concrete_design_masterlist SET
        concrete_psi='$psi', curation_days='$curation_days', concrete_type='$type',
        cubic_meter='$cubic', concrete_slump='$slump', wc_ratio='$wc',
        cement_source='$cement_source', admix_source='$admix_source',
        cement_mix='$cement_mix', concrete_price='$concrete_price',
        fly_ash='$fly_ash', sand='$sand', three_eight='$three_eight',
        three_fourth='$three_fourth', G1='$g1', admix='$admix',
        water='$water', air='$air', others='$others', total='$total'
        WHERE id=$design_id";

    if ($conn->query($update_sql)) {
        echo "<script>alert('Design updated successfully!');window.location.href='concrete_design_list.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating design: {$conn->error}');</script>";
    }
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Concrete Design</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
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
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            align-items: center;
        }

        .form-row label {
            width: 180px;
            font-weight: bold;
            color: #333;
        }

        .form-row input {
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
            background: #0b63c4;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-submit:hover {
            background: #0e4a8b;
        }

        .btn-cancel {
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        @media (max-width: 576px) {
            .form-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-row label {
                width: 100%;
                margin-bottom: 5px;
            }

            .form-row input {
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

    <div class="main-content" style="padding-top: 140px;">
        <div class="form-container">
            <h2>Edit Concrete Design</h2>

            <form method="POST">
                <div class="form-row">
                    <label>Concrete PSI:</label>
                    <input type="text" name="concrete_psi"
                        value="<?= htmlspecialchars($design['concrete_psi'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Curation Days:</label>
                    <input type="text" name="curation_days"
                        value="<?= htmlspecialchars($design['curation_days'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Type:</label>
                    <input type="text" name="concrete_type"
                        value="<?= htmlspecialchars($design['concrete_type'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <input type="text" name="cubic_meter" value="<?= htmlspecialchars($design['cubic_meter'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Slump:</label>
                    <input type="text" name="concrete_slump"
                        value="<?= htmlspecialchars($design['concrete_slump'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>W/C Ratio:</label>
                    <input type="text" name="wc_ratio" value="<?= htmlspecialchars($design['wc_ratio'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Cement Source:</label>
                    <input type="text" name="cement_source"
                        value="<?= htmlspecialchars($design['cement_source'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Admix Source:</label>
                    <input type="text" name="admix_source"
                        value="<?= htmlspecialchars($design['admix_source'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input type="text" name="cement_mix" value="<?= htmlspecialchars($design['cement_mix'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Price:</label>
                    <input type="text" name="concrete_price"
                        value="<?= htmlspecialchars($design['concrete_price'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input type="text" name="fly_ash" value="<?= htmlspecialchars($design['fly_ash'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input type="text" name="sand" value="<?= htmlspecialchars($design['sand'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input type="text" name="three_eight" value="<?= htmlspecialchars($design['three_eight'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>3/4":</label>
                    <input type="text" name="three_fourth"
                        value="<?= htmlspecialchars($design['three_fourth'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input type="text" name="G1" value="<?= htmlspecialchars($design['G1'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input type="text" name="admix" value="<?= htmlspecialchars($design['admix'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input type="text" name="water" value="<?= htmlspecialchars($design['water'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input type="text" name="air" value="<?= htmlspecialchars($design['air'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input type="text" name="others" value="<?= htmlspecialchars($design['others'] ?? '') ?>">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input type="text" name="total" value="<?= htmlspecialchars($design['total'] ?? '') ?>">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <a href="concrete_design_list.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>