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

    // **FIXED: Only columns that exist in your database**
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
        cdl_is_active='" . $escape($_POST['is_active']) . "',
        updated_at=NOW()

    WHERE id=$mix_id";

    if ($conn->query($update)) {
        header("Location: concrete_design_list.php");
        exit;
    } else {
        $error = "Error: " . $conn->error;
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

        .form-grid h2 {
            grid-column: 1 / -1;
            text-align: center;
            margin-bottom: 10px;
            background: #ffffff;
            color: #333;
            padding: 17px;
            border-radius: 8px;
        }

        .form-grid {
            align-items: start;
            margin-top: 25px;
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
            display: inline-block;
            line-height: 1.5;
        }

        .btn-cancel:hover {
            background: #5a6268;
        }

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

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 12px;
            align-items: center;
        }

        .form-row label {
            width: 160px;
            font-weight: bold;
            min-width: 160px;
        }

        .form-row input,
        .form-row select,
        .form-row textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            min-width: 200px;
        }

        .vb-field {
            border-left: 4px solid #0b63c4;
            padding-left: 10px;
        }

        .ssd-field {
            border-left: 4px solid #4CAF50;
            padding-left: 10px;
        }

        .error {
            color: red;
            padding: 10px;
            background: #ffe6e6;
            border-radius: 5px;
            margin: 20px 30px;
        }

        @media (max-width: 1100px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form id="addForm" method="POST">
        <div class="form-grid">
            <h2>Edit Customized Mix #<?php echo $mix_id; ?></h2>

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
                    <label>Cubic Meter <span style="color:red;">*</span>:</label>
                    <select name="vb_cubic_meter" class="vb-field required-field" id="cubicMeterSelect" required>
                        <option value="">Select Cubic Meter</option>
                        <option value="3/4" <?php echo ($data['cdl_vb_cubic_meter'] == '3/4') ? 'selected' : ''; ?>>3/4
                        </option>
                        <option value="others" <?php echo $meter_is_other ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <input type="text" name="vb_cubic_meter_other" id="cubicMeterOther" class="vb-field required-field"
                        value="<?php echo $meter_is_other ? htmlspecialchars($data['cdl_vb_cubic_meter']) : ''; ?>"
                        placeholder="Enter cubic meter"
                        style="display:<?php echo $meter_is_other ? 'block' : 'none'; ?>; margin-top:5px;" <?php echo $meter_is_other ? 'required' : ''; ?>>
                </div>

                <!-- Concrete Slump -->
                <div class="form-row">
                    <label>Concrete Slump <span style="color:red;">*</span>:</label>
                    <input name="vb_concrete_slump" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_concrete_slump'] ?? ''); ?>"
                        placeholder='6" - 8"' required>
                </div>

                <!-- W/C Ratio -->
                <div class="form-row">
                    <label>W/C Ratio <span style="color:red;">*</span>:</label>
                    <input name="vb_wc_ratio" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_wc_ratio'] ?? ''); ?>" placeholder="0.00"
                        required>
                </div>

                <!-- Cement Source -->
                <div class="form-row">
                    <label>Cement Source <span style="color:red;">*</span>:</label>
                    <select name="vb_cement_source" class="vb-field required-field" id="cementSourceSelect" required>
                        <option value="">Select Cement Source</option>
                        <option value="Portland" <?php echo ($data['cdl_vb_cement_source'] == 'Portland') ? 'selected' : ''; ?>>Portland</option>
                        <option value="others" <?php echo $cement_is_other ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <input type="text" name="vb_cement_source_other" id="cementSourceOther"
                        value="<?php echo $cement_is_other ? htmlspecialchars($data['cdl_vb_cement_source']) : ''; ?>"
                        class="vb-field required-field"
                        style="display:<?php echo $cement_is_other ? 'block' : 'none'; ?>; margin-top:5px;" <?php echo $cement_is_other ? 'required' : ''; ?>>
                </div>

                <!-- Admix Source -->
                <div class="form-row">
                    <label>Admix Source <span style="color:red;">*</span>:</label>
                    <select name="vb_admix_source" class="vb-field required-field" id="admixSourceSelect" required>
                        <option value="">Select Admix Source</option>
                        <option value="Proem" <?php echo ($data['cdl_vb_admix_source'] == 'Proem') ? 'selected' : ''; ?>>
                            Proem</option>
                        <option value="others" <?php echo $admix_is_other ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <input type="text" name="vb_admix_source_other" id="admixSourceOther"
                        value="<?php echo $admix_is_other ? htmlspecialchars($data['cdl_vb_admix_source']) : ''; ?>"
                        class="vb-field required-field"
                        style="display:<?php echo $admix_is_other ? 'block' : 'none'; ?>; margin-top:5px;" <?php echo $admix_is_other ? 'required' : ''; ?>>
                </div>

                <!-- Concrete PSI -->
                <div class="form-row">
                    <label>Concrete PSI <span style="color:red;">*</span>:</label>
                    <select name="concrete_psi" class="vb-field required-field" id="psiSelect" required>
                        <option value="">Select PSI</option>
                        <?php foreach ($psiOptions as $psi): ?>
                            <option value="<?php echo $psi; ?>" <?php echo ($data['cdl_vb_concrete_psi'] == $psi) ? 'selected' : ''; ?>><?php echo $psi; ?></option>
                        <?php endforeach; ?>
                        <option value="others" <?php echo $psi_is_other ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <input type="text" name="concrete_psi_other" id="psiOther" class="vb-field required-field"
                        value="<?php echo $psi_is_other ? htmlspecialchars($data['cdl_vb_concrete_psi']) : ''; ?>"
                        style="display:<?php echo $psi_is_other ? 'block' : 'none'; ?>; margin-top:5px;" <?php echo $psi_is_other ? 'required' : ''; ?>>
                </div>

                <!-- Curing Days -->
                <div class="form-row">
                    <label>Curing Days <span style="color:red;">*</span>:</label>
                    <select name="curation_days" class="vb-field required-field" id="curingDaysSelect" required>
                        <option value="">Select Days</option>
                        <?php foreach ($daysOptions as $day): ?>
                            <option value="<?php echo $day; ?>" <?php echo ($data['cdl_vb_curation_days'] == $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                        <?php endforeach; ?>
                        <option value="others" <?php echo $days_is_other ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <input type="text" name="curation_days_other" id="curingDaysOther" class="vb-field required-field"
                        value="<?php echo $days_is_other ? htmlspecialchars($data['cdl_vb_curation_days']) : ''; ?>"
                        style="display:<?php echo $days_is_other ? 'block' : 'none'; ?>; margin-top:5px;" <?php echo $days_is_other ? 'required' : ''; ?>>
                </div>

                <!-- Concrete Type -->
                <div class="form-row">
                    <label>Concrete Type <span style="color:red;">*</span>:</label>
                    <select name="concrete_type" class="vb-field required-field" id="concreteTypeSelect" required>
                        <option value="">Select Type</option>
                        <?php foreach ($typeOptions as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($data['cdl_vb_concrete_type'] == $type) ? 'selected' : ''; ?>>
                                <?php echo $type; ?>
                                </option>
                        <?php endforeach; ?>
                        <option value="others" <?php echo $type_is_other ? 'selected' : ''; ?>>Others</option>
                    </select>
                    <input type="text" name="concrete_type_other" id="concreteTypeOther" class="vb-field required-field"
                        value="<?php echo $type_is_other ? htmlspecialchars($data['cdl_vb_concrete_type']) : ''; ?>"
                       
                        style="display:<?php echo $type_is_other ? 'block' : 'none'; ?>; margin-top:5px;" <?php echo $type_is_other ? 'required' : ''; ?>>
                </div>

                <!-- Client Name -->
                <div class="form-row">
                    <label>Client Name <span style="color:red;">*</span>:</label>
                    <input name="client_name" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_client_name'] ?? ''); ?>" required>
                </div>

                <!-- Client Contact -->
                <div class="form-row">
                    <label>Client Contact <span style="color:red;">*</span>:</label>
                    <input name="client_contact" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_client_contact'] ?? ''); ?>" required>
                </div>

                <!-- Remarks (if cdl_remarks exists) -->
            <?php if (isset($data['cdl_remarks'])): ?>
                    <div class="form-row">
                        <label>Remarks:</label>
                        <textarea name="cdl_remarks"
                            class="vb-field"><?php echo htmlspecialchars($data['cdl_remarks'] ?? ''); ?></textarea>
                        </div>
                <?php endif; ?>

                <!-- Is Active -->
                <div class="form-row">
                    <label>Is Active <span style="color:red;">*</span>:</label>
                    <select name="is_active" class="vb-field required-field" required>
                        <option value="">Select Status</option>
                        <option value="1" <?php echo ($data['cdl_is_active'] == '1') ? 'selected' : ''; ?>>Active
                        </option>
                        <option value="0" <?php echo ($data['cdl_is_active'] == '0') ? 'selected' : ''; ?>>Inactive
                        </option>
                    </select>
                </div>

                <!-- Concrete Price -->
                <div class="form-row">
                    <label>Concrete Price <span style="color:red;">*</span>:</label>
                    <input name="concrete_price" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_concrete_price'] ?? ''); ?>" required>
                </div>
            </div>

            <!-- ================= VB CARD ================= -->
            <div class="form-card">
                <div class="section-title">Volume Batch (VB)</div>

                <div class="form-row">
                    <label>Cement Mix <span style="color:red;">*</span>:</label>
                    <input name="vb_cement_mix" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_cement_mix'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input name="vb_fly_ash" class="vb-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_fly_ash'] ?? ''); ?>" placeholder="3.00">
                </div>

                <div class="form-row">
                    <label>Sand <span style="color:red;">*</span>:</label>
                    <input name="vb_sand" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_sand'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input name="vb_three_eight" class="vb-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_three_eight'] ?? ''); ?>" placeholder="42.55">
                </div>

                <div class="form-row">
                    <label>3/4" <span style="color:red;">*</span>:</label>
                    <input name="vb_three_fourth" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_three_fourth'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input name="vb_G1" class="vb-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_G1'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Admix <span style="color:red;">*</span>:</label>
                    <input name="vb_admix" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_admix'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>Water <span style="color:red;">*</span>:</label>
                    <input name="vb_water" class="vb-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_water'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input name="vb_air" class="vb-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_air'] ?? ''); ?>" placeholder="0.001 m³">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input name="vb_others" class="vb-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_others'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input name="vb_total" class="vb-field"
                        value="<?php echo htmlspecialchars($data['cdl_vb_total'] ?? ''); ?>"
                        placeholder="2348.8 kgs/m³">
                </div>
            </div>

            <!-- ================= SSD CARD ================= -->
            <div class="form-card">
                <div class="section-title ssd">Saturated Surface Dry (SSD)</div>

                <div class="form-row">
                    <label>Cement Mix <span style="color:red;">*</span>:</label>
                    <input name="ssd_cement_mix" class="ssd-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_cement_mix'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input name="ssd_fly_ash" class="ssd-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_fly_ash'] ?? ''); ?>" placeholder="3/4">
                </div>

                <div class="form-row">
                    <label>Sand <span style="color:red;">*</span>:</label>
                    <input name="ssd_sand" class="ssd-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_sand'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input name="ssd_three_eight" class="ssd-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_three_eight'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>3/4" <span style="color:red;">*</span>:</label>
                    <input name="ssd_three_fourth" class="ssd-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_three_fourth'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input name="ssd_G1" class="ssd-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_G1'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Admix <span style="color:red;">*</span>:</label>
                    <input name="ssd_admix" class="ssd-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_admix'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>Water <span style="color:red;">*</span>:</label>
                    <input name="ssd_water" class="ssd-field required-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_water'] ?? ''); ?>" required>
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input name="ssd_air" class="ssd-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_air'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input name="ssd_others" class="ssd-field"
                        value="<?php echo htmlspecialchars($data['cdl_ssd_others'] ?? ''); ?>">
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