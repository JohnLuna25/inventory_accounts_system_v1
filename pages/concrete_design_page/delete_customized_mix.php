<?php
include __DIR__ . '/../../FunctionAndDB/db_connect.php';

$mix_id = intval($_GET['id'] ?? 0);

if ($mix_id <= 0) {
    die("Invalid customized mix ID.");
}

// Optional: check if exists first
$check = $conn->query("SELECT id FROM cdl_customized_mix_list WHERE id = $mix_id");

if (!$check || $check->num_rows == 0) {
    die("Customized mix not found.");
}

// DELETE QUERY
$delete = "DELETE FROM cdl_customized_mix_list WHERE id = $mix_id";

if ($conn->query($delete)) {
    echo "<script>
        alert('Customized Mix deleted successfully!');
        window.location.href='concrete_design_list.php';
    </script>";
} else {
    echo "<script>
        alert('Error deleting record: {$conn->error}');
        window.history.back();
    </script>";
}

$conn->close();
?>