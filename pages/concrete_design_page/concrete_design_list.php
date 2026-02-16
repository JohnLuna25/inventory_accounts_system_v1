<?php
// Include required files
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check database connection
if (!isset($conn)) {
    die("Database connection not established. Check db_connect.php.");
}

// Table name based on your CSV import
$table_name = 'sorcom_concrete_design_masterlist';
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'cd_head.php'; ?>

<head>
    <script>
        // Two-step delete confirmation
        function confirmDeletion(psi) {
            if (!confirm(`You just selected ${psi} for deletion. Proceed?`)) {
                return false; // First cancel
            }
            return confirm(`Are you sure you want to delete ${psi} data? This will not be recovered again.`);
        }
    </script>
</head>

<body>
    <h2 style="font-size: 24px; text-align: center; margin-top: 65px;">
        CSV Data Display: sorcom_concrete_design_masterlist
    </h2>

    <table>
        <thead>
            <tr>
                <?php
                $sql_headers = "SHOW COLUMNS FROM $table_name";
                $headers_result = $conn->query($sql_headers);
                $columns = [];

                // Custom header mapping
                $header_map = [
                    'three_fourth' => '3/4"',
                    'three_eight' => '3/8"',
                    'cement_mix' => 'Cement Mix (m³)',
                    'sand' => 'Sand (m³)',
                    'air' => 'Air (m³)',
                    'total' => 'Total (m³)'
                ];

                if ($headers_result->num_rows > 0) {
                    while ($header = $headers_result->fetch_assoc()) {
                        $columns[] = $header['Field'];
                        $pretty_header = $header_map[$header['Field']] ?? ucwords(str_replace('_', ' ', $header['Field']));
                        echo "<th>" . htmlspecialchars($pretty_header) . "</th>";
                    }
                }
                ?>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM $table_name";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($columns as $col) {
                        echo "<td>" . htmlspecialchars($row[$col]) . "</td>";
                    }

                    // Encode PSI for safe JS usage
                    $psi_js = json_encode($row['concrete_psi']);

                    echo "<td class='action-buttons'>
                            <a href='edit_design.php?id=" . $row['id'] . "' class='button edit'>✏ Edit</a>
                            <a href='delete_design.php?id=" . $row['id'] . "' class='button delete'
                               onclick='return confirmDeletion($psi_js);'>🗑 Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='" . (count($columns) + 1) . "'>No data found in CSV table.</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>

    <style>
        .action-buttons {
            text-align: center;
            white-space: nowrap;
        }

        a.button {
            padding: 3px 8px;
            font-size: 12px;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin: 1px;
        }

        a.edit {
            background: #2196F3;
        }

        a.delete {
            background: #f44336;
        }

        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            text-align: center;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px 8px;
        }

        th {
            background: #0b63c4;
            color: #fff;
        }
    </style>
</body>

</html>