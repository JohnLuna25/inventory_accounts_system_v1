<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Fetch all mixture inventory records
$result = $conn->query("SELECT * FROM sorcom_mixture_inventory ORDER BY id DESC");

// Fetch all design mixes from masterlist
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
        /* Toggle button style - match existing buttons */

        .btn-view {
            padding: 3px 6px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 10px;
            margin-right: 3px;
        }

        .btn-view:hover {
            background: #1976D2;
        }

        /* Suggestion bar styles */
        .suggestion-bar {
            display: none;
            background: #5f5e5e;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 5px 0 15px 0;
        }

        .suggestion-bar.show {
            display: block;
        }

        .suggestion-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .suggestion-table th,
        .suggestion-table td {
            border: 1px solid #000000;
            padding: 6px;
            text-align: center;
            font-size: 11px;
            background-color: #c0c0c0;
        }

        .suggestion-table th {
            background: #332e2e;
        }

        .btn-add-produce {
            padding: 3px 8px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
        }

        .btn-add-produce:hover {
            background: #388E3C;
        }

        .no-suggestion {
            color: #666;
            font-style: italic;
            padding: 10px;
        }
    </style>
</head>

<body>

    <h2>Mixture Inventory</h2>

    <div style="text-align: right; width: 97%; margin: 10px auto; overflow: hidden;">
        <a href="addMixture_inventory.php" class="mixture-controls">+ Add Mixture</a>
        <button class="back-btn" onclick="window.location.href='../inventory_page/inventory_management.php'">← Back to
            Ready-Mix</button>
    </div>
    <table>
        <tr>
            <th>ID</th>
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

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['date_added']; ?></td>
                    <td><?php echo $row['cement']; ?></td>
                    <td><?php echo $row['fly_ash']; ?></td>
                    <td><?php echo $row['sand']; ?></td>
                    <td><?php echo $row['3_4']; ?></td>
                    <td><?php echo $row['3_8']; ?></td>
                    <td><?php echo $row['g1']; ?></td>
                    <td><?php echo $row['admix']; ?></td>
                    <td><?php echo $row['water']; ?></td>
                    <td><?php echo $row['air']; ?></td>
                    <td><?php echo $row['others']; ?></td>
                    <td><?php echo $row['total'] . ' kgs/m³'; ?></td>

                    <td>
                        <div class="action-buttons">
                            <div class="button-row">
                                <button type="button" class="btn btn-info btn-sm"
                                    onclick="toggleSuggestion(<?php echo $row['id']; ?>)">View</button>
                                <button type="button" class="btn btn-warning btn-sm"
                                    onclick="window.location.href='editMixture_Inventory.php?id=<?php echo $row['id']; ?>'">Edit</button>
                                <button type="button" class="btn btn-danger btn-sm"
                                    onclick="if(confirm('Are you sure?')) { window.location.href='deleteMixture_Inventory.php?id=<?php echo $row['id']; ?>'; }">Delete</button>
                            </div>
                        </div>
                    </td>
                </tr>

                <!-- SUGGESTION BAR ROW -->
                <tr>
                    <td colspan="14" style="padding: 0; background: #f5f5f5;">
                        <div id="suggestion-<?php echo $row['id']; ?>" class="suggestion-bar">
                            <strong>Available Designs (Based on Current Stock):</strong>

                            <?php
                            // Get current stock from this row
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

                            // Filter designs that can be produced
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

                            // Display available designs
                            if (count($available_designs) > 0):
                                ?>
                                <table class="suggestion-table">
                                    <thead>
                                        <tr>
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
                                                <td><?php echo $d['concrete_psi']; ?></td>
                                                <td><?php echo $d['curation_days']; ?> days</td>
                                                <td><?php echo $d['concrete_type']; ?></td>
                                                <td><?php echo $d['total']; ?></td>
                                                <td>
                                                    <form method="POST" action="produce_from_mixture.php" style="margin: 0;">
                                                        <input type="hidden" name="mixture_id" value="<?php echo $row['id']; ?>">
                                                        <input type="hidden" name="design_psi"
                                                            value="<?php echo $d['concrete_psi']; ?>">
                                                        <input type="hidden" name="design_days"
                                                            value="<?php echo $d['curation_days']; ?>">
                                                        <input type="hidden" name="design_type"
                                                            value="<?php echo $d['concrete_type']; ?>">
                                                        <input type="hidden" name="design_batch" value="<?php echo $d['total']; ?>">
                                                        <button type="submit" class="btn-add-produce">ADD</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="no-suggestion">No designs can be produced with current stock.</p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="14">No data found.</td>
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