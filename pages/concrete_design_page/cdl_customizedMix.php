<?php
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cdl_vb_concrete_psi = ($_POST['vb_concrete_psi'] === 'others' ? ($_POST['vb_concrete_psi_others'] ?? '') : $_POST['vb_concrete_psi']) ?? '';
    $cdl_vb_cubic_meter = ($_POST['vb_cubic_meter'] === 'others' ? ($_POST['vb_cubic_meter_others'] ?? '') : $_POST['vb_cubic_meter']) ?? '';
    $cdl_vb_curation_days = ($_POST['vb_curation_days'] === 'others' ? ($_POST['vb_curation_days_others'] ?? '') : $_POST['vb_curation_days']) ?? '';
    $cdl_vb_concrete_type = ($_POST['vb_concrete_type'] === 'others' ? ($_POST['vb_concrete_type_others'] ?? '') : $_POST['vb_concrete_type']) ?? '';
    $cdl_vb_concrete_slump = $_POST['vb_concrete_slump'] ?? '';
    $cdl_vb_wc_ratio = $_POST['vb_wc_ratio'] ?? '';
    $cdl_vb_cement_source = ($_POST['vb_cement_source'] === 'others' ? ($_POST['vb_cement_source_others'] ?? '') : $_POST['vb_cement_source']) ?? '';
    $cdl_vb_admix_source = ($_POST['vb_admix_source'] === 'others' ? ($_POST['vb_admix_source_others'] ?? '') : $_POST['vb_admix_source']) ?? '';

    $cdl_vb_cement_mix = $_POST['vb_cement_mix'] ?? '';
    $cdl_vb_fly_ash = $_POST['vb_fly_ash'] ?? '';
    $cdl_vb_sand = $_POST['vb_sand'] ?? '';
    $cdl_vb_three_eight = $_POST['vb_three_eight'] ?? '';
    $cdl_vb_three_fourth = $_POST['vb_three_fourth'] ?? '';
    $cdl_vb_G1 = $_POST['vb_G1'] ?? '';
    $cdl_vb_admix = $_POST['vb_admix'] ?? '';
    $cdl_vb_water = $_POST['vb_water'] ?? '';
    $cdl_vb_air = $_POST['vb_air'] ?? '';
    $cdl_vb_others = $_POST['vb_others'] ?? '';
    $cdl_vb_total = $_POST['vb_total'] ?? '';
    $cdl_concrete_price = $_POST['concrete_price'] ?? '';

    $cdl_ssd_cement_mix = $_POST['ssd_cement_mix'] ?? '';
    $cdl_ssd_fly_ash = $_POST['ssd_fly_ash'] ?? '';
    $cdl_ssd_sand = $_POST['ssd_sand'] ?? '';
    $cdl_ssd_three_eight = $_POST['ssd_three_eight'] ?? '';
    $cdl_ssd_three_fourth = $_POST['ssd_three_fourth'] ?? '';
    $cdl_ssd_G1 = $_POST['ssd_G1'] ?? '';
    $cdl_ssd_admix = $_POST['ssd_admix'] ?? '';
    $cdl_ssd_water = $_POST['ssd_water'] ?? '';
    $cdl_ssd_air = $_POST['ssd_air'] ?? '';
    $cdl_ssd_others = $_POST['ssd_others'] ?? '';

    $cdl_client_name = $_POST['client_name'] ?? '';
    $cdl_client_contact = $_POST['client_contact'] ?? '';
    $cdl_experiment_notes = $_POST['experiment_notes'] ?? '';
    $cdl_customization_notes = $_POST['customization_notes'] ?? '';
    $cdl_is_active = $_POST['is_active'] ?? 1;

    $created_by = $_SESSION['username'] ?? 'admin';

    $escape = function ($value) use ($conn) {
        return $conn->real_escape_string($value ?? '');
    };

    $insert = "INSERT INTO cdl_customized_mix_list (
        cdl_vb_concrete_psi, cdl_vb_cubic_meter, cdl_vb_curation_days, cdl_vb_concrete_type, cdl_vb_concrete_slump,
        cdl_vb_wc_ratio, cdl_vb_cement_source, cdl_vb_admix_source, cdl_vb_cement_mix, cdl_vb_fly_ash,
        cdl_vb_sand, cdl_vb_three_eight, cdl_vb_three_fourth, cdl_vb_G1, cdl_vb_admix, cdl_vb_water,
        cdl_vb_air, cdl_vb_others, cdl_vb_total, cdl_concrete_price,
        cdl_ssd_cement_mix, cdl_ssd_fly_ash, cdl_ssd_sand, cdl_ssd_three_eight, cdl_ssd_three_fourth,
        cdl_ssd_G1, cdl_ssd_admix, cdl_ssd_water, cdl_ssd_air, cdl_ssd_others,
        cdl_client_name, cdl_client_contact, cdl_experiment_notes, cdl_customization_notes, cdl_is_active,
        created_by, created_at, updated_at
    ) VALUES (
        '" . $escape($cdl_vb_concrete_psi) . "', '" . $escape($cdl_vb_cubic_meter) . "', '" . $escape($cdl_vb_curation_days) . "', '" . $escape($cdl_vb_concrete_type) . "', '" . $escape($cdl_vb_concrete_slump) . "',
        '" . $escape($cdl_vb_wc_ratio) . "', '" . $escape($cdl_vb_cement_source) . "', '" . $escape($cdl_vb_admix_source) . "', '" . $escape($cdl_vb_cement_mix) . "', '" . $escape($cdl_vb_fly_ash) . "',
        '" . $escape($cdl_vb_sand) . "', '" . $escape($cdl_vb_three_eight) . "', '" . $escape($cdl_vb_three_fourth) . "', '" . $escape($cdl_vb_G1) . "', '" . $escape($cdl_vb_admix) . "', '" . $escape($cdl_vb_water) . "',
        '" . $escape($cdl_vb_air) . "', '" . $escape($cdl_vb_others) . "', '" . $escape($cdl_vb_total) . "', '" . $escape($cdl_concrete_price) . "',
        '" . $escape($cdl_ssd_cement_mix) . "', '" . $escape($cdl_ssd_fly_ash) . "', '" . $escape($cdl_ssd_sand) . "', '" . $escape($cdl_ssd_three_eight) . "', '" . $escape($cdl_ssd_three_fourth) . "',
        '" . $escape($cdl_ssd_G1) . "', '" . $escape($cdl_ssd_admix) . "', '" . $escape($cdl_ssd_water) . "', '" . $escape($cdl_ssd_air) . "', '" . $escape($cdl_ssd_others) . "',
        '" . $escape($cdl_client_name) . "', '" . $escape($cdl_client_contact) . "', '" . $escape($cdl_experiment_notes) . "', '" . $escape($cdl_customization_notes) . "', '" . $escape($cdl_is_active) . "',
        '" . $escape($created_by) . "', NOW(), NOW()
    )";

    if ($conn->query($insert)) {
        $_SESSION['success_message'] = "Customized Mix added successfully!";
        header("Location: /Inventory-Accounts-System-v1/pages/concrete_design_page/concrete_design_list.php");
        exit();
    } else {
        $error_message = "Error adding customized mix: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Customized Mix</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <?php showHeader() ?>

    <style>
        html,
        body {
            overflow: hidden;
        }

        body {
            font-family: Arial;
            background: #BFC3AA;
            margin-top: 40px;
            padding-bottom: 60px;
        }

        .form-container {
            max-width: 1400px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        /* HEADER TITLE (UNCHANGED) */
        .form-grid h2 {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 10px;
            background: #ffffff;
            color: #333;
            padding: 17px;
            border-radius: 8px;
        }

        /* MOVE CARDS SLIGHTLY DOWN */
        .form-grid {
            align-items: start;
            margin-top: 25px;
            /* reduced from 100px */
            margin-left: 30px;
            margin-right: 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .form-buttons {
            grid-column: 1 / -1;
            display: flex;
            justify-content: left;
            align-items: left;
            gap: 10px;
            margin: 10px 0 20px 0;
        }

        .btn-submit {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-submit:hover {
            background: #218838;
        }

        .btn-cancel {
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

        /* CARD STYLE */
        .form-card {
            height: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 8px;
            background: #fff;
            max-height: 400px;
            overflow: auto;
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

        /* FORM ROWS */
        .form-row {
            display: flex;
            flex-wrap: wrap;
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

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

</head>

<body>

    <form id="addForm" method="POST">

        <div class="form-grid">
            <h2>Add Customized Concrete Mix</h2>

            <!-- BUTTONS -->
            <div class="form-buttons">
                <button class="btn-submit" type="submit">Save Customized Mix</button>
                <a href="concrete_design_list.php" class="btn-cancel">Cancel</a>
            </div>

            <!-- ================= CLIENT CARD ================= -->
            <div class="form-card">
                <div class="section-title">Client & Experiment Info</div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <select name="vb_cubic_meter" class="vb-field">
                        <option value="3/4">3/4</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Concrete Slump:</label>
                    <input name="vb_concrete_slump" class="vb-field" placeholder='6" - 8"'>
                </div>

                <div class="form-row">
                    <label>W/C Ratio:</label>
                    <input name="vb_wc_ratio" class="vb-field" placeholder="0.00">
                </div>

                <div class="form-row">
                    <label>Cement Source:</label>
                    <select name="vb_cement_source" class="vb-field">
                        <option value="Portland">Portland</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Admix Source:</label>
                    <select name="vb_admix_source" class="vb-field">
                        <option value="Proem">Proem</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Concrete PSI:</label>
                    <input type="text" name="concrete_psi" class="form-control">
                </div>

                <div class="form-row">
                    <label>Curation Days:</label>
                    <input type="number" name="curation_days" class="form-control">
                </div>

                <div class="form-row">
                    <label>Concrete Type:</label>
                    <input type="text" name="concrete_type" class="form-control">
                </div>

                <div class="form-row">
                    <label>Client Name:</label>
                    <input name="client_name" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Client Contact:</label>
                    <input name="client_contact" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Experiment Notes:</label>
                    <textarea name="experiment_notes" class="vb-field"></textarea>
                </div>

                <div class="form-row">
                    <label>Customization Notes:</label>
                    <textarea name="customization_notes" class="vb-field"></textarea>
                </div>

                <div class="form-row">
                    <label>Is Active:</label>
                    <select name="is_active" class="vb-field">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="form-row">
                    <label>Concrete Price:</label>
                    <input name="concrete_price" class="vb-field">
                </div>
            </div>

            <!-- ================= VB CARD ================= -->
            <div class="form-card">
                <div class="section-title">Volume Batch (VB)</div>


                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input name="vb_cement_mix" class="vb-field" placeholder="29.00 kgs/0.50 m³">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input name="vb_fly_ash" class="vb-field" placeholder="3.00">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input name="vb_sand" class="vb-field" placeholder="36.60 kgs/0.50 m³">
                </div>

                <div class="form-row">
                    <label>3/8\":</label>
                    <input name="vb_three_eight" class="vb-field" placeholder="42.55">
                </div>

                <div class="form-row">
                    <label>3/4\":</label>
                    <input name="vb_three_fourth" class="vb-field" placeholder="42.55 kgs/0.50 m³">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input name="vb_G1" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input name="vb_admix" class="vb-field" placeholder="0.29 ltr">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input name="vb_water" class="vb-field" placeholder="9.00 ltr">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input name="vb_air" class="vb-field" placeholder="0.001 m³">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input name="vb_others" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input name="vb_total" class="vb-field" placeholder="2348.8 kgs/m³">
                </div>



            </div>

            <!-- ================= SSD CARD ================= -->
            <div class="form-card">
                <div class="section-title ssd">Saturated Surface Dry (SSD)</div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input name="ssd_cement_mix" class="ssd-field" placeholder="580.00">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input name="ssd_fly_ash" class="ssd-field" placeholder="3/4">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input name="ssd_sand" class="ssd-field" placeholder="732.00">
                </div>

                <div class="form-row">
                    <label>3/8\":</label>
                    <input name="ssd_three_eight" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>3/4\":</label>
                    <input name="ssd_three_fourth" class="ssd-field" placeholder="851.00">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input name="ssd_G1" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input name="ssd_admix" class="ssd-field" placeholder="5.80">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input name="ssd_water" class="ssd-field" placeholder="180.00">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input name="ssd_air" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input name="ssd_others" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input name="vb_total" class="vb-field" placeholder="2348.8 kgs/m³">
                </div>

            </div>



        </div>



    </form>

</body>

</html>