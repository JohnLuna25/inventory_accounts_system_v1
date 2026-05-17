<?php
ob_start();
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';
require_once __DIR__ . '/../../FunctionAndDB/Web_Functions.php';
//include_once __DIR__ . '/ip_postHandler.php';

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $edit_id = intval($_POST['update_id']);
    $date_added = trim($_POST['date_added'] ?? date('Y-m-d'));
    $design_mix = trim($_POST['design_mix'] ?? '');
    $curation_days = intval($_POST['curation_days'] ?? 0);
    $concrete_type = trim($_POST['concrete_type'] ?? '');
    $stocks_in = intval($_POST['stocks_in'] ?? 0);
    $stocks_out = intval($_POST['stocks_out'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($date_added) || empty($design_mix) || empty($concrete_type)) {
        $error_message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("UPDATE scd_vb_list 
            SET date_added = ?, design_mix_id = ?, curation_days = ?, concrete_type = ?, stocks_in = ?, stocks_out = ?, remarks = ? 
            WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("sssiiisi", $date_added, $design_mix, $curation_days, $concrete_type, $stocks_in, $stocks_out, $remarks, $edit_id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: inventory_management.php");
                exit;
            } else {
                $error_message = "Error updating inventory: " . $stmt->error;
            }
        } else {
            $error_message = "Error preparing update statement: " . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>

    <!-- Bootstrap CSS -->
    <link href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Lexend+Giga:wght@400;500;600;700&family=Nunito:wght@400;500;600;700&family=Iosevka+Charon+Mono&family=Lexend+Mega:wght@400;500;600;700&family=Lexend+Meta:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="inventory_management.css">

    <?php include '../../assets/resources.php'; ?>

</head>

<body>
    <?php showHeader(); ?>
    <div class="main-content">
        <div class="page-title-bar">
            <h2 class="text-center mb-3" style="font-family: 'Poppins', sans-serif; font-size: 32px; margin-top: auto;">
                Ready-Mix
                Inventory</h2>
            <div class="search-container">
                <a href="ip_form.php" class="btn">+ Add Inventory</a>
                <a href="mixture_inventory.php" class="btn">Open Mixture Inventory</a>
            </div>
        </div>

        <?php if ($error_message): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php include __DIR__ . '/ip_tableDisplay.php'; ?>
    </div>
</body>

</html>