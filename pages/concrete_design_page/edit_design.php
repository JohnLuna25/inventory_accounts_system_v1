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
$vb_id = intval($_GET['id'] ?? 0);

if ($vb_id <= 0) {
    die("No concrete design selected.");
}

// Fetch VB data using vb_id
$vb_result = $conn->query("SELECT * FROM scd_vb_list WHERE vb_id = $vb_id");
if (!$vb_result || $vb_result->num_rows == 0) {
    die("Volume Batch design not found.");
}
$vb_design = $vb_result->fetch_assoc();

// Fetch SSD data using ssd_id
$ssd_result = $conn->query("SELECT * FROM scd_ssd_list WHERE ssd_id = $vb_id");
if ($ssd_result && $ssd_result->num_rows > 0) {
    $ssd_design = $ssd_result->fetch_assoc();
} else {
    $ssd_design = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VB Fields
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

    // SSD Fields
    $vb_cubic = $_POST['vb_cubic_meter'] ?? '';
    $ssd_cement_source = $_POST['ssd_cement_source'] ?? '';
    $ssd_cement_mix = $_POST['ssd_cement_mix'] ?? '';
    $ssd_fly_ash = $_POST['ssd_fly_ash'] ?? '';
    $ssd_three_fourth = $_POST['ssd_three_fourth'] ?? '';
    $ssd_three_eight = $_POST['ssd_three_eight'] ?? '';
    $ssd_water = $_POST['ssd_water'] ?? '';
    $vb_concrete_slump = $_POST['vb_concrete_slump'] ?? '';
    $ssd_admix_source = $_POST['ssd_admix_source'] ?? '';
    $ssd_sand = $_POST['ssd_sand'] ?? '';
    $ssd_g1 = $_POST['ssd_G1'] ?? '';
    $ssd_air = $_POST['ssd_air'] ?? '';
    $ssd_wc = $_POST['ssd_wc_ratio'] ?? '';
    $ssd_admix = $_POST['ssd_admix'] ?? '';
    $ssd_others = $_POST['ssd_others'] ?? '';

    // Escape values for SQL safety
    $escape = function ($value) use ($conn) {
        return $conn->real_escape_string($value ?? '');
    };

    // Update VB table with vb_ prefix
    $update_vb = "UPDATE scd_vb_list SET
        vb_concrete_psi='" . $escape($psi) . "', vb_curation_days='" . $escape($curation_days) . "', vb_concrete_type='" . $escape($type) . "',
        vb_cubic_meter='" . $escape($cubic) . "', vb_concrete_slump='" . $escape($slump) . "', vb_wc_ratio='" . $escape($wc) . "',
        vb_cement_source='" . $escape($cement_source) . "', vb_admix_source='" . $escape($admix_source) . "',
        vb_cement_mix='" . $escape($cement_mix) . "', concrete_price='" . $escape($concrete_price) . "',
        vb_fly_ash='" . $escape($fly_ash) . "', vb_sand='" . $escape($sand) . "', vb_three_eight='" . $escape($three_eight) . "',
        vb_three_fourth='" . $escape($three_fourth) . "', vb_G1='" . $escape($g1) . "', vb_admix='" . $escape($admix) . "',
        vb_water='" . $escape($water) . "', vb_air='" . $escape($air) . "', vb_others='" . $escape($others) . "', vb_total='" . $escape($total) . "'
        WHERE vb_id=$vb_id";

    // Update SSD table with ssd_ prefix
    $update_ssd = "UPDATE scd_ssd_list SET
    ssd_cement_mix='" . $escape($ssd_cement_mix) . "', ssd_fly_ash='" . $escape($ssd_fly_ash) . "',
    ssd_three_fourth='" . $escape($ssd_three_fourth) . "', ssd_three_eight='" . $escape($ssd_three_eight) . "',
    ssd_water='" . $escape($ssd_water) . "', vb_concrete_slump='" . $escape($vb_concrete_slump) . "',
    ssd_admix_source='" . $escape($ssd_admix_source) . "', ssd_sand='" . $escape($ssd_sand) . "',
        ssd_G1='" . $escape($ssd_g1) . "', ssd_air='" . $escape($ssd_air) . "', ssd_wc_ratio='" . $escape($ssd_wc) . "',
        ssd_admix='" . $escape($ssd_admix) . "', ssd_others='" . $escape($ssd_others) . "'
        WHERE ssd_id=$vb_id";

    $vb_success = $conn->query($update_vb);
    $ssd_success = $conn->query($update_ssd);

    if ($vb_success && $ssd_success) {
        echo "<script>alert('Design updated successfully!');window.location.href='concrete_design_list.php';</script>";
        exit;
    } else {
        $error = "";
        if (!$vb_success)
            $error .= "VB Error: " . $conn->error . "<br>";
        if (!$ssd_success)
            $error .= "SSD Error: " . $conn->error;
        echo "<script>alert('Error updating design: {$error}');</script>";
    }
}

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
            max-width: 900px;
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

        .section-title {
            background: #0b63c4;
            color: white;
            padding: 10px 15px;
            margin: 20px 0 15px 0;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }

        .section-title.ssd {
            background: #4CAF50;
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

        .form-row input.vb-field {
            border-left: 4px solid #0b63c4;
        }

        .form-row input.ssd-field {
            border-left: 4px solid #4CAF50;
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

                <div class="section-title">Main Design</div>

                <div class="form-row">
                    <label>Concrete PSI:</label>
                    <input type="text" name="concrete_psi" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_concrete_psi'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Curation Days:</label>
                    <input type="text" name="curation_days" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_curation_days'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Type:</label>
                    <input type="text" name="concrete_type" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_concrete_type'] ?? ''); ?>">
                </div>

                <!-- Volume Batch Section -->
                <div class="section-title">Volume Batch (VB)</div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <input type="text" name="cubic_meter" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_cubic_meter'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Slump:</label>
                    <input type="text" name="concrete_slump" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_concrete_slump'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>W/C Ratio:</label>
                    <input type="text" name="wc_ratio" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_wc_ratio'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Cement Source:</label>
                    <input type="text" name="cement_source" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_cement_source'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Admix Source:</label>
                    <input type="text" name="admix_source" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_admix_source'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input type="text" name="cement_mix" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_cement_mix'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Price:</label>
                    <input type="text" name="concrete_price" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['concrete_price'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input type="text" name="fly_ash" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_fly_ash'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input type="text" name="sand" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_sand'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input type="text" name="three_eight" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_three_eight'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>3/4":</label>
                    <input type="text" name="three_fourth" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_three_fourth'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input type="text" name="G1" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_G1'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input type="text" name="admix" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_admix'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input type="text" name="water" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_water'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input type="text" name="air" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_air'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input type="text" name="others" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_others'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input type="text" name="total" class="vb-field"
                        value="<?php echo htmlspecialchars($vb_design['vb_total'] ?? ''); ?>">
                </div>

                <!-- Saturated Surface Dry Section -->
                <div class="section-title ssd">Saturated Surface Dry (SSD)</div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <input type="text" name="vb_cubic_meter" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['vb_cubic_meter'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Cement Source:</label>
                    <input type="text" name="ssd_cement_source" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_cement_source'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input type="text" name="ssd_cement_mix" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_cement_mix'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input type="text" name="ssd_fly_ash" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_fly_ash'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>3/4":</label>
                    <input type="text" name="ssd_three_fourth" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_three_fourth'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input type="text" name="ssd_three_eight" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_three_eight'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input type="text" name="ssd_water" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_water'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Slump:</label>
                    <input type="text" name="vb_concrete_slump" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['vb_concrete_slump'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Admix Source:</label>
                    <input type="text" name="ssd_admix_source" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_admix_source'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input type="text" name="ssd_sand" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_sand'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input type="text" name="ssd_G1" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_G1'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input type="text" name="ssd_air" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_air'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>W/C Ratio:</label>
                    <input type="text" name="ssd_wc_ratio" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_wc_ratio'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input type="text" name="ssd_admix" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_admix'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input type="text" name="ssd_others" class="ssd-field"
                        value="<?php echo htmlspecialchars($ssd_design['ssd_others'] ?? ''); ?>">
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