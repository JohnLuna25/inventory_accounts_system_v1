<?php
// Include DB connection and functions
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Fetch all VB data
$vb_result = $conn->query("SELECT * FROM scd_vb_list ORDER BY vb_concrete_psi ASC");

// Fetch all SSD data into an array
$ssd_result = $conn->query("SELECT * FROM scd_ssd_list ORDER BY ssd_cement_mix ASC");
$ssd_rows = [];
while ($row = $ssd_result->fetch_assoc()) {
    $ssd_rows[] = $row;
}

// Handle search query (search VB only)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== "") {
    $search = htmlspecialchars($search);
    $vb_result = $conn->query("SELECT * FROM scd_vb_list WHERE vb_concrete_psi LIKE '%$search%' OR vb_curation_days LIKE '%$search%' OR vb_concrete_type LIKE '%$search%' ORDER BY vb_concrete_psi ASC");
}

// Check VB query
if (!$vb_result) {
    echo "VB Query Error: " . $conn->error;
}

// Check SSD query
if (!$ssd_result) {
    echo "SSD Query Error: " . $conn->error;
}

// Debug: show row counts
$vb_count = $vb_result ? $vb_result->num_rows : 0;
$ssd_count = count($ssd_rows);
echo "VB Rows: $vb_count, SSD Rows: $ssd_count";


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Design Masterlist</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <?php showHeader() ?>
    <style>
        /* ==== ALL ORIGINAL STYLES KEPT ==== */
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
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
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
            border: 1px solid #888;
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

        .dropbar-row {
            display: none;
            background: #f9f9f9;
        }

        .dropbar-row td {
            background: #fafafa;
            text-align: left;
            padding: 10px;
        }

        .dropbar-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .dropbar-inner td {
            text-align: center;
            font-weight: bold;
            background: #f0f0f0;
        }

        .dropbar-inner td:first-child {
            width: 20%;
            min-width: 120px;
        }

        .dropbar-inner td.ssd-col,
        .dropbar-inner td.vb-col {
            width: 40%;
            min-width: 150px;
        }

        .dropbar-inner td.merged-cell {
            background: #fff;
            text-align: center;
            width: 80%;
        }

        @media (max-width:576px) {
            .main-content {
                padding-top: 200px;
            }

            .page-title-bar {
                margin: 10px;
                padding: 15px;
            }

            .page-title-bar h2 {
                font-size: 18px;
                margin-bottom: 12px;
            }

            .search-container {
                flex-direction: column;
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

            .dropbar-inner td:first-child {
                width: 25%;
                min-width: 100px;
            }

            .dropbar-inner td.ssd-col,
            .dropbar-inner td.vb-col {
                width: 37.5%;
                min-width: 100px;
            }

            .dropbar-inner td.merged-cell {
                width: 75%;
            }
        }

        @media (max-width:400px) {
            .main-content {
                padding-top: 230px;
            }
        }
    </style>
</head>

<body>

    <div class="main-content">

        <div class="page-title-bar">
            <h2 class="text-center mb-3">Concrete Design Masterlist</h2>
            <form method="get" class="search-container">
                <input type="text" name="search" placeholder="Search PSI, Days, Type..." value="<?php echo $search; ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Concrete PSI</th>
                        <th>Curation Days</th>
                        <th>Concrete Type</th>
                        <th>Total</th>
                        <th>Concrete Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $row_index = 0;
                    while ($vb_row = $vb_result->fetch_assoc()) {
                        $ssd_row = $ssd_rows[$row_index] ?? []; // use corresponding SSD row if exists
                        $row_index++;

                        echo "<tr>";
                        echo "<td>" . ($vb_row['vb_concrete_psi'] ?? '') . "</td>";
                        echo "<td>" . ($vb_row['vb_curation_days'] ?? '') . "</td>";
                        echo "<td>" . ($vb_row['vb_concrete_type'] ?? '') . "</td>";
                        echo "<td>" . ($vb_row['vb_total'] ?? '') . "</td>";
                        echo "<td>" . ($vb_row['vb_concrete_price'] ?? '') . "</td>";
                        echo "<td>";
                        echo "<a href='#' class='btn-view' onclick='toggleDropbar($row_index); return false;'>View</a>";
                        echo "<a href='edit_design.php?row=$row_index' class='btn-edit'>Edit</a>";
                        echo "</td>";
                        echo "</tr>";

                        // Dropbar row
                        echo "<tr id='dropbar-$row_index' class='dropbar-row'>";
                        echo "<td colspan='6'>";
                        echo "<table class='dropbar-inner'>";
                        echo "<tr><td></td><td class='ssd-col'>Saturated Surface Dry (SSD)</td><td class='vb-col'>Volume Batch</td></tr>";




                        // Fields to merge (SSD and VB in one cell)
                        $merged_fields = [
                            "Cubic Meter" => "cubic_meter",
                            "Cement Source" => "cement_source",
                            "Concrete Slump" => "concrete_slump",
                            "Admix Source" => "admix_source"
                        ];

                        // Fields that remain separate (SSD left, VB right)
                        $separate_fields = [
                            "Cement Mix" => "cement_mix",
                            "Fly Ash" => "fly_ash",
                            "3/4\"" => "three_fourth",
                            "3/8\"" => "three_eight",
                            "Water" => "water",
                            "Sand" => "sand",
                            "G1" => "G1",
                            "Air" => "air",
                            "W/C Ratio" => "wc_ratio",
                            "Admix" => "admix",
                            "Others" => "others"
                        ];

                        // Render merged fields first
                        foreach ($merged_fields as $label => $col) {
                            $ssd_value = $ssd_row['ssd_' . $col] ?? '';
                            if ($ssd_value == 0)
                                $ssd_value = '';
                            $vb_value = $vb_row['vb_' . $col] ?? '';
                            if ($vb_value == 0)
                                $vb_value = '';

                            // Combine both values
                            $combined = trim($ssd_value . ' ' . $vb_value);

                            echo "<tr><td>$label</td><td colspan='2' class='merged-cell'>$combined</td></tr>";

                        }

                        // Render separate fields
                        foreach ($separate_fields as $label => $col) {
                            $ssd_value = $ssd_row['ssd_' . $col] ?? '';
                            if ($ssd_value == 0)
                                $ssd_value = '';
                            $vb_value = $vb_row['vb_' . $col] ?? '';
                            if ($vb_value == 0)
                                $vb_value = '';
                            echo "<tr><td>$label</td><td>$ssd_value</td><td>$vb_value</td></tr>";
                        }

                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function toggleDropbar(index) {
            var row = document.getElementById('dropbar-' + index);
            if (!row) return;
            row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
        }
    </script>

</body>

</html>