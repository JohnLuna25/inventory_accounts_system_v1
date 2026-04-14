<?php
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

/** @var mysqli $conn */

// Safety check
if (!isset($conn)) {
    die("Database connection not established.");
}

$mix_id = intval($_GET['id'] ?? 0);

if ($mix_id <= 0) {
    die("No customized mix selected.");
}

// Fetch data
$result = $conn->query("SELECT * FROM cdl_customized_mix_list WHERE id = $mix_id");

if (!$result || $result->num_rows == 0) {
    die("Customized mix not found.");
}

$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $escape = function ($val) use ($conn) {
        return $conn->real_escape_string($val ?? '');
    };

    $vb_concrete_psi = $_POST['vb_concrete_psi'] ?? '';
    $vb_cubic_meter = $_POST['vb_cubic_meter'] ?? '';
    $vb_curation_days = $_POST['vb_curation_days'] ?? '';
    $vb_concrete_type = $_POST['vb_concrete_type'] ?? '';
    $vb_concrete_slump = $_POST['vb_concrete_slump'] ?? '';
    $vb_wc_ratio = $_POST['vb_wc_ratio'] ?? '';
    $vb_cement_source = $_POST['vb_cement_source'] ?? '';
    $vb_admix_source = $_POST['vb_admix_source'] ?? '';
    $vb_cement_mix = $_POST['vb_cement_mix'] ?? '';
    $vb_fly_ash = $_POST['vb_fly_ash'] ?? '';
    $vb_sand = $_POST['vb_sand'] ?? '';
    $vb_three_eight = $_POST['vb_three_eight'] ?? '';
    $vb_three_fourth = $_POST['vb_three_fourth'] ?? '';
    $vb_G1 = $_POST['vb_G1'] ?? '';
    $vb_admix = $_POST['vb_admix'] ?? '';
    $vb_water = $_POST['vb_water'] ?? '';
    $vb_air = $_POST['vb_air'] ?? '';
    $vb_others = $_POST['vb_others'] ?? '';
    $vb_total = $_POST['vb_total'] ?? '';
    $concrete_price = $_POST['concrete_price'] ?? '';

    $ssd_cement_mix = $_POST['ssd_cement_mix'] ?? '';
    $ssd_fly_ash = $_POST['ssd_fly_ash'] ?? '';
    $ssd_sand = $_POST['ssd_sand'] ?? '';
    $ssd_three_eight = $_POST['ssd_three_eight'] ?? '';
    $ssd_three_fourth = $_POST['ssd_three_fourth'] ?? '';
    $ssd_G1 = $_POST['ssd_G1'] ?? '';
    $ssd_admix = $_POST['ssd_admix'] ?? '';
    $ssd_water = $_POST['ssd_water'] ?? '';
    $ssd_air = $_POST['ssd_air'] ?? '';
    $ssd_others = $_POST['ssd_others'] ?? '';

    $client_name = $_POST['client_name'] ?? '';
    $client_contact = $_POST['client_contact'] ?? '';
    $experiment_notes = $_POST['experiment_notes'] ?? '';
    $customization_notes = $_POST['customization_notes'] ?? '';
    $is_active = $_POST['is_active'] ?? 1;

    $update = "UPDATE cdl_customized_mix_list SET
        cdl_vb_concrete_psi='" . $escape($vb_concrete_psi) . "',
        cdl_vb_cubic_meter='" . $escape($vb_cubic_meter) . "',
        cdl_vb_curation_days='" . $escape($vb_curation_days) . "',
        cdl_vb_concrete_type='" . $escape($vb_concrete_type) . "',
        cdl_vb_concrete_slump='" . $escape($vb_concrete_slump) . "',
        cdl_vb_wc_ratio='" . $escape($vb_wc_ratio) . "',
        cdl_vb_cement_source='" . $escape($vb_cement_source) . "',
        cdl_vb_admix_source='" . $escape($vb_admix_source) . "',
        cdl_vb_cement_mix='" . $escape($vb_cement_mix) . "',
        cdl_vb_fly_ash='" . $escape($vb_fly_ash) . "',
        cdl_vb_sand='" . $escape($vb_sand) . "',
        cdl_vb_three_eight='" . $escape($vb_three_eight) . "',
        cdl_vb_three_fourth='" . $escape($vb_three_fourth) . "',
        cdl_vb_G1='" . $escape($vb_G1) . "',
        cdl_vb_admix='" . $escape($vb_admix) . "',
        cdl_vb_water='" . $escape($vb_water) . "',
        cdl_vb_air='" . $escape($vb_air) . "',
        cdl_vb_others='" . $escape($vb_others) . "',
        cdl_vb_total='" . $escape($vb_total) . "',
        cdl_concrete_price='" . $escape($concrete_price) . "',

        cdl_ssd_cement_mix='" . $escape($ssd_cement_mix) . "',
        cdl_ssd_fly_ash='" . $escape($ssd_fly_ash) . "',
        cdl_ssd_sand='" . $escape($ssd_sand) . "',
        cdl_ssd_three_eight='" . $escape($ssd_three_eight) . "',
        cdl_ssd_three_fourth='" . $escape($ssd_three_fourth) . "',
        cdl_ssd_G1='" . $escape($ssd_G1) . "',
        cdl_ssd_admix='" . $escape($ssd_admix) . "',
        cdl_ssd_water='" . $escape($ssd_water) . "',
        cdl_ssd_air='" . $escape($ssd_air) . "',
        cdl_ssd_others='" . $escape($ssd_others) . "',

        cdl_client_name='" . $escape($client_name) . "',
        cdl_client_contact='" . $escape($client_contact) . "',
        cdl_experiment_notes='" . $escape($experiment_notes) . "',
        cdl_customization_notes='" . $escape($customization_notes) . "',
        cdl_is_active='" . $escape($is_active) . "',
        updated_at=NOW()

        WHERE id=$mix_id";

    if ($conn->query($update)) {
        header("Location: concrete_design_list.php");
        exit;
    } else {
        echo "<script>alert('Error: {$conn->error}');</script>";
    }
}

// Close connection at the very end
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Customized Mix</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <?php showHeader(); ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
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

        .section-title {
            background: #0b63c4;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0 15px;
        }

        .section-title.ssd {
            background: #4CAF50;
        }

        .form-row {
            display: flex;
            margin-bottom: 15px;
        }

        .form-row label {
            width: 180px;
            font-weight: bold;
        }

        .form-row input,
        textarea,
        select {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="main-content" style="padding-top:140px;">
        <div class="form-container">

            <h2>Edit Customized Mix</h2>

            <form method="POST">

                <div class="section-title">Volume Batch (VB)</div>

                <div class="form-row">
                    <label>Concrete PSI:</label>
                    <input type="text" name="vb_concrete_psi" value="<?= $data['cdl_vb_concrete_psi'] ?>">
                </div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <input type="text" name="vb_cubic_meter" value="<?= $data['cdl_vb_cubic_meter'] ?>">
                </div>

                <div class="form-row">
                    <label>Curation Days:</label>
                    <input type="text" name="vb_curation_days" value="<?= $data['cdl_vb_curation_days'] ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Type:</label>
                    <input type="text" name="vb_concrete_type" value="<?= $data['cdl_vb_concrete_type'] ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Price:</label>
                    <input type="text" name="concrete_price" value="<?= $data['cdl_concrete_price'] ?>">
                </div>

                <div class="section-title ssd">SSD</div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input type="text" name="ssd_cement_mix" value="<?= $data['cdl_ssd_cement_mix'] ?>">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input type="text" name="ssd_fly_ash" value="<?= $data['cdl_ssd_fly_ash'] ?>">
                </div>

                <div class="section-title">Client Info</div>

                <div class="form-row">
                    <label>Client Name:</label>
                    <input type="text" name="client_name" value="<?= $data['cdl_client_name'] ?>">
                </div>

                <div class="form-row">
                    <label>Client Contact:</label>
                    <input type="text" name="client_contact" value="<?= $data['cdl_client_contact'] ?>">
                </div>

                <div class="form-row">
                    <label>Notes:</label>
                    <textarea name="experiment_notes"><?= $data['cdl_experiment_notes'] ?></textarea>
                </div>

                <div class="form-buttons">
                    <button class="btn-submit">Save Changes</button>
                    <a href="concrete_design_list.php" class="btn-cancel">Cancel</a>
                </div>

            </form>
        </div>
    </div>

</body>

</html>