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
    $stmt = $conn->prepare("DELETE FROM sorcom_readymix_inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch inventory with Batch from concrete_design_masterlist
$sql = "SELECT 
            i.id,
            i.date_added,
            i.design_mix_id,
            i.curation_days,
            i.concrete_type,
            i.stocks_in,
            i.stocks_out,
            i.remarks,
            d.total as batch
        FROM sorcom_readymix_inventory i
        LEFT JOIN sorcom_concrete_design_masterlist d 
            ON i.design_mix_id = d.concrete_psi 
            AND i.curation_days = d.curation_days 
            AND i.concrete_type = d.concrete_type
        ORDER BY i.date_added DESC, i.id ASC";

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
                <th>Current Stock (in batch)</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    $stocks_in = intval($row['stocks_in']);
                    $stocks_out = intval($row['stocks_out']);
                    $batch = floatval($row['batch'] ?? 0);
                    $current_stock_batch = ($stocks_in - $stocks_out) * $batch;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['date_added']) ?></td>
                        <td><?= htmlspecialchars($row['design_mix_id']) ?></td>
                        <td><?= isset($row['curation_days']) ? intval($row['curation_days']) : '0' ?></td>
                        <td><?= htmlspecialchars($row['concrete_type']) ?></td>
                        <td><?= number_format($batch, 1) ?> kgs/m³</td>
                        <td><?= $stocks_in ?></td>
                        <td><?= $stocks_out ?></td>
                        <td><?= number_format($current_stock_batch, 1) ?> kgs/m³</td>
                        <td><?= htmlspecialchars($row['remarks']) ?></td>
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
                    <td colspan="10" style="text-align: center;">No inventory records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php $conn->close(); ?>
</body>

</html>