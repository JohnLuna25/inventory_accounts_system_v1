<?php
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

// Fetch inventory
$result = $conn->query("SELECT * FROM sorcom_readymix_inventory ORDER BY date_added DESC, id ASC");
if (!$result)
    die("Inventory query failed: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #f4f4f4;
        }

        a.edit-inventory-btn {
            background: #4caf50;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        a.edit-inventory-btn:hover {
            background: #45a049;
        }

        button.delete-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
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
                <th>ID</th>
                <th>Date</th>
                <th>Design Mix</th>
                <th>Batch</th>
                <th>Stocks In</th>
                <th>Stocks Out</th>
                <th>Current Stock</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()):
                $current = intval($row['stocks_in']) - intval($row['stocks_out']); ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['date_added']) ?></td>
                    <td><?= htmlspecialchars($row['design_mix_id']) ?></td>
                    <td><?= htmlspecialchars($row['batch']) ?></td>
                    <td><?= intval($row['stocks_in']) ?></td>
                    <td><?= intval($row['stocks_out']) ?></td>
                    <td><?= $current ?></td>
                    <td class="remarks" title="<?= htmlspecialchars($row['remarks']) ?>">
                    <td>
                        <a href="ip_editModal.php?id=<?= $row['id'] ?>" class="edit-modal-btn">Edit</a>

                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php $conn->close(); ?>
</body>

</html>