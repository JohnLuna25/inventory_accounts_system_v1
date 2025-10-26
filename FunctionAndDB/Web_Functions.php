<?php
function showHeader() {
    echo '
    <div style="
        background-color: blue; 
        padding: 5px 10px; 
        width: 100%; 
        margin: 0; 
        text-align: center; 
        position: fixed; 
        top: 0; 
        left: 0;
        z-index: 1000;
    ">
        <h1 style="
            font-family: Arial, sans-serif; 
            font-size: 20px; 
            font-weight: bold; 
            color: yellow; 
            text-align: center; 
            margin: 0;
        ">
            SORCOM READY-MIX CONCRETE
        </h1>

        <p style="
            font-family: Arial, sans-serif; 
            font-size: 14px; 
            font-weight: 600; 
            color: yellow; 
            text-align: center; 
            margin: 0;
        ">
            Inventory and Accounts Management System.
        </p>

        <p style="
            font-family: Arial, sans-serif; 
            font-weight: semibold,
            font-size: 15px; 
            color: yellow; 
            text-align: right; 
            margin: 2px 10px 0 0;
        ">
            '.date("M d Y").'
            <br>
        </p>
        <a href="home.php" 
            style="
                font-family: Arial, sans-serif;
                font-size: 15px;
                color: yellow;
                text-decoration: none;
                position: fixed;
                top: 10px; 
                left: 10px;   
    ">
  ⬅️ Return Home
</a>
    </div>
    ';
}
?>


<script>
// ==============================================
// --- JavaScript Functions (Unchanged Logic for Modals) ---
// ==============================================

/**
 * Serializes form data into a simple string for comparison.
 */
function serializeForm(formElement) {
    const inputs = formElement.querySelectorAll('input:not([type="hidden"]), textarea');
    let data = [];
    inputs.forEach(input => {
        data.push(input.name + ':' + input.value.trim());
    });
    return data.join('|');
}

/**
 * Handles the "Cancel" logic for both Add and Edit modals.
 */
function checkCancel(modalId, formId, isEdit = false) {
    const modal = document.getElementById(modalId);
    const form = document.getElementById(formId);
    const currentData = serializeForm(form);
    
    let isDirty = false;

    if (isEdit) {
        // For Edit: compare current data with the stored original data
        const originalData = document.getElementById('original_data').value;
        if (currentData !== originalData) {
            isDirty = true;
        }
    } else {
        // For Add: Check if any content exists
        if (currentData.replace(/\|/g, '').length > (form.querySelectorAll('input:not([type="hidden"]), textarea').length * 2)) {
             isDirty = true;
        }
    }

    if (isDirty) {
        // IF cancel is clicked while the textboxes have some inputs, Display message first
        if (confirm("Are you sure you want to cancel?")) {
            modal.style.display = 'none';
            form.reset(); 
        }
    } else {
        // ELSE IF cancel is clicked while textboxes are null/unchanged, proceed to close the window
        modal.style.display = 'none';
    }
}


// --- Add Inventory Logic ---

function openAddModal() {
    document.getElementById('addInventoryForm').reset();
    document.getElementById('addInventoryModal').style.display = 'block';
}

function handleAddSubmit() {
    const form = document.getElementById('addInventoryForm');
    
    if (!form.checkValidity()) {
        alert("Please fill out all required fields.");
        return;
    }

    // Submission placeholder
    alert("Simulating submission to add_inventory.php. Data: " + serializeForm(form));
    
    document.getElementById('addInventoryModal').style.display = 'none';
    form.reset();
}


// --- Edit Inventory Logic ---

function openEditModal(rowDataJson) {
    const rowData = JSON.parse(rowDataJson);
    const form = document.getElementById('editInventoryForm');
    
    // Populate the form fields
    document.getElementById('edit_id').value = rowData.id;
    document.getElementById('edit_date').value = rowData.date_added;
    document.getElementById('edit_design_mix').value = rowData.design_mix_id;
    document.getElementById('edit_description').value = rowData.description;
    document.getElementById('edit_batch').value = rowData.batch;
    document.getElementById('edit_stocks_in').value = rowData.stocks_in == 0 ? '' : rowData.stocks_in;
    document.getElementById('edit_stocks_out').value = rowData.stocks_out == 0 ? '' : rowData.stocks_out;
    document.getElementById('edit_remarks').value = rowData.remarks;
    
    // Store the original serialized data for change detection
    document.getElementById('original_data').value = serializeForm(form);
    
    document.getElementById('editInventoryModal').style.display = 'block';
}

function handleEditDone() {
    const form = document.getElementById('editInventoryForm');
    
    if (!form.checkValidity()) {
        alert("Please fill out all required fields.");
        return;
    }
    
    // "Are you sure you want to save changes?"
    if (confirm("Are you sure you want to save changes?")) {
        // Submission placeholder
        alert("Simulating saving changes for ID: " + document.getElementById('edit_id').value);
        
        document.getElementById('editInventoryModal').style.display = 'none';
    }
}


// --- Delete Inventory Logic ---

function handleDelete(inventoryId) {
    // Step 1: "Do you want to delete this?"
    if (confirm("Do you want to delete this?")) {
        // Step 2: "You cannot retrieve this after deletion, Are you sure?"
        if (confirm("You cannot retrieve this after deletion, Are you sure?")) {
            // IF "Yes" is pressed, then delete it.
            alert("SUCCESS: Deleting inventory ID: " + inventoryId);
        } else {
            // IF "No" is pressed, cancel deletion.
            alert("Deletion cancelled by second confirmation.");
        }
    } else {
        // IF user clicked cancel (No in the first confirm), don't erase.
        alert("Deletion cancelled by first confirmation.");
    }
}
</script>
