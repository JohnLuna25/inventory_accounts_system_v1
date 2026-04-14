<?php
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$mix_id = intval($_GET['id'] ?? 0);

if ($mix_id <= 0) {
    die("No customized mix selected.");
}

$result = $conn->query("SELECT * FROM cdl_customized_mix_list WHERE id = $mix_id");

if (!$result || $result->num_rows == 0) {
    die("Customized mix not found.");
}

$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $escape = function ($v) use ($conn) {
        return $conn->real_escape_string($v ?? '');
    };

    $update = "UPDATE cdl_customized_mix_list SET
        cdl_vb_cubic_meter='" . $escape($_POST['vb_cubic_meter']) . "',
        cdl_vb_concrete_slump='" . $escape($_POST['vb_concrete_slump']) . "',
        cdl_vb_wc_ratio='" . $escape($_POST['vb_wc_ratio']) . "',
        cdl_vb_cement_source='" . $escape($_POST['vb_cement_source']) . "',
        cdl_vb_admix_source='" . $escape($_POST['vb_admix_source']) . "',
        cdl_vb_cement_mix='" . $escape($_POST['vb_cement_mix']) . "',
        cdl_vb_fly_ash='" . $escape($_POST['vb_fly_ash']) . "',
        cdl_vb_sand='" . $escape($_POST['vb_sand']) . "',
        cdl_vb_three_eight='" . $escape($_POST['vb_three_eight']) . "',
        cdl_vb_three_fourth='" . $escape($_POST['vb_three_fourth']) . "',
        cdl_vb_G1='" . $escape($_POST['vb_G1']) . "',
        cdl_vb_admix='" . $escape($_POST['vb_admix']) . "',
        cdl_vb_water='" . $escape($_POST['vb_water']) . "',
        cdl_vb_air='" . $escape($_POST['vb_air']) . "',
        cdl_vb_others='" . $escape($_POST['vb_others']) . "',
        cdl_vb_total='" . $escape($_POST['vb_total']) . "',
        cdl_concrete_price='" . $escape($_POST['concrete_price']) . "',

        cdl_ssd_cement_mix='" . $escape($_POST['ssd_cement_mix']) . "',
        cdl_ssd_fly_ash='" . $escape($_POST['ssd_fly_ash']) . "',
        cdl_ssd_sand='" . $escape($_POST['ssd_sand']) . "',
        cdl_ssd_three_eight='" . $escape($_POST['ssd_three_eight']) . "',
        cdl_ssd_three_fourth='" . $escape($_POST['ssd_three_fourth']) . "',
        cdl_ssd_G1='" . $escape($_POST['ssd_G1']) . "',
        cdl_ssd_admix='" . $escape($_POST['ssd_admix']) . "',
        cdl_ssd_water='" . $escape($_POST['ssd_water']) . "',
        cdl_ssd_air='" . $escape($_POST['ssd_air']) . "',
        cdl_ssd_others='" . $escape($_POST['ssd_others']) . "',

        cdl_client_name='" . $escape($_POST['client_name']) . "',
        cdl_client_contact='" . $escape($_POST['client_contact']) . "',
        cdl_experiment_notes='" . $escape($_POST['experiment_notes']) . "',
        cdl_customization_notes='" . $escape($_POST['customization_notes']) . "',
        cdl_is_active='" . $escape($_POST['is_active']) . "',
        updated_at=NOW()

        WHERE id=$mix_id";

    if ($conn->query($update)) {
        header("Location: concrete_design_list.php");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Edit Customized Mix</title>

    <?php showHeader() ?>

    <style>
        body {
            font-family: Arial;
            background: #BFC3AA;
            margin: 0;
            padding-bottom: 60px;
        }

        .form-grid {
            margin-top: 100px;
            margin-left: 30px;
            margin-right: 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .form-grid h2 {
            grid-column: 1 / -1;
            text-align: center;
            margin-top: 5px;
            margin-bottom: 10px;

            background: #ffffff;
            color: #333;
            padding: 25px;
            border-radius: 8px;
        }

        .form-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            background: #fff;
        }

        .section-title {
            background: #0b63c4;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .section-title.ssd {
            background: #4CAF50;
        }

        .form-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
        }

        .form-row label {
            width: 160px;
            font-weight: bold;
        }

        .form-row input,
        .form-row select,
        .form-row textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .vb-field {
            border-left: 4px solid #0b63c4;
        }

        .ssd-field {
            border-left: 4px solid #4CAF50;
        }

        .form-buttons {
            grid-column: 1 / -1;
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            border: none;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <form method="POST">

        <div class="form-grid">

            <h2>Edit Customized Concrete Mix</h2>

            <!-- VB -->
            <!-- ================= VB CARD ================= -->
            <div class="form-card">
                <div class="section-title">Volume Batch (VB)</div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <input name="vb_cubic_meter" class="vb-field" value="<?= $data['cdl_vb_cubic_meter'] ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Slump:</label>
                    <input name="vb_concrete_slump" class="vb-field" value="<?= $data['cdl_vb_concrete_slump'] ?>"
                        placeholder='6" - 8"'>
                </div>

                <div class="form-row">
                    <label>W/C Ratio:</label>
                    <input name="vb_wc_ratio" class="vb-field" value="<?= $data['cdl_vb_wc_ratio'] ?>">
                </div>

                <div class="form-row">
                    <label>Cement Source:</label>
                    <input name="vb_cement_source" class="vb-field" value="<?= $data['cdl_vb_cement_source'] ?>">
                </div>

                <div class="form-row">
                    <label>Admix Source:</label>
                    <input name="vb_admix_source" class="vb-field" value="<?= $data['cdl_vb_admix_source'] ?>">
                </div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input name="vb_cement_mix" class="vb-field" value="<?= $data['cdl_vb_cement_mix'] ?>">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input name="vb_fly_ash" class="vb-field" value="<?= $data['cdl_vb_fly_ash'] ?>">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input name="vb_sand" class="vb-field" value="<?= $data['cdl_vb_sand'] ?>">
                </div>

                <div class="form-row">
                    <label>3/8\":</label>
                    <input name="vb_three_eight" class="vb-field" value="<?= $data['cdl_vb_three_eight'] ?>">
                </div>

                <div class="form-row">
                    <label>3/4\":</label>
                    <input name="vb_three_fourth" class="vb-field" value="<?= $data['cdl_vb_three_fourth'] ?>">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input name="vb_G1" class="vb-field" value="<?= $data['cdl_vb_G1'] ?>">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input name="vb_admix" class="vb-field" value="<?= $data['cdl_vb_admix'] ?>">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input name="vb_water" class="vb-field" value="<?= $data['cdl_vb_water'] ?>">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input name="vb_air" class="vb-field" value="<?= $data['cdl_vb_air'] ?>">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input name="vb_others" class="vb-field" value="<?= $data['cdl_vb_others'] ?>">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input name="vb_total" class="vb-field" value="<?= $data['cdl_vb_total'] ?>">
                </div>

                <div class="form-row">
                    <label>Concrete Price:</label>
                    <input name="concrete_price" class="vb-field" value="<?= $data['cdl_concrete_price'] ?>">
                </div>
            </div>

            <!-- ================= SSD CARD ================= -->
            <div class="form-card">
                <div class="section-title ssd">Saturated Surface Dry (SSD)</div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input name="ssd_cement_mix" class="ssd-field" value="<?= $data['cdl_ssd_cement_mix'] ?>">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input name="ssd_fly_ash" class="ssd-field" value="<?= $data['cdl_ssd_fly_ash'] ?>">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input name="ssd_sand" class="ssd-field" value="<?= $data['cdl_ssd_sand'] ?>">
                </div>

                <div class="form-row">
                    <label>3/8\":</label>
                    <input name="ssd_three_eight" class="ssd-field" value="<?= $data['cdl_ssd_three_eight'] ?>">
                </div>

                <div class="form-row">
                    <label>3/4\":</label>
                    <input name="ssd_three_fourth" class="ssd-field" value="<?= $data['cdl_ssd_three_fourth'] ?>">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input name="ssd_G1" class="ssd-field" value="<?= $data['cdl_ssd_G1'] ?>">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input name="ssd_admix" class="ssd-field" value="<?= $data['cdl_ssd_admix'] ?>">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input name="ssd_water" class="ssd-field" value="<?= $data['cdl_ssd_water'] ?>">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input name="ssd_air" class="ssd-field" value="<?= $data['cdl_ssd_air'] ?>">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input name="ssd_others" class="ssd-field" value="<?= $data['cdl_ssd_others'] ?>">
                </div>
            </div>

            <!-- ================= CLIENT CARD ================= -->
            <div class="form-card">
                <div class="section-title">Client & Experiment Info</div>

                <div class="form-row">
                    <label>Client Name:</label>
                    <input name="client_name" class="vb-field" value="<?= $data['cdl_client_name'] ?>">
                </div>

                <div class="form-row">
                    <label>Client Contact:</label>
                    <input name="client_contact" class="vb-field" value="<?= $data['cdl_client_contact'] ?>">
                </div>

                <div class="form-row">
                    <label>Experiment Notes:</label>
                    <textarea name="experiment_notes" class="vb-field"><?= $data['cdl_experiment_notes'] ?></textarea>
                </div>

                <div class="form-row">
                    <label>Customization Notes:</label>
                    <textarea name="customization_notes"
                        class="vb-field"><?= $data['cdl_customization_notes'] ?></textarea>
                </div>

                <div class="form-row">
                    <label>Is Active:</label>
                    <select name="is_active" class="vb-field">
                        <option value="1" <?= ($data['cdl_is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= ($data['cdl_is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="form-buttons">
                <button class="btn-submit" type="submit">Save Changes</button>
                <a class="btn-cancel" href="concrete_design_list.php">Cancel</a>
            </div>

        </div>

    </form>

</body>

</html>