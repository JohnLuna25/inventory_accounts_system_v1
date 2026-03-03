<?php
// Include DB connection and functions
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VB Fields
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

    // SSD Fields
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

    // Client/Experiment Fields
    $client_name = $_POST['client_name'] ?? '';
    $client_contact = $_POST['client_contact'] ?? '';
    $experiment_notes = $_POST['experiment_notes'] ?? '';
    $customization_notes = $_POST['customization_notes'] ?? '';
    $is_active = $_POST['is_active'] ?? 1;

    // System Fields
    $created_by = $_SESSION['username'] ?? 'admin';

    // Escape values for SQL safety
    $escape = function ($value) use ($conn) {
        return $conn->real_escape_string($value ?? '');
    };

    // Insert into database
    $insert = "INSERT INTO cdl_customized_mix_list (
        vb_concrete_psi, vb_cubic_meter, vb_curation_days, vb_concrete_type, vb_concrete_slump,
        vb_wc_ratio, vb_cement_source, vb_admix_source, vb_cement_mix, vb_fly_ash,
        vb_sand, vb_three_eight, vb_three_fourth, vb_G1, vb_admix, vb_water,
        vb_air, vb_others, vb_total, concrete_price,
        ssd_cement_mix, ssd_fly_ash, ssd_sand, ssd_three_eight, ssd_three_fourth,
        ssd_G1, ssd_admix, ssd_water, ssd_air, ssd_others,
        client_name, client_contact, experiment_notes, customization_notes, is_active,
        created_by, created_at, updated_at
    ) VALUES (
        '" . $escape($vb_concrete_psi) . "', '" . $escape($vb_cubic_meter) . "', '" . $escape($vb_curation_days) . "', '" . $escape($vb_concrete_type) . "', '" . $escape($vb_concrete_slump) . "',
        '" . $escape($vb_wc_ratio) . "', '" . $escape($vb_cement_source) . "', '" . $escape($vb_admix_source) . "', '" . $escape($vb_cement_mix) . "', '" . $escape($vb_fly_ash) . "',
        '" . $escape($vb_sand) . "', '" . $escape($vb_three_eight) . "', '" . $escape($vb_three_fourth) . "', '" . $escape($vb_G1) . "', '" . $escape($vb_admix) . "', '" . $escape($vb_water) . "',
        '" . $escape($vb_air) . "', '" . $escape($vb_others) . "', '" . $escape($vb_total) . "', '" . $escape($concrete_price) . "',
        '" . $escape($ssd_cement_mix) . "', '" . $escape($ssd_fly_ash) . "', '" . $escape($ssd_sand) . "', '" . $escape($ssd_three_eight) . "', '" . $escape($ssd_three_fourth) . "',
        '" . $escape($ssd_G1) . "', '" . $escape($ssd_admix) . "', '" . $escape($ssd_water) . "', '" . $escape($ssd_air) . "', '" . $escape($ssd_others) . "',
        '" . $escape($client_name) . "', '" . $escape($client_contact) . "', '" . $escape($experiment_notes) . "', '" . $escape($customization_notes) . "', '" . $escape($is_active) . "',
        '" . $escape($created_by) . "', NOW(), NOW()
    )";

    if ($conn->query($insert)) {
        $success_message = "Customized Mix added successfully!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customized Mix</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <?php showHeader() ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
        }

        .main-content {
            padding-top: 70px;
        }

        .form-container {
            max-width: 1200px;
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

        .form-row input,
        .form-row textarea,
        .form-row select {
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

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
            margin-left: 5px;
        }

        .btn-delete:hover {
            background: #c82333;
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

            .form-row input,
            .form-row textarea,
            .form-row select {
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

    <div class="main-content">
        <div class="form-container">
            <h2>Add Customized Concrete Mix</h2>

            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST">

                <!-- VB Section -->
                <div class="section-title">Volume Batch (VB)</div>

                <div class="form-row">
                    <label>Concrete PSI:</label>
                    <input type="text" name="vb_concrete_psi" class="vb-field" required>
                </div>

                <div class="form-row">
                    <label>Cubic Meter:</label>
                    <input type="text" name="vb_cubic_meter" class="vb-field" required>
                </div>

                <div class="form-row">
                    <label>Curation Days:</label>
                    <input type="text" name="vb_curation_days" class="vb-field" required>
                </div>

                <div class="form-row">
                    <label>Concrete Type:</label>
                    <input type="text" name="vb_concrete_type" class="vb-field" required>
                </div>

                <div class="form-row">
                    <label>Concrete Slump:</label>
                    <input type="text" name="vb_concrete_slump" class="vb-field">
                </div>

                <div class="form-row">
                    <label>W/C Ratio:</label>
                    <input type="text" name="vb_wc_ratio" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Cement Source:</label>
                    <input type="text" name="vb_cement_source" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Admix Source:</label>
                    <input type="text" name="vb_admix_source" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input type="text" name="vb_cement_mix" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input type="text" name="vb_fly_ash" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input type="text" name="vb_sand" class="vb-field">
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input type="text" name="vb_three_eight" class="vb-field">
                </div>

                <div class="form-row">
                    <label>3/4":</label>
                    <input type="text" name="vb_three_fourth" class="vb-field">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input type="text" name="vb_G1" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input type="text" name="vb_admix" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input type="text" name="vb_water" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input type="text" name="vb_air" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input type="text" name="vb_others" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Total:</label>
                    <input type="text" name="vb_total" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Concrete Price:</label>
                    <input type="text" name="concrete_price" class="vb-field">
                </div>

                <!-- SSD Section -->
                <div class="section-title ssd">Saturated Surface Dry (SSD)</div>

                <div class="form-row">
                    <label>Cement Mix:</label>
                    <input type="text" name="ssd_cement_mix" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Fly Ash:</label>
                    <input type="text" name="ssd_fly_ash" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Sand:</label>
                    <input type="text" name="ssd_sand" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>3/8":</label>
                    <input type="text" name="ssd_three_eight" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>3/4":</label>
                    <input type="text" name="ssd_three_fourth" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>G1:</label>
                    <input type="text" name="ssd_G1" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Admix:</label>
                    <input type="text" name="ssd_admix" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Water:</label>
                    <input type="text" name="ssd_water" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Air:</label>
                    <input type="text" name="ssd_air" class="ssd-field">
                </div>

                <div class="form-row">
                    <label>Others:</label>
                    <input type="text" name="ssd_others" class="ssd-field">
                </div>

                <!-- Client/Experiment Fields -->
                <div class="section-title">Client & Experiment Info</div>

                <div class="form-row">
                    <label>Client Name:</label>
                    <input type="text" name="client_name" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Client Contact:</label>
                    <input type="text" name="client_contact" class="vb-field">
                </div>

                <div class="form-row">
                    <label>Experiment Notes:</label>
                    <textarea name="experiment_notes" rows="3"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px;"></textarea>
                </div>

                <div class="form-row">
                    <label>Customization Notes:</label>
                    <textarea name="customization_notes" rows="3"
                        style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px;"></textarea>
                </div>

                <div class="form-row">
                    <label>Is Active:</label>
                    <select name="is_active">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <!-- Hidden System Fields -->
                <input type="hidden" name="created_by" value="<?php echo $_SESSION['username'] ?? 'admin'; ?>">

                <!-- Form Buttons -->
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Save Customized Mix</button>
                    <a href="concrete_design_list.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleDropbar(index) {
            var row = document.getElementById('dropbar-' + index);
            if (!row) return;
            row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
        }
    </script>

</body>

</html>