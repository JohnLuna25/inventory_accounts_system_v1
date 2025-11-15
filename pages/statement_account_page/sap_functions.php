<?php
function markOrderDone($conn, $order_date, $customer_name, $delivery_date)
{
    $stmt = $conn->prepare("UPDATE statement_of_account SET order_status='done' WHERE order_date=? AND customer_name=? AND delivery_date=?");
    $stmt->bind_param("sss", $order_date, $customer_name, $delivery_date);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

function getPendingOrders($conn)
{
    $sql = "SELECT * FROM statement_of_account WHERE order_status='pending' ORDER BY order_date DESC";
    return $conn->query($sql);
}

?>