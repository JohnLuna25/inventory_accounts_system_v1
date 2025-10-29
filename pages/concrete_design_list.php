<?php
// Always use reliable include paths
include __DIR__ . '/../FunctionAndDB/db_connect.php';
include __DIR__ . '/../FunctionAndDB/Web_Functions.php';

// Ensure connection is valid
if (!isset($conn)) {
    die("Database connection not established. Check db_connect.php.");
}

// Fetch all rows safely
$query = "SELECT * FROM concrete_designs";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <br><br>
    <title>Concrete Design List</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f6f6f6; margin: 0; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border:1px solid #ddd; padding:8px; text-align:center; font-size:14px; }
        th { background:#0b63c4; color: #fff; }
        tr:hover { background:#f0f8ff; }
    </style>
</head>
<body style="font-family: Arial; text-align: center; background-color: #BFC3AA;">

    <?php showHeader(); ?>

    <h2 style="font-size: 24px; text-align: center; margin-top: 40px;">Concrete Design List</h2>

    <table>
        <thead>
            <tr>
                <th>Materials</th>
                <th>Cement</th>
                <th>Fly Ash</th>
                <th>Water</th>
                <th>Admix</th>
                <th>Air</th>
                <th>G1</th>
                <th>3/4"</th>
                <th>3/8"</th>
                <th>FA</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Materials']) ?></td>
                    <td><?= htmlspecialchars($row['Cement']) ?></td>
                    <td><?= htmlspecialchars($row['Fly_ash']) ?></td>
                    <td><?= htmlspecialchars($row['Water']) ?></td>
                    <td><?= htmlspecialchars($row['Admix']) ?></td>
                    <td><?= htmlspecialchars($row['Air']) ?></td>
                    <td><?= htmlspecialchars($row['G1']) ?></td>
                    <td><?= htmlspecialchars($row['three_quarter']) ?></td>
                    <td><?= htmlspecialchars($row['three_eighth']) ?></td>
                    <td><?= htmlspecialchars($row['FA']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

<?php $conn->close(); ?>
