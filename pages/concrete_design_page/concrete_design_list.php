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
    $search_sql = "WHERE id LIKE ? OR concrete_psi LIKE ? OR curation_days LIKE ? OR concrete_type LIKE ?";
    $params = array_fill(0, 4, $search_param);
    $types = str_repeat("s", 4);
}

$sql_summary = "SELECT * FROM $table_name $search_sql ORDER BY id ASC";

$stmt = $conn->prepare($sql_summary);
if ($search !== "") {
    $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3]);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="ip_table.css">
    <?php showHeader() ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            margin: 0;
            padding: 0;
        }

        .page-title-bar {
            background: white;
            margin-top: 50px;
            padding: 20px;
            margin: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title-bar h2 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 24px;
        }

        .search-form {
            margin: 0;
        }

        .search-form input[type="text"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-form button {
            padding: 8px 15px;
            background: #0b63c4;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .table-container {
            background: white;
            margin: 20px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #0b63c4;
            color: white;
            padding: 12px;
            text-align: center;
        }

        td {
            border: 1px solid #888;
            padding: 10px;
            text-align: center;
        }

        .btn-view {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
        }

        .btn-edit {
            background: #2196F3;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        .dropbar-row {
            display: none;
            background: #f9f9f9;
        }

        .dropbar-row td {
            background: #fafafa;
            text-align: left;
        }

        .dropbar-inner {
            width: 100%;
        }

        .dropbar-inner td:first-child {
            font-weight: bold;
            width: 200px;
            background: #f0f0f0;
        }
    </style>
</head>

<body>

    <!-- Page Title and Search -->
    <div class="page-title-bar" style="position: relative; height: 80px; margin-top: 80px;">
        <h2 style="color: black; text-align: center; margin-top: 25px;">Concrete Design Masterlist (Summary)</h2>
        <form method="get" class="search-form" style="position: absolute; right: 20px; top: 45px;">
            <input type="text" name="search" placeholder="Search ID, PSI, Days, Type..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Concrete PSI</th>
                    <th>Curation Days</th>
                    <th>Concrete Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = $row['id'];
                        echo "<tr>";
                        echo "<td>" . (isset($row['id']) ? $row['id'] : '') . "</td>";
                        echo "<td>" . (isset($row['concrete_psi']) ? $row['concrete_psi'] : '') . "</td>";
                        echo "<td>" . (isset($row['curation_days']) ? $row['curation_days'] : '') . "</td>";
                        echo "<td>" . (isset($row['concrete_type']) ? $row['concrete_type'] : '') . "</td>";
                        echo "<td>";
                        echo "<a href='#' class='btn-view' onclick='toggleDropbar(" . $id . "); return false;'>View</a>";
                        echo "<a href='edit_design.php?id=" . $id . "' class='btn-edit'>Edit</a>";
                        echo "</td>";
                        echo "</tr>";

                        // Dropbar row
                        echo "<tr id='dropbar-" . $id . "' class='dropbar-row'>";
                        echo "<td colspan='5'>";
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
                        echo "<tr><td>Total</td><td>" . (isset($row['total']) ? $row['total'] : '') . "</td></tr>";
                        echo "<tr><td>Concrete Price</td><td>" . (isset($row['concrete_price']) ? $row['concrete_price'] : '') . "</td></tr>";
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
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