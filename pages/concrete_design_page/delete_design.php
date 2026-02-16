<?php
// Include database connection
include __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Check for design ID
if (!isset($_GET['id'])) {
    die("No design selected.");
}

$design_id = intval($_GET['id']); // sanitize input

// Fetch the design to get the PSI value
$result = $conn->query("SELECT concrete_psi FROM sorcom_concrete_design_masterlist WHERE id = $design_id");
if (!$result || $result->num_rows === 0) {
    die("Design not found.");
}
$design = $result->fetch_assoc();
$psi = $design['concrete_psi'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Delete Concrete Design</title>
    <script>
        // First confirmation
        if (confirm("You just selected <?= addslashes($psi) ?> for deletion. Proceed?")) {
            // Second confirmation
            if (confirm("Are you sure you want to delete <?= addslashes($psi) ?> data? This will not be recovered again.")) {
                // Redirect to actually delete the design
                window.location.href = "delete_design_action.php?id=<?= $design_id ?>";
            } else {
                // Cancelled second confirmation
                window.location.href = "concrete_design_list.php";
            }
        } else {
            // Cancelled first confirmation
            window.location.href = "concrete_design_list.php";
        }
    </script>
</head>

<body>
</body>

</html>