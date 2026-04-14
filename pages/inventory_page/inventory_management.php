<?php
ob_start();
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
include_once __DIR__ . '/ip_postHandler.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $edit_id = intval($_POST['update_id']);
    $date_added = trim($_POST['date_added'] ?? date('Y-m-d'));
    $design_mix = trim($_POST['design_mix'] ?? '');
    $curation_days = intval($_POST['curation_days'] ?? 0);
    $concrete_type = trim($_POST['concrete_type'] ?? '');
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($date_added) || empty($design_mix) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE scd_vb_list 
            SET date_added = ?, design_mix_id = ?, curation_days = ?, concrete_type = ?, stocks_in = ?, stocks_out = ?, remarks = ? 
            WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssiiisi", $date_added, $design_mix, $curation_days, $concrete_type, $stocks_in, $stocks_out, $remarks, $edit_id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: inventory_management.php");
                exit;
            } else {
                $error_message = "Error updating inventory: " . $stmt->error;
            }
        } else {
            $error_message = "Error preparing update statement: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="inventory_management.css">
    <?php showHeader() ?>

    <style>
        /* ==== SAME STYLING AS CONCRETE DESIGN MASTERLIST ==== */
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

        .page-title-bar {
            background: white;
            margin: 15px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-container a {
            padding: 10px 20px;
            background: #0b63c4;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .search-container input {
            flex: 1;
            min-width: 150px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-container button {
            padding: 10px 20px;
            background: #0b63c4;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .search-container .btn-success {
            background: #4CAF50;
        }

        .search-container .btn-secondary {
            background: #6c757d;
        }

        .table-container {
            background: white;
            margin: 15px;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th {
            background: #0b63c4;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-size: 14px;
            white-space: nowrap;
        }

        td {
            border: 1px solid #000000;
            padding: 10px 8px;
            text-align: center;
            font-size: 13px;
        }

        .btn-view {
            background: #4CAF50;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
            margin-right: 5px;
        }

        .btn-edit {
            background: #2196F3;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }

        .error {
            color: red;
            text-align: center;
            font-weight: bold;
            margin: 10px 15px;
        }

        @media (max-width: 576px) {
            .main-content {
                padding-top: 70px;
            }

            .page-title-bar {
                background: white;
                height: 90px;
                margin: 15px;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .page-title-bar h2 {
                font-size: 24px;

                text-align: center;
                margin: 0;
                color: black;
                margin-bottom: 20px;
            }

            .search-container {
                flex-direction: row;
                position: absolute;
                margin-left: -70px;
                margin: 0 auto;
            }

            .search-container input,
            .search-container button {
                width: 100%;
            }

            .table-container {
                margin: 10px;
                padding: 10px;
            }

            th,
            td {
                padding: 8px 5px;
                font-size: 12px;
            }

            .btn-view,
            .btn-edit {
                padding: 5px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="page-title-bar">
            <h2 class="text-center mb-3" style="font-weight: 300; font-size: 32px; margin-top: auto;">Ready-Mix
                Inventory</h2>
            <div class="search-container">
                <a href="ip_addForm.php" class="btn">+ Add Inventory</a>
                <a href="mixture_inventory.php" class="btn">Open Mixture Inventory</a>
            </div>
        </div>

        <?php if ($error_message): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php include __DIR__ . '/ip_tableDisplay.php'; ?>
    </div>
</body>

</html>