<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM sorcom_mixture_inventory ORDER BY id DESC");

$designs_result = $conn->query("SELECT * FROM scd_vb_list");
$all_designs = [];
while ($d = $designs_result->fetch_assoc()) {
    $all_designs[] = $d;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SORCOM Mixture Inventory</title>
    <?php showHeader(); ?>

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

        .page-title-bar {
            background: white;
            margin: 15px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-container {
            display: flex;
            justify-content: flex-end;
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

        .table-container {
            background: rgb(197, 197, 197);
            margin: 15px;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(161, 11, 11, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
            background: #e2e2e2;
        }

        th {
            background: #0b63c4;
            color: white;
            padding: 12px 8px;
            font-size: 14px;
            text-align: center;
        }

        td {
            border: 1px solid #000;
            padding: 10px 8px;
            text-align: center;
            font-size: 13px;
        }

        /* VIEW PANEL */
        .view-panel {
            display: none;
            background: #f3f3f3;
            padding: 12px;
        }

        .view-box {
            background: white;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #b4b4b4;
            margin-top: 8px;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
        }

        .btn-view {
            background: #28a745;
            color: white;
        }

        .btn-edit {
            background: #fd7e14;
            color: white;
        }

        .btn-del {
            background: #dc3545;
            color: white;
        }
    </style>
</head>

<body>

    <div class="main-content">

        <!-- HEADER -->
        <div class="page-title-bar">
            <h2 style="text-align:center; margin:0; font-weight:300;">Mixture Inventory</h2>

            <div class="search-container">
                <a href="addMixture_inventory.php">+ Add Mixture</a>
                <a href="../inventory_page/inventory_management.php">← Back</a>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">

            <table>
                <tr>
                    <th>Date</th>
                    <th>Cement</th>
                    <th>Fly Ash</th>
                    <th>Sand</th>
                    <th>3/4</th>
                    <th>3/8</th>
                    <th>G1</th>
                    <th>Admix</th>
                    <th>Water</th>
                    <th>Air</th>
                    <th>Others</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= $row['date_added'] ?></td>
                        <td><?= $row['cement'] ?></td>
                        <td><?= $row['fly_ash'] ?></td>
                        <td><?= $row['sand'] ?></td>
                        <td><?= $row['3_4'] ?></td>
                        <td><?= $row['3_8'] ?></td>
                        <td><?= $row['g1'] ?></td>
                        <td><?= $row['admix'] ?></td>
                        <td><?= $row['water'] ?></td>
                        <td><?= $row['air'] ?></td>
                        <td><?= $row['others'] ?></td>
                        <td><?= $row['total'] ?> kgs/m³</td>

                        <td>
                            <button class="btn btn-view" onclick="toggleView(<?= $row['id'] ?>)">View</button>
                            <button class="btn btn-edit"
                                onclick="window.location.href='editMixture_Inventory.php?id=<?= $row['id'] ?>'">Edit</button>
                            <button class="btn btn-del"
                                onclick="if(confirm('Delete?')) window.location.href='deleteMixture_Inventory.php?id=<?= $row['id'] ?>'">Delete</button>
                        </td>
                    </tr>

                    <!-- VIEW ROW -->
                    <tr id="view-<?= $row['id'] ?>" class="view-panel">
                        <td colspan="13">

                            <div class="view-box">
                                <strong>Available Designs (Based on Current Stock):</strong><br><br>

                                <?php
                                $stock = [
                                    'cement' => floatval($row['cement']),
                                    'fly_ash' => floatval($row['fly_ash']),
                                    'sand' => floatval($row['sand']),
                                    '3_4' => floatval($row['3_4']),
                                    '3_8' => floatval($row['3_8']),
                                    'g1' => floatval($row['g1']),
                                    'admix' => floatval($row['admix']),
                                    'water' => floatval($row['water']),
                                    'air' => floatval($row['air'])
                                ];

                                $available_designs = [];

                                foreach ($all_designs as $d) {

                                    $can_produce =
                                        $stock['cement'] >= floatval($d['vb_cement_mix']) &&
                                        $stock['fly_ash'] >= floatval($d['vb_fly_ash']) &&
                                        $stock['sand'] >= floatval($d['vb_sand']) &&
                                        $stock['3_4'] >= floatval($d['vb_three_fourth']) &&
                                        $stock['3_8'] >= floatval($d['vb_three_eight']) &&
                                        $stock['g1'] >= floatval($d['vb_G1']) &&
                                        $stock['admix'] >= floatval($d['vb_admix']) &&
                                        $stock['water'] >= floatval($d['vb_water']) &&
                                        $stock['air'] >= floatval($d['vb_air']);

                                    if ($can_produce) {
                                        $available_designs[] = $d;
                                    }
                                }

                                if (count($available_designs) > 0):
                                    ?>

                                    <table class="suggestion-table"
                                        style="width:100%; border-collapse:collapse; margin-top:10px;">
                                        <thead>
                                            <tr style="background:#0b63c4; color:white;">
                                                <th>PSI</th>
                                                <th>Curation Days</th>
                                                <th>Type</th>
                                                <th>Batch (kgs/m³)</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($available_designs as $d): ?>
                                                <tr>
                                                    <td><?= $d['vb_concrete_psi'] ?></td>
                                                    <td><?= $d['vb_curation_days'] ?></td>
                                                    <td><?= $d['vb_concrete_type'] ?></td>
                                                    <td><?= $d['vb_total'] ?> kgs/m³</td>

                                                    <td>
                                                        <a href="ip_addForm.php?psi=<?= urlencode($d['vb_concrete_psi']) ?>
                    &days=<?= urlencode($d['vb_curation_days']) ?>
                    &type=<?= urlencode($d['vb_concrete_type']) ?>
                    &batch=<?= urlencode($d['vb_total']) ?>
                    &mixture_id=<?= $row['id'] ?>"
                                                            style="padding:3px 8px; background:#4CAF50; color:white; text-decoration:none; border-radius:4px; font-size:10px;">
                                                            ADD
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                <?php else: ?>

                                    <p style="text-align:center; font-style:italic;">
                                        No producible designs for current stock
                                    </p>

                                <?php endif; ?>
                            </div>

                        </td>
                    </tr>

                <?php endwhile; ?>

            </table>

        </div>
    </div>

    <script>
        function toggleView(id) {
            let row = document.getElementById('view-' + id);
            row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>