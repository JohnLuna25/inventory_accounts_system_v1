<?php
$success_msg = "";

// Handle POST-Redirect-GET pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['done_btn'])) {
    if (markOrderDone($conn, $_POST['order_date'], $_POST['customer_name'], $_POST['delivery_date'])) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1&customer=" . urlencode($_POST['customer_name']));
        exit;
    } else {
        $success_msg = "<span style='color:red;'>Error updating order.</span>";
    }
}

// Show success message only if GET parameters exist
if (isset($_GET['success']) && isset($_GET['customer'])) {
    $success_msg = "Order for <strong>" . htmlspecialchars($_GET['customer']) . "</strong> marked as done successfully!";
}

// Fetch pending orders safely
$result = null; // initialize to avoid undefined variable warning
if (isset($conn)) {
    $result = getPendingOrders($conn);
}
?>