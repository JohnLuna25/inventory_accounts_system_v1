// ip_functions.js

// Open modal
function openModal(id) {
  var modal = document.getElementById(id);
  if (modal) modal.style.display = "block";
}

// Close modal
function closeModal(id) {
  var modal = document.getElementById(id);
  if (modal) modal.style.display = "none";
}

// Open Edit modal and fill data
function openEditModal(row) {
  if (!row) return;

  document.getElementById("edit_id").value = row.id || "";
  document.getElementById("edit_date").value = row.date_added || "";
  document.getElementById("edit_design").value = row.design_mix_id || "";
  document.getElementById("edit_description").value = row.description || "";
  document.getElementById("edit_batch").value = row.batch || "";
  document.getElementById("edit_stocks_in").value = row.stocks_in || "";
  document.getElementById("edit_stocks_out").value = row.stocks_out || "";
  document.getElementById("edit_remarks").value = row.remarks || "";

  openModal("editInventoryModal");
}
