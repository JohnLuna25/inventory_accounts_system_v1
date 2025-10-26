<?php
// 1. Include the database connection file (Required for DB queries)
require_once '../FunctionAndDB/db_connect.php';

// 2. Include the file with your web functions (Required for showHeader())
require_once '../FunctionAndDB/Web_Functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SORCOM Inventory Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="font-family: Arial; text-align: center;">

<?php
// Call the header function defined in Web_Functions.php
showHeader();
?>

<h2 style="text-align: left; margin-left: 5%; margin-top: 80px;">
    Ready-Mix Inventory
</h2>
<br>

<div style="text-align: right; margin-right: 5%; margin-bottom: 20px;">
    <button 
        onclick="openAddModal()"
        style="padding: 10px 15px; font-size: 16px; cursor: pointer; margin-left: 10px; border: none; border-radius: 5px; background-color: #0b63c4; color: white; transition: background-color 0.3s;"
        onmouseover="this.style.backgroundColor='#084f9b'"
        onmouseout="this.style.backgroundColor='#0b63c4'"
    >
        Add Inventory
    </button>
</div>

<?php
// Fetch readymix inventory, hide rows without design_mix_id
$result = $conn->query("SELECT * FROM sorcom_readymix_inventory 
                         WHERE design_mix_id IS NOT NULL AND design_mix_id != ''
                         ORDER BY date_added DESC");

if (!$result) {
    die("Query failed: " . $conn->error);
}

echo '<table border="1" cellpadding="8" style="margin:auto; border-collapse:collapse; width:90%">';
echo '<thead style="background:#0b63c4; color:#fff;">
        <tr>
            <th style="padding: 8px;">Edit</th> <th style="padding: 8px;">ID</th>
            <th style="padding: 8px;">Date</th>
            <th style="padding: 8px;">Design Mix ID</th>
            <th style="padding: 8px;">Description</th>
            <th style="padding: 8px;">Batch</th>
            <th style="padding: 8px;">Stocks In</th>
            <th style="padding: 8px;">Stocks Out</th>
            <th style="padding: 8px;">Remarks</th>
            <th style="padding: 8px;">Delete</th> </tr>
    </thead><tbody>';

while ($row = $result->fetch_assoc()) {
    $inventory_id = htmlspecialchars($row['id']);
    // Encode the entire row data to easily pass it to the Edit function
    $row_data = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); 
    
    // Inline styles for table action buttons
    $edit_btn_style = 'padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; color: white; font-size: 14px; background-color: #ff9800;'; // Orange
    $delete_btn_style = 'padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; color: white; font-size: 14px; background-color: #f44336;'; // Red

    echo '<tr>';
    // Edit Button (on the left side)
    echo '<td style="text-align: center;">
            <button 
                onclick="openEditModal(\''.$row_data.'\')"
                style="'.$edit_btn_style.'"
                onmouseover="this.style.backgroundColor=\'#e68900\'"
                onmouseout="this.style.backgroundColor=\'#ff9800\'"
            >
                Edit
            </button>
          </td>'; 
    echo '<td style="text-align: center;">'.$inventory_id.'</td>';
    echo '<td style="text-align: center;">'.htmlspecialchars($row['date_added']).'</td>';
    echo '<td style="text-align: center;">'.htmlspecialchars($row['design_mix_id']).'</td>';
    echo '<td style="text-align: center;">'.htmlspecialchars($row['description']).'</td>';
    echo '<td style="text-align: center;">'.htmlspecialchars($row['batch']).'</td>';
    // Hide zeros in stocks_in and stocks_out
    echo '<td style="text-align: center;">'.(htmlspecialchars($row['stocks_in']) != 0 ? htmlspecialchars($row['stocks_in']) : '').'</td>';
    echo '<td style="text-align: center;">'.(htmlspecialchars($row['stocks_out']) != 0 ? htmlspecialchars($row['stocks_out']) : '').'</td>';
    echo '<td style="text-align: left;">'.htmlspecialchars($row['remarks']).'</td>'; 
    // Delete Button (on the right side)
    echo '<td style="text-align: center;">
            <button 
                onclick="handleDelete(\''.$inventory_id.'\')"
                style="'.$delete_btn_style.'"
                onmouseover="this.style.backgroundColor=\'#da190b\'"
                onmouseout="this.style.backgroundColor=\'#f44336\'"
            >
                Delete
            </button>
          </td>';
    echo '</tr>';
}

echo '</tbody></table>';

$conn->close();
?>

<div id="addInventoryModal" 
    style="display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);"
    onclick="if (event.target === this) checkCancel('addInventoryModal', 'addInventoryForm')"
>
    <div style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19); text-align: left;">
        <h3 style="text-align: center; margin-top: 0;">Add Inventory Details</h3>
        <form id="addInventoryForm">
            <label for="add_date" style="display: block; margin-bottom: 5px; font-weight: bold;">Date added:</label>
            <input type="date" id="add_date" name="date_added" required 
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="add_design_mix" style="display: block; margin-bottom: 5px; font-weight: bold;">Design Mix:</label>
            <input type="text" id="add_design_mix" name="design_mix" required
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="add_description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description: (Placeholder saying: days and type)</label>
            <input type="text" id="add_description" name="description" placeholder="days and type" required
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="add_batch" style="display: block; margin-bottom: 5px; font-weight: bold;">Batch:</label>
            <input type="text" id="add_batch" name="batch"
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="add_stocks_in" style="display: block; margin-bottom: 5px; font-weight: bold;">Stocks in:</label>
            <input type="number" id="add_stocks_in" name="stocks_in" min="0"
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="add_stocks_out" style="display: block; margin-bottom: 5px; font-weight: bold;">Stocks out:</label>
            <input type="number" id="add_stocks_out" name="stocks_out" min="0"
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="add_remarks" style="display: block; margin-bottom: 5px; font-weight: bold;">Remarks:</label>
            <textarea id="add_remarks" name="remarks"
                      style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"></textarea>
            
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="handleAddSubmit()"
                        style="padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; background-color: #4CAF50; color: white; margin-left: 10px;"
                        onmouseover="this.style.backgroundColor='#45a049'"
                        onmouseout="this.style.backgroundColor='#4CAF50'"
                >
                    Add
                </button>
                <button type="button" onclick="checkCancel('addInventoryModal', 'addInventoryForm')"
                        style="padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; background-color: #ccc; color: #333; margin-left: 10px;"
                        onmouseover="this.style.backgroundColor='#bbb'"
                        onmouseout="this.style.backgroundColor='#ccc'"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editInventoryModal" 
    style="display: none; position: fixed; z-index: 10; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);"
    onclick="if (event.target === this) checkCancel('editInventoryModal', 'editInventoryForm', true)"
>
    <div style="background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19); text-align: left;">
        <h3 style="text-align: center; margin-top: 0;">Edit Inventory Details</h3>
        <form id="editInventoryForm">
            <input type="hidden" id="edit_id" name="id">
            <input type="hidden" id="original_data" name="original_data">

            <label for="edit_date" style="display: block; margin-bottom: 5px; font-weight: bold;">Date added:</label>
            <input type="date" id="edit_date" name="date_added" required
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="edit_design_mix" style="display: block; margin-bottom: 5px; font-weight: bold;">Design Mix:</label>
            <input type="text" id="edit_design_mix" name="design_mix" required
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="edit_description" style="display: block; margin-bottom: 5px; font-weight: bold;">Description: (days and type)</label>
            <input type="text" id="edit_description" name="description" required
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="edit_batch" style="display: block; margin-bottom: 5px; font-weight: bold;">Batch:</label>
            <input type="text" id="edit_batch" name="batch"
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="edit_stocks_in" style="display: block; margin-bottom: 5px; font-weight: bold;">Stocks in:</label>
            <input type="number" id="edit_stocks_in" name="stocks_in" min="0"
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="edit_stocks_out" style="display: block; margin-bottom: 5px; font-weight: bold;">Stocks out:</label>
            <input type="number" id="edit_stocks_out" name="stocks_out" min="0"
                   style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            
            <label for="edit_remarks" style="display: block; margin-bottom: 5px; font-weight: bold;">Remarks:</label>
            <textarea id="edit_remarks" name="remarks"
                      style="display: block; margin-bottom: 15px; width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"></textarea>
            
            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="handleEditDone()"
                        style="padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; background-color: #4CAF50; color: white; margin-left: 10px;"
                        onmouseover="this.style.backgroundColor='#45a049'"
                        onmouseout="this.style.backgroundColor='#4CAF50'"
                >
                    Done
                </button>
                <button type="button" onclick="checkCancel('editInventoryModal', 'editInventoryForm', true)"
                        style="padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; background-color: #ccc; color: #333; margin-left: 10px;"
                        onmouseover="this.style.backgroundColor='#bbb'"
                        onmouseout="this.style.backgroundColor='#ccc'"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>


</body>
</html>