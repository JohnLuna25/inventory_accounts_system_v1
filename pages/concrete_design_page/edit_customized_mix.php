<?php
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

session_start();

/* ================= SAFETY CHECK ================= */
if (!isset($conn)) {
    die("Database connection failed (conn is missing). Check db_connect.php");
}

$mix_id = intval($_GET['id'] ?? 0);
if ($mix_id <= 0)
    die("No customized mix selected.");

$result = $conn->query("SELECT * FROM cdl_customized_mix_list WHERE id = $mix_id");
if (!$result || $result->num_rows == 0)
    die("Customized mix not found.");

$data = $result->fetch_assoc();

/* ================= OPTIONS ================= */
$psiOptions = ["1500", "3000", "3500", "4000", "4500", "5000", "6000"];
$daysOptions = ["7", "14", "21"];
$typeOptions = ["Ordinary", "Pumpcrete Design", "Tremie"];
$meterOptions = ["3/4"];
$cementOptions = ["Portland"];
$admixOptions = ["Proem"];

/* ================= HELPERS ================= */
function isOther($value, $options)
{
    return !in_array($value, $options);
}

/* ================= DETECT "OTHERS" ================= */
$psi_is_other = isOther($data['cdl_vb_concrete_psi'], $psiOptions);
$days_is_other = isOther($data['cdl_vb_curation_days'], $daysOptions);
$type_is_other = isOther($data['cdl_vb_concrete_type'], $typeOptions);
$meter_is_other = isOther($data['cdl_vb_cubic_meter'], $meterOptions);
$cement_is_other = isOther($data['cdl_vb_cement_source'], $cementOptions);
$admix_is_other = isOther($data['cdl_vb_admix_source'], $admixOptions);

/* ================= POST ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $escape = function ($v) use ($conn) {
        return $conn->real_escape_string($v ?? '');
    };

    $getValue = function ($key, $otherKey) {
        return (($_POST[$key] ?? '') === 'others')
            ? ($_POST[$otherKey] ?? '')
            : ($_POST[$key] ?? '');
    };

    $update = "UPDATE cdl_customized_mix_list SET

        cdl_vb_cubic_meter='" . $escape($getValue('vb_cubic_meter', 'vb_cubic_meter_other')) . "',
        cdl_vb_concrete_slump='" . $escape($_POST['vb_concrete_slump']) . "',
        cdl_vb_wc_ratio='" . $escape($_POST['vb_wc_ratio']) . "',

        cdl_vb_cement_source='" . $escape($getValue('vb_cement_source', 'vb_cement_source_other')) . "',
        cdl_vb_admix_source='" . $escape($getValue('vb_admix_source', 'vb_admix_source_other')) . "',

        cdl_vb_concrete_psi='" . $escape($getValue('concrete_psi', 'concrete_psi_other')) . "',
        cdl_vb_curation_days='" . $escape($getValue('curation_days', 'curation_days_other')) . "',
        cdl_vb_concrete_type='" . $escape($getValue('concrete_type', 'concrete_type_other')) . "',

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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Customized Mix</title>
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
            margin-bottom: -5px;
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
            <h2>Edit Customized Concrete Mix</h2>

            <!-- BUTTONS -->
            <div class="form-buttons">
                <button class="btn-submit" type="submit">Save Customized Mix</button>
                <a href="concrete_design_list.php" class="btn-cancel">Cancel</a>
            </div>

            <!-- ================= CLIENT CARD ================= -->
            <div class="form-card">
                <div class="section-title">Client & Experiment Info</div>

                <!-- Cubic Meter -->
                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <select name="vb_cubic_meter" class="vb-field" id="cubicMeterSelect">
                        <option value="3/4"<?= ($data['cdl_vb_cubic_meter'] == '3/4') ? 'selected' : '' ?>>3/4</option>
            <option value="others" <?= (!in_array($data['cdl_vb_cubic_meter'], ['3/4'])) ? 'selected' : '' ?>>Others</option>
        </select>

        <input type="text"
               name="vb_cubic_meter_other"
               id="cubicMeterOther"
               class="vb-field"
               placeholder="Enter cubic meter"
               style="display:<?= in_array($data['cdl_vb_cubic_meter'], ['3/4']) ? 'none' : 'block' ?>; margin-top:5px;"
               value="<?= (!in_array($data['cdl_vb_cubic_meter'], ['3/4'])) ? htmlspecialchars($data['cdl_vb_cubic_meter']) : '' ?>">
    </div>

    <!-- Concrete Slump -->
    <div class="form-row">
        <label>Concrete Slump:</label>
        <input name="vb_concrete_slump"
               class="vb-field"
               value="<?= htmlspecialchars($data['cdl_vb_concrete_slump']) ?>">
    </div>

    <!-- W/C Ratio -->
    <div class="form-row">
        <label>W/C Ratio:</label>
        <input name="vb_wc_ratio"
               class="vb-field"
               value="<?= htmlspecialchars($data['cdl_vb_wc_ratio']) ?>">
    </div>

    <!-- Cement Source -->
    <div class="form-row">
        <label>Cement Source:</label>
        <select name="vb_cement_source" class="vb-field" id="cementSourceSelect">
            <option value="Portland" <?= ($data['cdl_vb_cement_source'] == 'Portland') ? 'selected' : '' ?>>Portland</option>
            <option value="others" <?= ($data['cdl_vb_cement_source'] != 'Portland') ? 'selected' : '' ?>>Others</option>
        </select>

        <input type="text"
               name="vb_cement_source_other"
               id="cementSourceOther"
               class="vb-field"
               style="display:<?= ($data['cdl_vb_cement_source'] == 'Portland') ? 'none' : 'block' ?>; margin-top:5px;"
               value="<?= ($data['cdl_vb_cement_source'] != 'Portland') ? htmlspecialchars($data['cdl_vb_cement_source']) : '' ?>">
    </div>

    <!-- Admix Source -->
    <div class="form-row">
        <label>Admix Source:</label>
        <select name="vb_admix_source" class="vb-field" id="admixSourceSelect">
            <option value="Proem" <?= ($data['cdl_vb_admix_source'] == 'Proem') ? 'selected' : '' ?>>Proem</option>
            <option value="others" <?= ($data['cdl_vb_admix_source'] != 'Proem') ? 'selected' : '' ?>>Others</option>
        </select>

        <input type="text"
               name="vb_admix_source_other"
               id="admixSourceOther"
               class="vb-field"
               style="display:<?= ($data['cdl_vb_admix_source'] == 'Proem') ? 'none' : 'block' ?>; margin-top:5px;"
               value="<?= ($data['cdl_vb_admix_source'] != 'Proem') ? htmlspecialchars($data['cdl_vb_admix_source']) : '' ?>">
    </div>

    <!-- Concrete PSI -->
    <div class="form-row">
        <label>Concrete PSI:</label>
        <select name="concrete_psi" class="form-control" id="psiSelect">
            <?php
            $psiOptions = ["1500", "3000", "3500", "4000", "4500", "5000", "6000"];
            $val = $data['cdl_vb_concrete_psi'];
            foreach ($psiOptions as $opt) {
                $sel = ($val == $opt) ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt</option>";
            }
            ?>
            <option value="others" <?= (!in_array($val, $psiOptions)) ? 'selected' : '' ?>>Others</option>
        </select>

        <input type="text"
               name="concrete_psi_other"
               id="psiOther"
               class="form-control"
               style="display:<?= in_array($val, $psiOptions) ? 'none' : 'block' ?>; margin-top:5px;"
               value="<?= (!in_array($val, $psiOptions)) ? htmlspecialchars($val) : '' ?>">
    </div>

    <!-- Curing Days -->
    <div class="form-row">
        <label>Curing Days:</label>
        <select name="curation_days" class="form-control" id="curingDaysSelect">
            <?php
            $daysOptions = ["7", "14", "21"];
            $val = $data['cdl_vb_curation_days'];
            foreach ($daysOptions as $opt) {
                $sel = ($val == $opt) ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt</option>";
            }
            ?>
            <option value="others" <?= (!in_array($val, $daysOptions)) ? 'selected' : '' ?>>Others</option>
        </select>

        <input type="text"
               name="curation_days_other"
               id="curingDaysOther"
               class="form-control"
               style="display:<?= in_array($val, $daysOptions) ? 'none' : 'block' ?>; margin-top:5px;"
               value="<?= (!in_array($val, $daysOptions)) ? htmlspecialchars($val) : '' ?>">
    </div>

    <!-- Concrete Type -->
    <div class="form-row">
        <label>Concrete Type:</label>
        <select name="concrete_type" class="form-control" id="concreteTypeSelect">
            <?php
            $typeOptions = ["Ordinary", "Pumpcrete Design", "Tremie"];
            $val = $data['cdl_vb_concrete_type'];
            foreach ($typeOptions as $opt) {
                $sel = ($val == $opt) ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt</option>";
            }
            ?>
            <option value="others" <?= (!in_array($val, $typeOptions)) ? 'selected' : '' ?>>Others</option>
        </select>

        <input type="text"
               name="concrete_type_other"
               id="concreteTypeOther"
               class="form-control"
               style="display:<?= in_array($val, $typeOptions) ? 'none' : 'block' ?>; margin-top:5px;"
               value="<?= (!in_array($val, $typeOptions)) ? htmlspecialchars($val) : '' ?>">
    </div>

    <!-- Client Name -->
    <div class="form-row">
        <label>Client Name:</label>
        <input name="client_name"
               class="vb-field"
               value="<?= htmlspecialchars($data['cdl_client_name']) ?>">
    </div>

    <!-- Client Contact -->
    <div class="form-row">
        <label>Client Contact:</label>
        <input name="client_contact"
               class="vb-field"
               value="<?= htmlspecialchars($data['cdl_client_contact']) ?>">
    </div>

    <!-- Experiment Notes -->
    <div class="form-row">
        <label>Experiment Notes:</label>
        <textarea name="experiment_notes" class="vb-field"><?= htmlspecialchars($data['cdl_experiment_notes']) ?></textarea>
    </div>

    <!-- Customization Notes -->
    <div class="form-row">
        <label>Customization Notes:</label>
        <textarea name="customization_notes" class="vb-field"><?= htmlspecialchars($data['cdl_customization_notes']) ?></textarea>
    </div>

    <!-- Is Active -->
    <div class="form-row">
        <label>Is Active:</label>
        <select name="is_active" class="vb-field">
            <option value="1" <?= ($data['cdl_is_active'] == 1) ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= ($data['cdl_is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <!-- Concrete Price -->
    <div class="form-row">
        <label>Concrete Price:</label>
        <input name="concrete_price"
               class="vb-field"
               value="<?= htmlspecialchars($data['cdl_concrete_price']) ?>">
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

    <!-- ================= JS HANDLER ================= -->
    <script>
        function handleOthers(selectId, inputId) {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);

            select.addEventListener("change", function () {
                if (this.value === "others") {
                    input.style.display = "block";
                    input.required = true;
                } else {
                    input.style.display = "none";
                    input.required = false;
                    input.value = "";
                }
            });
        }

        // Attach all fields
        handleOthers("cubicMeterSelect", "cubicMeterOther");
        handleOthers("cementSourceSelect", "cementSourceOther");
        handleOthers("admixSourceSelect", "admixSourceOther");
        handleOthers("psiSelect", "psiOther");
        handleOthers("curingDaysSelect", "curingDaysOther");
        handleOthers("concreteTypeSelect", "concreteTypeOther");
    </script>
</body>

</html>