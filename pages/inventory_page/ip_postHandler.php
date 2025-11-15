<?php

include __DIR__ . '/ip_prepareFields.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Get all fields in one go
    extract(prepareInventoryFields($_POST));

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO sorcom_readymix_inventory 
                (date_added, design_mix_id, description, batch, stocks_in, stocks_out, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssdds",
            $_POST['date_added'],
            $_POST['design_mix'],
            $description,
            $batch,
            $stocks_in,
            $stocks_out,
            $remarks
        );
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE sorcom_readymix_inventory 
                SET date_added=?, design_mix_id=?, description=?, batch=?, stocks_in=?, stocks_out=?, remarks=? 
                WHERE id=?");
        $stmt->bind_param(
            "ssssddsi",
            $_POST['date_added'],
            $_POST['design_mix'],
            $description,
            $batch,
            $stocks_in,
            $stocks_out,
            $remarks,
            $_POST['id']
        );
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM sorcom_readymix_inventory WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>