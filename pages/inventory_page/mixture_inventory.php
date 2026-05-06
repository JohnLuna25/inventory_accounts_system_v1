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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Mixture Inventory</title>
    <link rel="stylesheet" href="mixture_inventory.css">
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <?php include '../../assets/resources.php'; ?>
    <?php showHeader(); ?>
</head>

<body>

    <div class="main-content">

        <!-- HEADER -->
        <div class="page-title-bar">
            <h2 style="font-family: 'Poppins', sans-serif; text-align:center; margin:0; font-weight:300;">Mixture
                Inventory</h2>

            <div class="button-container">
                <a href="mixture_form.php" class="btn-add">+ Add Mixture</a>
                <a href="../inventory_page/inventory_management.php" class="btn-back">← Back</a>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-container">

            <table>
                <thead>
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
                </thead>

                <tbody>
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
                                    onclick="window.location.href='mixture_form.php?id=<?= $row['id'] ?>'">Edit</button>
                                <button class="btn btn-del"
                                    onclick="if(confirm('Delete this record?')) window.location.href='mixture_form.php?delete=<?= $row['id'] ?>'">Delete</button>
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
                                                        <td><?= $d['vb_total'] ?></td>
                                                        <td>
                                                            <a href="mi_form.php?psi=<?= urlencode($d['vb_concrete_psi']) ?>
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
                </tbody>

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