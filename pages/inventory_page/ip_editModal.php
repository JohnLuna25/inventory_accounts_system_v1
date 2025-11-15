<?php
echo '
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
                    <button type="button" onclick="closeModal(\'editInventoryModal\')">Cancel</button>
                </form>
            </div>
        </div>
    ';
?>