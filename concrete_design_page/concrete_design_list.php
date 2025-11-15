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

<?php include 'concrete_design.php' ?>

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