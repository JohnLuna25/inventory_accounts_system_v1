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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="ip_table.css">

    <?php showHeader(); ?>
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
                    <th>Batch (kgs/m³)</th>
                    <th>Stocks In</th>
                    <th>Stock Out</th>
                    <th>Current Stock (kgs/m³)</th>
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
                            <td><?= number_format(floatval($row['batch'] ?? 0), 1) ?></td>
                            <td><?= intval($row['stocks_in'] ?? 0) ?></td>
                            <td><?= intval($row['stocks_out'] ?? 0) ?></td>
                            <td><?= number_format(floatval($row['current_stock_batch'] ?? 0), 1) ?></td>
                            <td><?= htmlspecialchars($row['remarks'] ?? '') ?></td>
                            <td>
                                <a href="ip_form.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
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