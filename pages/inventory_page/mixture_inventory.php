<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM sorcom_mixture_inventory ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SORCOM Mixture Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../inventory_page/mixture_inventory.css">
    <?php showHeader(); ?>
</head>

<body>

    <h2>Mixture Inventory</h2>

    <div style="text-align: left; width: 97%; margin: 20px auto;">
        <a href="addMixture_inventory.php" class="mixture-controls">+ Add Mixture</a>
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
                    <td><?php echo $row['total']; ?></td>
                    <td>
                        <a href="editMixture_Inventory.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="deleteMixture_Inventory.php?id=<?php echo $row['id']; ?>" class="btn-delete"
                            onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="14">No data found.</td>
            </tr>
        <?php endif; ?>
    </table>

</body>

</html>