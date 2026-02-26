<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'sorcom_db');
if ($conn->connect_error)
    die("Connection failed: " . $conn->connect_error);

// Handle Delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM Sorcom_readymix_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch inventory
$sql = "SELECT 
            id,
            date_added,
            design_mix_id,
            batch,
            stocks_in,
            stocks_out,
            remarks,
            concrete_type,
            curation_days,
            current_stock_batch
        FROM Sorcom_readymix_inventory
        ORDER BY date_added DESC, id ASC";

$result = $conn->query($sql);
if (!$result)
    die("Inventory query failed: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="ip_table.css">
    <?php showHeader() ?>

    <style>
        body {
            font-family: Arial;
            padding: 15px;
        }

        table {
            border-collapse: collapse;
            width: 99%;
            margin: 15px auto;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: center;
            font-size: 12px;
            white-space: nowrap;
        }

        th {
            background: #f4f4f4;
            font-size: 11px;
        }

        .edit-btn {
            background: #0b63c4;
            color: white;
            border: none;
            padding: 3px 6px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 10px;
        }

        .edit-btn:hover {
            background: #0e4a8b;
        }

        button.delete-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 3px 6px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
        }

        button.delete-btn:hover {
            background: #d32f2f;
        }
    </style>
</head>

<body>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Design Mix</th>
                <th>Curation Days</th>
                <th>Concrete Type</th>
                <th>Batch</th>
                <th>Stocks In</th>
                <th>Stock Out</th>
                <th>Current Stock</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['date_added'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['design_mix_id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['curation_days'] ?? '') ?></td>
                        <td>
                            <?= htmlspecialchars($row['concrete_type'] ?? '') ?>
                        <td><?= number_format(floatval($row['batch'] ?? 0), 1) ?> kgs/m³</td>
                        <td><?= intval($row['stocks_in'] ?? 0) ?></td>
                        <td><?= intval($row['stocks_out'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($row['current_stock_batch'] ?? '') ?></td>


                        <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
                        <td>
                            <!-- EDIT BUTTON -->
                            <a href="ip_editForm.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>

                            <!-- DELETE BUTTON -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" style="text-align: center;">No inventory records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php $conn->close(); ?>
</body>

</html>