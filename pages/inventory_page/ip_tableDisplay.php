<?php
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

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
        FROM sorcom_readymix_inventory
        ORDER BY date_added DESC, id DESC";

$result = $conn->query($sql);
if (!$result)
    die("Inventory query failed: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="ip_table.css">
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <?php showHeader() ?>

    <style>
        body {
            font-family: Arial;
            padding: 15px;
        }

        table {
            border-collapse: collapse;
            width: 99.5%;
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

        /* For Stock Buttons */
        .triangle-btn {
            width: 0 !important;
            height: 0 !important;
            border-style: solid !important;
            cursor: pointer;
            transition: transform 0.2s ease;
            display: inline-block !important;
            margin: 0 5px;
            padding: 0 !important;
            border-radius: 0 !important;
            vertical-align: middle !important;
        }

        /* Container Fix: Prevents buttons from wrapping to next line */
        .stock-in-actions,
        .stock-out-actions {
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center;
        }

        /* Green Triangle (Increment) - Pointing Up */
        .btn-green {
            border-width: 0 8px 12px 8px !important;
            border-color: transparent transparent #28a745 transparent !important;
        }

        /* Red Triangle (Decrement) - Pointing Down */
        .btn-red {
            border-width: 12px 8px 0 8px !important;
            border-color: #dc3545 transparent transparent transparent !important;
        }

        /* Hover Effects */
        .triangle-btn:hover {
            transform: scale(1.1);
            filter: brightness(1.1);
        }

        /* Active/Click Effect */
        .triangle-btn:active {
            transform: scale(0.95);
        }
    </style>
</head>

<body>
    <div class="table-container">
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
                    <th>Table Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date_added'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['design_mix_id'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['curation_days'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['concrete_type'] ?? '') ?></td>
                            <td><?= number_format(floatval($row['batch'] ?? 0), 1) ?> kgs/m³</td>
                            <td><?= intval($row['stocks_in'] ?? 0) ?></td>
                            <td><?= intval($row['stocks_out'] ?? 0) ?></td>
                            <td><?= number_format(floatval($row['current_stock_batch'] ?? 0), 1) ?> kgs/m³</td>
                            <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
                            <td>
                                <a href="ip_editForm.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
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
    </div>
    <?php $conn->close(); ?>
</body>

</html>