<?php
session_start();
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Check if user is logged in and is master (Plant Manager only)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'master') {
    header("Location: /Inventory-Accounts-System-v1/login_page/web_login.php");
    exit();
}

$message = "";
$messageType = "";

// Handle Restore action
if (isset($_GET['restore'])) {
    $id = intval($_GET['restore']);
    $stmt = $conn->prepare("UPDATE sorcom_readymix_inventory SET recycled_data = 0, recycled_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: recycle_inventory.php?msg=restored");
        exit();
    } else {
        header("Location: recycle_inventory.php?msg=error");
        exit();
    }
    $stmt->close();
}

// Handle Permanent Erase action
if (isset($_GET['erase'])) {
    $id = intval($_GET['erase']);
    $stmt = $conn->prepare("DELETE FROM sorcom_readymix_inventory WHERE id = ? AND recycled_data = 1");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: recycle_inventory.php?msg=erased");
        exit();
    } else {
        header("Location: recycle_inventory.php?msg=error");
        exit();
    }
    $stmt->close();
}

// Show message based on URL parameter
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'restored') {
        $message = "Record restored successfully!";
        $messageType = "success";
    } elseif ($_GET['msg'] == 'erased') {
        $message = "Record permanently deleted!";
        $messageType = "success";
    } elseif ($_GET['msg'] == 'error') {
        $message = "Operation failed. Please try again.";
        $messageType = "error";
    }
}

// Fetch recycled records
$query = "SELECT * FROM sorcom_readymix_inventory WHERE recycled_data = 1 ORDER BY recycled_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycle Bin - SORCOM</title>
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: "Inter", sans-serif !important;
            background:
                linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                url("/Inventory-Accounts-System-v1/assets/images/SBP.png") no-repeat center center fixed !important;
            background-size: cover !important;
            font-family: 'Poppins', sans-serif;
        }

        .container-custom {
            max-width: 1400px;
            margin: 100px auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .page-title {
            color: #2e3590;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-top: 50px;
        }

        .table-custom th,
        .table-custom td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
        }

        .table-custom th {
            background: #2e3590;
            color: white;
            text-align: center;
        }

        /* For the expandable details table */
        #details-table {
            width: 100%;
            border: none;
        }

        #details-table th,
        #details-table td {
            text-align: center;
            vertical-align: middle;
            border: none;
            padding: 8px;
        }

        .btn-restore {
            background: #4caf50;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            margin-right: 5px;
        }

        .btn-erase {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
        }

        .message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <?php showHeader(); ?>

    <div class="container-custom">
        <h2 class="page-title"><i class="fas fa-trash-restore"></i> Recycle Bin</h2>
        <p>Records here will be automatically deleted after 30 days.</p>
        <div style="margin-top: 20px; text-align: right;">
            <a href="inventory_management.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Inventory</a>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <table class="table-custom">
            <thead>
                <tr>
                    <th>Recycled At</th>
                    <th>Date Created</th>
                    <th>ID</th>
                    <th>Design Mix</th>
                    <th>Curation Days</th>
                    <th>Concrete Type</th>
                    <th>Batch (kgs/m³)</th>
                    <th>Table Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($row['recycled_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['date_added']); ?></td>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['design_mix_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['curation_days']); ?></td>
                            <td><?php echo htmlspecialchars($row['concrete_type']); ?></td>
                            <td><?php echo number_format($row['batch'], 1); ?></td>
                            <td>
                                <button class="btn-restore" onclick="toggleDetails(<?php echo $row['id']; ?>)">
                                    View
                                </button>
                                <a href="?restore=<?php echo $row['id']; ?>" class="btn-restore"
                                    onclick="return confirm('Restore this record?')">
                                    Restore
                                </a>
                                <a href="?erase=<?php echo $row['id']; ?>" class="btn-erase"
                                    onclick="return confirm('Permanently delete this record? This cannot be undone.')">
                                    Erase
                                </a>
                            </td>
                        </tr>
                        <!-- Expandable row for details -->
                        <tr id="details-<?php echo $row['id']; ?>" style="display: none; background-color: #f9f9f9;">
                            <td colspan="8">
                                <table style="width: 100%; border: none;">
                                    <tr>
                                        <th style="border: none; padding: 8px;">Stocks In</th>
                                        <th style="border: none; padding: 8px;">Stocks Out</th>
                                        <th style="border: none; padding: 8px;">Current Stock (kgs/m³)</th>
                                        <th style="border: none; padding: 8px;">Remarks</th>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 8px;"><?php echo $row['stocks_in']; ?></td>
                                        <td style="border: none; padding: 8px;"><?php echo $row['stocks_out']; ?></td>
                                        <td style="border: none; padding: 8px;">
                                            <?php echo number_format($row['current_stock_batch'], 1); ?>
                                        </td>
                                        <td style="border: none; padding: 8px;"><?php echo htmlspecialchars($row['remarks']); ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No records in recycle bin.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


    </div>

    <script>
        function toggleDetails(id) {
            var row = document.getElementById('details-' + id);
            if (row.style.display === 'none' || row.style.display === '') {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        }
    </script>

</body>

</html>