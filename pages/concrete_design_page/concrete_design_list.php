<?php
// Include required files
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check database connection
if (!isset($conn)) {
    die("Database connection not established. Check db_connect.php.");
}

// Table name
$table_name = 'sorcom_concrete_design_masterlist';
?>

<!DOCTYPE html>
<html lang="en">

<?php include 'cd_head.php'; ?>

<head>
    <title>Sorcom Concrete Design Masterlist</title>
    <script>
        // Two-step delete confirmation
        function confirmDeletion(psi) {
            if (!confirm(`You just selected ${psi} for deletion. Proceed?`)) return false;
            return confirm(`Are you sure you want to delete ${psi} data? This will not be recovered again.`);
        }
    </script>
    <style>
        /* Table styling */
        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            text-align: center;
            border: 1px solid #888;
            /* gray border */
        }

        th,
        td {
            border: 1px solid #888;
            /* gray borders */
            padding: 5px 8px;
        }

        th {
            background: #0b63c4;
            color: #fff;
        }

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

        /* Row colors */
        /* Rows 1–6: #E4C039 */
        tbody tr:nth-child(1),
        tbody tr:nth-child(2),
        tbody tr:nth-child(3),
        tbody tr:nth-child(4),
        tbody tr:nth-child(5),
        tbody tr:nth-child(6) {
            background-color: #E4C039;
        }

        /* Rows 7–9: #87B442 */
        tbody tr:nth-child(7),
        tbody tr:nth-child(8),
        tbody tr:nth-child(9) {
            background-color: #87B442;
        }

        /* Rows 10–12: #FF5050 */
        tbody tr:nth-child(10),
        tbody tr:nth-child(11),
        tbody tr:nth-child(12) {
            background-color: #FF5050;
        }

        /* Rows 13–16: #E4C039 */
        tbody tr:nth-child(13),
        tbody tr:nth-child(14),
        tbody tr:nth-child(15),
        tbody tr:nth-child(16) {
            background-color: #E4C039;
        }

        /* Rows 17–19: #87B442 */
        tbody tr:nth-child(17),
        tbody tr:nth-child(18),
        tbody tr:nth-child(19) {
            background-color: #87B442;
        }

        /* Rows 20–22: #E4C039 */
        tbody tr:nth-child(20),
        tbody tr:nth-child(21),
        tbody tr:nth-child(22) {
            background-color: #E4C039;
        }

        /* Rows 23–25: #87B442 */
        tbody tr:nth-child(23),
        tbody tr:nth-child(24),
        tbody tr:nth-child(25) {
            background-color: #87B442;
        }

        /* Rows 26–28: #FF5050 */
        tbody tr:nth-child(26),
        tbody tr:nth-child(27),
        tbody tr:nth-child(28) {
            background-color: #FF5050;
        }

        /* Rows 29–31: #E4C039 */
        tbody tr:nth-child(29),
        tbody tr:nth-child(30),
        tbody tr:nth-child(31) {
            background-color: #E4C039;
        }

        /* Rows 32–34: #87B442 */
        tbody tr:nth-child(32),
        tbody tr:nth-child(33),
        tbody tr:nth-child(34) {
            background-color: #87B442;
        }

        /* Rows 35–37: #E4C039 */
        tbody tr:nth-child(35),
        tbody tr:nth-child(36),
        tbody tr:nth-child(37) {
            background-color: #E4C039;
        }

        /* Rows 38–40: #87B442 */
        tbody tr:nth-child(38),
        tbody tr:nth-child(39),
        tbody tr:nth-child(40) {
            background-color: #87B442;
        }

        /* CSV Button styling */
        #saveCsvBtn {
            margin: 10px auto;
            display: block;
            padding: 5px 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <h2 style="font-size: 24px; text-align: center; margin-top: 65px;">
        Sorcom Concrete Design Masterlist
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
                    'wc_ratio' => 'W/C Ratio',
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

    <!-- CSV Save Button at bottom -->
    <button id="saveCsvBtn">💾 Save as CSV File</button>

    <!-- CSV Save Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('saveCsvBtn');
            btn.addEventListener('click', function () {
                const table = document.querySelector('table');
                if (!table) return alert("Table not found!");

                const rows = table.querySelectorAll('tr');
                const csv = [];

                rows.forEach(row => {
                    const cols = row.querySelectorAll('th, td');
                    const rowData = [];

                    cols.forEach((col, index) => {
                        // Skip last column (Action)
                        if (index === cols.length - 1) return;

                        let data = col.innerText.replace(/"/g, '""');
                        if (data.includes(',') || data.includes('"')) {
                            data = `"${data}"`;
                        }
                        rowData.push(data);
                    });

                    csv.push(rowData.join(','));
                });

                const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
                const downloadLink = document.createElement('a');
                downloadLink.download = 'concrete_design_masterlist.csv';
                downloadLink.href = URL.createObjectURL(csvFile);
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        });
    </script>
</body>

</html>