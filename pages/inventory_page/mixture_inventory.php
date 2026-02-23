<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM sorcom_mixture_inventory ORDER BY id DESC");

$designs_result = $conn->query("SELECT * FROM sorcom_concrete_design_masterlist");
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../inventory_page/mixture_inventory.css">
    <?php showHeader(); ?>

    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .suggestion-bar {
            display: none;
            background: #5f5e5e;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 5px 0 15px 0;
            font-family: Arial, sans-serif;

        }

        .suggestion-bar.show {
            display: block;
        }

        .suggestion-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-family: Arial, sans-serif;
            color: #000000;
        }

        .suggestion-table th,
        .suggestion-table td {
            border: 1px solid #000000;
            padding: 6px;
            text-align: center;
            font-size: 11px;
            font-family: Arial, sans-serif;
            background-color: #c0c0c0;
        }

        .suggestion-table th {
            background: #332e2e;
            font-family: Arial, sans-serif;
        }

        .btn-add-produce {
            padding: 3px 8px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            font-family: Arial, sans-serif;
        }

        .btn-add-produce:hover {
            background: #388E3C;
        }

        .no-suggestion {
            color: #000000;
            font-style: italic;
            padding: 10px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body style="font-family: Arial, sans-serif;">

    <h2 style="text-align: center; margin-top: 57px; color: black; font-family: Arial, sans-serif;">Mixture Inventory
    </h2>

    <div style="text-align: right; width: 97%; margin: 10px auto; overflow: hidden;">
        <a href="addMixture_inventory.php"
            style="padding: 10px 15px; background-color: #0b63c4; color: white; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; float: right; margin-left: 7px; font-family: Arial, sans-serif;">+
            Add Mixture</a>
        <button onclick="window.location.href='../inventory_page/inventory_management.php'"
            style="padding: 10px 15px; background-color: #0b63c4; color: white; font-weight: bold; border-radius: 4px; display: inline-block; float: right; margin-right: 7px; cursor: pointer; border: none; font-family: Arial, sans-serif; font-size: 13px;">←
            Back to Ready-Mix</button>
    </div>

    <table
        style="border-collapse: collapse; width: 99%; margin: 20px auto; background: #fff; border: 1px solid #ccc; font-family: Arial, sans-serif;">
        <tr style="background: #0b63c4; color: #fff; font-family: Arial, sans-serif;">

            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Date</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Cement</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Fly Ash</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Sand</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">3/4</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">3/8</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">G1</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Admix</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Water</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Air</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Others</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Total</th>
            <th style="border: 1px solid #ccc; padding: 8px; font-family: Arial, sans-serif;">Actions</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr style="font-family: Arial, sans-serif;">

                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['date_added']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['cement']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['fly_ash']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['sand']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['3_4']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['3_8']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['g1']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['admix']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['water']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['air']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['others']; ?>
                    </td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <?php echo $row['total'] . ' kgs/m³'; ?>
                    </td>

                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">
                        <div style="display: flex; justify-content: center; gap: 5px;">
                            <button type="button"
                                style="background-color: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-family: Arial, sans-serif;"
                                onclick="toggleSuggestion(<?php echo $row['id']; ?>)">View</button>
                            <button type="button"
                                style="background-color: #fd7e14; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-family: Arial, sans-serif;"
                                onclick="window.location.href='editMixture_Inventory.php?id=<?php echo $row['id']; ?>'">Edit</button>
                            <button type="button"
                                style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-family: Arial, sans-serif;"
                                onclick="if(confirm('Are you sure?')) { window.location.href='deleteMixture_Inventory.php?id=<?php echo $row['id']; ?>'; }">Delete</button>
                        </div>
                    </td>
                </tr>

                <!-- SUGGESTION BAR ROW -->
                <tr>
                    <td colspan="14" style="padding: 0; background: #f5f5f5;">
                        <div id="suggestion-<?php echo $row['id']; ?>" class="suggestion-bar">
                            <strong
                                style="display: block; margin-bottom: 10px; color: #ffffff; text-align: center; font-family: Arial, sans-serif;">Available
                                Designs (Based on Current Stock):</strong>

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
                            foreach ($all_designs as $design) {
                                $can_produce = (
                                    $stock['cement'] >= floatval($design['cement_mix']) &&
                                    $stock['fly_ash'] >= floatval($design['fly_ash']) &&
                                    $stock['sand'] >= floatval($design['sand']) &&
                                    $stock['3_4'] >= floatval($design['three_fourth']) &&
                                    $stock['3_8'] >= floatval($design['three_eight']) &&
                                    $stock['g1'] >= floatval($design['G1']) &&
                                    $stock['admix'] >= floatval($design['admix']) &&
                                    $stock['water'] >= floatval($design['water']) &&
                                    $stock['air'] >= floatval($design['air'])
                                );

                                if ($can_produce) {
                                    $available_designs[] = $design;
                                }
                            }

                            if (count($available_designs) > 0):
                                ?>
                                <table class="suggestion-table"
                                    style="width: 100%; border-collapse: collapse; background: #fff; font-family: Arial, sans-serif;">
                                    <thead>
                                        <tr style="background: #0b63c4; color: #fff; font-family: Arial, sans-serif;">
                                            <th
                                                style="border: 1px solid #000; padding: 6px; font-size: 11px; font-family: Arial, sans-serif;">
                                                PSI</th>
                                            <th
                                                style="border: 1px solid #000; padding: 6px; font-size: 11px; font-family: Arial, sans-serif;">
                                                Curation Days</th>
                                            <th
                                                style="border: 1px solid #000; padding: 6px; font-size: 11px; font-family: Arial, sans-serif;">
                                                Type</th>
                                            <th
                                                style="border: 1px solid #000; padding: 6px; font-size: 11px; font-family: Arial, sans-serif;">
                                                Batch (kgs/m³)</th>
                                            <th
                                                style="border: 1px solid #000; padding: 6px; font-size: 11px; font-family: Arial, sans-serif;">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($available_designs as $d): ?>
                                            <tr>
                                                <td
                                                    style="border: 1px solid #000; padding: 6px; font-size: 11px; background-color: #c0c0c0; text-align: center; font-family: Arial, sans-serif;">
                                                    <?php echo $d['concrete_psi']; ?>
                                                </td>
                                                <td
                                                    style="border: 1px solid #000; padding: 6px; font-size: 11px; background-color: #c0c0c0; text-align: center; font-family: Arial, sans-serif;">
                                                    <?php echo $d['curation_days']; ?> days
                                                </td>
                                                <td
                                                    style="border: 1px solid #000; padding: 6px; font-size: 11px; background-color: #c0c0c0; text-align: center; font-family: Arial, sans-serif;">
                                                    <?php echo $d['concrete_type']; ?>
                                                </td>
                                                <td
                                                    style="border: 1px solid #000; padding: 6px; font-size: 11px; background-color: #c0c0c0; text-align: center; font-family: Arial, sans-serif;">
                                                    <?php echo $d['total']; ?>
                                                </td>
                                                <td
                                                    style="border: 1px solid #000; padding: 6px; font-size: 11px; background-color: #c0c0c0; text-align: center; font-family: Arial, sans-serif;">
                                                    <form method="POST" action="produce_from_mixture.php" style="margin: 0;">
                                                        <input type="hidden" name="mixture_id" value="<?php echo $row['id']; ?>">
                                                        <input type="hidden" name="design_psi"
                                                            value="<?php echo $d['concrete_psi']; ?>">
                                                        <input type="hidden" name="design_days"
                                                            value="<?php echo $d['curation_days']; ?>">
                                                        <input type="hidden" name="design_type"
                                                            value="<?php echo $d['concrete_type']; ?>">
                                                        <input type="hidden" name="design_batch" value="<?php echo $d['total']; ?>">
                                                        <button type="submit" class="btn-add-produce"
                                                            style="padding: 3px 8px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 10px; font-family: Arial, sans-serif;">ADD</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="no-suggestion"
                                    style="color: #000000; font-style: italic; padding: 10px; font-family: Arial, sans-serif; text-align: center;">
                                    No
                                    designs can be produced with current stock!</p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="14"
                    style="border: 1px solid #ccc; padding: 8px; text-align: center; font-family: Arial, sans-serif;">No
                    data found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <script>
        function toggleSuggestion(id) {
            var element = document.getElementById('suggestion-' + id);
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>

</body>

</html>

<?php $conn->close(); ?>