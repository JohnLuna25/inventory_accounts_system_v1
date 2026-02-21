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

// Fetch inventory
$result = $conn->query("SELECT * FROM sorcom_readymix_inventory ORDER BY date_added DESC, id ASC");
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
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            width: 95%;
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

        .edit-btn {
            background: #0b63c4;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .edit-btn:hover {
            background: #0e4a8b;
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

                <th>Date</th>
                <th>Design Mix</th>
                <th>Curation Days</th>
                <th>Concrete Type</th>
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
                    <td><?= htmlspecialchars($row['date_added']) ?></td>
                    <td><?= htmlspecialchars($row['design_mix_id']) ?></td>
                    <td><?= isset($row['curation_days']) ? intval($row['curation_days']) : '0' ?></td>
                    <td><?= htmlspecialchars($row['concrete_type']) ?></td>
                    <td><?= htmlspecialchars($row['batch']) ?></td>
                    <td><?= intval($row['stocks_in']) ?></td>
                    <td><?= intval($row['stocks_out']) ?></td>
                    <td><?= $current ?></td>
                    <td><?= htmlspecialchars($row['remarks']) ?></td>
                    <td>
                        <!-- EDIT BUTTON - Using link instead of form -->
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
        </tbody>
    </table>

    <?php $conn->close(); ?>
</body>

</html>