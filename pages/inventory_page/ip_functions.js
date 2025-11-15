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