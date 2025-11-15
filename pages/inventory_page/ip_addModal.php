<?php

echo '
<div id="addInventoryModal" class="modal">
    <div class="modal-content">
        <h3>Add Inventory</h3>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <label>Date:</label>
            <input type="date" name="date_added" required value="' . date('Y-m-d') . '">
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
            <button type="button" onclick="closeModal(\'addInventoryModal\')">Cancel</button>
        </form>
    </div>
</div>
';

?>