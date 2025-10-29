<?php
require_once '../FunctionAndDB/db_connect.php';
require_once '../FunctionAndDB/Web_Functions.php';

// --------------------------
// Helper function
// --------------------------
function nullIfEmpty($value)
{
    return ($value === '' || $value === '0') ? null : $value;
}

// --------------------------
// Handle form submissions
// --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stocks_in = nullIfEmpty($_POST['stocks_in']);
        $stocks_out = nullIfEmpty($_POST['stocks_out']);
        $description = nullIfEmpty($_POST['description']);
        $batch = nullIfEmpty($_POST['batch']);
        $remarks = nullIfEmpty($_POST['remarks']);

        $stmt = $conn->prepare("INSERT INTO sorcom_readymix_inventory 
                (date_added, design_mix_id, description, batch, stocks_in, stocks_out, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssdds",
            $_POST['date_added'],
            $_POST['design_mix'],
            $description,
            $batch,
            $stocks_in,
            $stocks_out,
            $remarks
        );
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'edit') {
        $stocks_in = nullIfEmpty($_POST['stocks_in']);
        $stocks_out = nullIfEmpty($_POST['stocks_out']);
        $description = nullIfEmpty($_POST['description']);
        $batch = nullIfEmpty($_POST['batch']);
        $remarks = nullIfEmpty($_POST['remarks']);

        $stmt = $conn->prepare("UPDATE sorcom_readymix_inventory 
                SET date_added=?, design_mix_id=?, description=?, batch=?, stocks_in=?, stocks_out=?, remarks=? 
                WHERE id=?");
        $stmt->bind_param(
            "ssssddsi",
            $_POST['date_added'],
            $_POST['design_mix'],
            $description,
            $batch,
            $stocks_in,
            $stocks_out,
            $remarks,
            $_POST['id']
        );
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM sorcom_readymix_inventory WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SORCOM Inventory Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php showHeader(); ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin-top: 120px;
            background: #f9f9f9;
            color: #333;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
            background: #ffffffff
        }

        table th,
        table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        table th {
            background: #0b63c4;
            color: #fff;
        }

        button {
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            width: 450px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 5px;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content input,
        .modal-content textarea {
            width: 100%;
            margin: 5px 0 10px 0;
            padding: 5px;
            box-sizing: border-box;
        }

        .modal-content button {
            margin-right: 5px;
        }
    </style>
</head>

<body style="font-family: Arial; text-align: center; background-color: #BFC3AA;">

    <h2 style="color: #000;">Ready-Mix Inventory</h2>

    <div style="text-align:left; margin-left: 5%;">
        <button onclick="openModal('addInventoryModal')" style="background: #211de6ff; color: white;">Add Item</button>
        <button onclick="location.href='mixture_inventory.php'" style="background: #211de6ff; color: white;">Open
            Mixture Inventory</button>
    </div>

    <?php
    $sql = "SELECT * FROM sorcom_readymix_inventory
                    WHERE 
                        (description IS NOT NULL AND description <> '') OR
                        (batch IS NOT NULL AND batch <> '') OR
                        (remarks IS NOT NULL AND remarks <> '') OR
                        (stocks_in IS NOT NULL AND stocks_in <> 0) OR
                        (stocks_out IS NOT NULL AND stocks_out <> 0) OR
                        (date_added IS NOT NULL AND date_added <> '') OR
                        (design_mix_id IS NOT NULL AND design_mix_id <> '')
                    ORDER BY date_added DESC, id ASC";

    $result = $conn->query($sql);

    echo '<table>
            <thead>
            <tr>
            <th>ID</th><th>Date</th><th>Design Mix ID</th><th>Description</th><th>Batch</th>
            <th>Stocks In</th><th>Stocks Out</th><th>Remarks</th><th>Actions</th>

            </tr></thead><tbody>';

    while ($row = $result->fetch_assoc()) {
        $row_json = htmlspecialchars(json_encode($row), ENT_QUOTES);
        echo '<tr>';
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['date_added']}</td>";
        echo "<td>{$row['design_mix_id']}</td>";
        echo "<td>{$row['description']}</td>";
        echo "<td>{$row['batch']}</td>";
        echo "<td>{$row['stocks_in']}</td>";
        echo "<td>{$row['stocks_out']}</td>";
        echo "<td>{$row['remarks']}</td>";
        echo "<td style='display: flex; gap: 5px; justify-content: center;'>
                        <button onclick='openEditModal($row_json)' 
                                style='background:#4CAF50; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'>
                            Edit
                        </button>
                        <form method='POST' style='margin:0;' onsubmit='return confirm(\"Are you sure you want to delete this?\")'>
                            <input type='hidden' name='id' value='{$row['id']}'>
                            <input type='hidden' name='action' value='delete'>
                            <button type='submit' 
                                    style='background:#f44336; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'>
                                Delete
                            </button>
                        </form>
                    </td>";

        echo '</tr>';
    }
    echo '</tbody></table>';
    $conn->close();
    ?>

    <!-- ADD MODAL -->
    <div id="addInventoryModal" class="modal">
        <div class="modal-content">
            <h3>Add Inventory</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <label>Date:</label>
                <input type="date" name="date_added" required value="<?= date('Y-m-d') ?>">
                <label>Design Mix ID:</label>
                <input type="text" name="design_mix" required>
                <label>Description:</label>
                <input type="text" name="description">
                <label>Batch:</label>
                <input type="text" name="batch">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in">
                <label>Stocks Out:</label>
                <input type="number" name="stocks_out">
                <label>Remarks:</label>
                <textarea name="remarks"></textarea>
                <button type="submit">Add</button>
                <button type="button" onclick="closeModal('addInventoryModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editInventoryModal" class="modal">
        <div class="modal-content">
            <h3>Edit Inventory</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <label>Date:</label>
                <input type="date" name="date_added" id="edit_date">
                <label>Design Mix ID:</label>
                <input type="text" name="design_mix" id="edit_design">
                <label>Description:</label>
                <input type="text" name="description" id="edit_description">
                <label>Batch:</label>
                <input type="text" name="batch" id="edit_batch">
                <label>Stocks In:</label>
                <input type="number" name="stocks_in" id="edit_stocks_in">
                <label>Stocks Out:</label>
                <input type="number" name="stocks_out" id="edit_stocks_out">
                <label>Remarks:</label>
                <textarea name="remarks" id="edit_remarks"></textarea>
                <button type="submit">Save</button>
                <button type="button" onclick="closeModal('editInventoryModal')">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openEditModal(row) {
            document.getElementById('edit_id').value = row.id;
            document.getElementById('edit_date').value = row.date_added;
            document.getElementById('edit_design').value = row.design_mix_id;
            document.getElementById('edit_description').value = row.description;
            document.getElementById('edit_batch').value = row.batch;
            document.getElementById('edit_stocks_in').value = row.stocks_in ?? '';
            document.getElementById('edit_stocks_out').value = row.stocks_out ?? '';
            document.getElementById('edit_remarks').value = row.remarks;
            openModal('editInventoryModal');
        }


    </script>

</body>

</html>