<?php
// Include DB connection and functions
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

if (!isset($conn)) {
    die("Database connection not established. Check db_connect.php.");
}

$table_name = 'sorcom_concrete_design_masterlist';

// Handle search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = "";
$params = array();
$types = "";

if ($search !== "") {
    $search_param = "%$search%";
    $search_sql = "WHERE concrete_psi LIKE ? OR curation_days LIKE ? OR concrete_type LIKE ?";
    $params = array_fill(0, 3, $search_param);
    $types = str_repeat("s", 3);
}

$sql_summary = "SELECT * FROM $table_name $search_sql ORDER BY id ASC";

$stmt = $conn->prepare($sql_summary);
if ($search !== "") {
    $stmt->bind_param($types, $params[0], $params[1], $params[2]);
}
$stmt->execute();
$result = $stmt->get_result();
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
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
        }

        /* Push content down below fixed header */
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

        .dropbar-inner td:first-child {
            font-weight: bold;
            width: 45%;
            background: #f0f0f0;
        }

        .dropbar-inner td:last-child {
            text-align: right;
        }

        /* Responsive */
        @media (max-width: 576px) {
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

            .search-container input {
                width: 100%;
            }

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
                width: 40%;
                font-size: 12px;
            }
        }

        @media (max-width: 400px) {
            .main-content {
                padding-top: 230px;
            }
        }
    </style>
</head>

<body>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Page Title and Search -->
        <div class="page-title-bar">
            <h2 class="text-center mb-3">Concrete Design Masterlist</h2>
            <form method="get" class="search-container">
                <input type="text" name="search" placeholder="Search PSI, Days, Type..."
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <!-- Table -->
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
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = $row['id'];
                            echo "<tr>";
                            echo "<td>" . (isset($row['concrete_psi']) ? $row['concrete_psi'] : '') . "</td>";
                            echo "<td>" . (isset($row['curation_days']) ? $row['curation_days'] : '') . "</td>";
                            echo "<td>" . (isset($row['concrete_type']) ? $row['concrete_type'] : '') . "</td>";
                            echo "<td>" . (isset($row['total']) ? $row['total'] : '') . "</td>";
                            echo "<td>" . (isset($row['concrete_price']) ? $row['concrete_price'] : '') . "</td>";
                            echo "<td>";
                            echo "<a href='#' class='btn-view' onclick='toggleDropbar(" . $id . "); return false;'>View</a>";
                            echo "<a href='edit_design.php?id=" . $id . "' class='btn-edit'>Edit</a>";
                            echo "</td>";
                            echo "</tr>";

                            // Dropbar row
                            echo "<tr id='dropbar-" . $id . "' class='dropbar-row'>";
                            echo "<td colspan='6'>";
                            echo "<table class='dropbar-inner'>";
                            echo "<tr><td>Cubic Meter</td><td>" . (isset($row['cubic_meter']) ? $row['cubic_meter'] : '') . "</td></tr>";
                            echo "<tr><td>Cement Source</td><td>" . (isset($row['cement_source']) ? $row['cement_source'] : '') . "</td></tr>";
                            echo "<tr><td>Fly Ash</td><td>" . (isset($row['fly_ash']) ? $row['fly_ash'] : '') . "</td></tr>";
                            echo "<tr><td>3/4\"</td><td>" . (isset($row['three_fourth']) ? $row['three_fourth'] : '') . "</td></tr>";
                            echo "<tr><td>3/8\"</td><td>" . (isset($row['three_eight']) ? $row['three_eight'] : '') . "</td></tr>";
                            echo "<tr><td>Water</td><td>" . (isset($row['water']) ? $row['water'] : '') . "</td></tr>";
                            echo "<tr><td>Concrete Slump</td><td>" . (isset($row['concrete_slump']) ? $row['concrete_slump'] : '') . "</td></tr>";
                            echo "<tr><td>Admix Source</td><td>" . (isset($row['admix_source']) ? $row['admix_source'] : '') . "</td></tr>";
                            echo "<tr><td>Sand</td><td>" . (isset($row['sand']) ? $row['sand'] : '') . "</td></tr>";
                            echo "<tr><td>G1</td><td>" . (isset($row['g1']) ? $row['g1'] : '') . "</td></tr>";
                            echo "<tr><td>Air</td><td>" . (isset($row['air']) ? $row['air'] : '') . "</td></tr>";
                            echo "<tr><td>W/C Ratio</td><td>" . (isset($row['wc_ratio']) ? $row['wc_ratio'] : '') . "</td></tr>";
                            echo "<tr><td>Cement Mix</td><td>" . (isset($row['cement_mix']) ? $row['cement_mix'] : '') . "</td></tr>";
                            echo "<tr><td>Admix</td><td>" . (isset($row['admix']) ? $row['admix'] : '') . "</td></tr>";
                            echo "<tr><td>Others</td><td>" . (isset($row['others']) ? $row['others'] : '') . "</td></tr>";
                            echo "</table>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function toggleDropbar(id) {
            var row = document.getElementById('dropbar-' + id);
            if (!row) return;
            if (row.style.display === 'table-row') {
                row.style.display = 'none';
            } else {
                row.style.display = 'table-row';
            }
        }
    </script>

</body>

</html>