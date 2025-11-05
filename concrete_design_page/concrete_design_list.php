<?php
// Include required files
include __DIR__ . '/../FunctionAndDB/db_connect.php';
include __DIR__ . '/../FunctionAndDB/Web_Functions.php';

if (!isset($conn)) {
    die("Database connection not established. Check db_connect.php.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Concrete Design List</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php showHeader(); ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #BFC3AA;
            color: #000;
            text-align: center;
        }


        table {
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 14px;
        }

        th {
            background: #0b63c4;
            color: #fff;
        }

        tr:hover {
            background: #014185ff;
            color: white;
        }

        a.button {
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a.view {
            background: #4CAF50;
            margin-right: 5px;
        }

        a.edit {
            background: #2196F3;
        }

        /* Dropbar style */
        .dropbar {
            display: none;
            background-color: #0b63c4;
            color: yellow;
            padding: 10px;
            text-align: left;
            border-radius: 0 0 8px 8px;
        }

        .dropbar table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            color: black;
        }

        .dropbar td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .dropbar td:first-child {
            font-weight: bold;
            text-align: left;
        }

        .dropbar td:last-child {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2 style="font-size: 24px; text-align: center; margin-top: 65px;">Concrete Design List</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>PSI</th>
                <th>Curration Type</th>
                <th>Type</th>
                <th>Cubic Meter</th>
                <th>Slump</th>
                <th>W/C Ratio</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT * FROM concrete_designs ORDER BY date_created DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $id = $row['id'];
                    echo "
                    <tr>
                        <td>{$id}</td>
                        <td>{$row['concrete_psi']}</td>
                        <td>{$row['curration_type']}</td>
                        <td>{$row['concrete_type']}</td>
                        <td>{$row['cubic_meter']}</td>
                        <td>{$row['concrete_slump']}</td>
                        <td>{$row['wc_ratio']}</td>
                        <td>
                            <a href='javascript:void(0)' class='button view' onclick='toggleDropbar($id)'>View</a>
                            <a class='button edit' href='edit_design.php?id={$id}'>Edit</a>
                        </td>
                    </tr>

                    <tr id='dropbar-{$id}' class='dropbar'>
                        <td colspan='8'>
                            <table>
                                <tr><td>PSI</td><td>{$row['concrete_psi']}</td></tr>
                                <tr><td>Curration Type</td><td>{$row['curration_type']}</td></tr>
                                <tr><td>Concrete Type</td><td>{$row['concrete_type']}</td></tr>
                                <tr><td>Cubic Meter</td><td>{$row['cubic_meter']}</td></tr>
                                <tr><td>Slump</td><td>{$row['concrete_slump']}</td></tr>
                                <tr><td>W/C Ratio</td><td>{$row['wc_ratio']}</td></tr>

                                <tr>
                                    <td colspan='2'>
                                        <strong>Volume Batch:</strong>
                                        <table style='width:100%; margin-top:5px; border-collapse: collapse;'>
                                            <tr style='background:#f2f2f2;'>
                                                <th>Cement</th>
                                                <th>Fly ash</th>
                                                <th>Water</th>
                                                <th>Admix</th>
                                                <th>Air</th>
                                                <th>G-1</th>
                                                <th>3/4\"</th>
                                                <th>3/8\"</th>
                                                <th>Sand</th>
                                            </tr>
                                            <tr>
                                                <td>13.00 kgs</td>
                                                <td>—</td>
                                                <td>8.90 ltr</td>
                                                <td>0.13 ltr</td>
                                                <td>—</td>
                                                <td>—</td>
                                                <td>47.75 kgs</td>
                                                <td>—</td>
                                                <td>46.40 kgs</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    ";
                }
            } else {
                echo "<tr><td colspan='8'>No concrete designs found.</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>

    <script>
        function toggleDropbar(id) {
            // Close all open dropbars first
            document.querySelectorAll('.dropbar').forEach(bar => {
                if (bar.id !== 'dropbar-' + id) bar.style.display = 'none';
            });

            // Toggle the selected one
            const dropbar = document.getElementById('dropbar-' + id);
            dropbar.style.display = (dropbar.style.display === 'table-row') ? 'none' : 'table-row';
        }

        // Close dropbars when clicking outside
        window.addEventListener('click', function (e) {
            if (!e.target.classList.contains('view')) {
                document.querySelectorAll('.dropbar').forEach(bar => bar.style.display = 'none');
            }
        });
    </script>

</body>

</html>