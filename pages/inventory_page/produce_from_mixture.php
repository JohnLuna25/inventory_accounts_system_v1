<?php
require_once __DIR__ . '/../../FunctionAndDB/db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mixture_inventory.php");
    exit;
}

// Get values from form
$mixture_id = intval($_POST['mixture_id'] ?? 0);
$design_psi = trim($_POST['design_psi'] ?? '');
$design_days = trim($_POST['design_days'] ?? '');  // Changed from intval to trim
$design_type = trim($_POST['design_type'] ?? '');
$design_batch = floatval($_POST['design_batch'] ?? 0);

// Validate inputs
if ($mixture_id <= 0 || empty($design_psi) || empty($design_days) || empty($design_type)) {
    die("Invalid input parameters.");
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get the design details from masterlist
    $stmt = $conn->prepare("SELECT * FROM scd_vb_list 
                       WHERE vb_concrete_psi = ? AND vb_curation_days = ? AND vb_concrete_type = ?");
    $stmt->bind_param("sss", $design_psi, $design_days, $design_type);
    $stmt->execute();
    $design_result = $stmt->get_result();
    $design = $design_result->fetch_assoc();
    $stmt->close();

    if (!$design) {
        throw new Exception("Design not found in masterlist.");
    }

    // 2. Get the current mixture inventory stock
    $stmt = $conn->prepare("SELECT * FROM sorcom_mixture_inventory WHERE id = ? FOR UPDATE");
    $stmt->bind_param("i", $mixture_id);
    $stmt->execute();
    $mixture_result = $stmt->get_result();
    $mixture = $mixture_result->fetch_assoc();
    $stmt->close();

    if (!$mixture) {
        throw new Exception("Mixture inventory record not found.");
    }

    // 3. Check if sufficient stock exists (double-check)
    $required = [
        'cement' => floatval($design['vb_cement_mix'] ?? 0),
        'fly_ash' => floatval($design['vb_fly_ash'] ?? 0),
        'sand' => floatval($design['vb_sand'] ?? 0),
        '3_4' => floatval($design['vb_three_fourth'] ?? 0),
        '3_8' => floatval($design['vb_three_eight'] ?? 0),
        'g1' => floatval($design['vb_G1'] ?? 0),
        'admix' => floatval($design['vb_admix'] ?? 0),
        'water' => floatval($design['vb_water'] ?? 0),
        'air' => floatval($design['vb_air'] ?? 0)
    ];

    $available = [
        'cement' => floatval($mixture['cement']),
        'fly_ash' => floatval($mixture['fly_ash']),
        'sand' => floatval($mixture['sand']),
        '3_4' => floatval($mixture['3_4']),
        '3_8' => floatval($mixture['3_8']),
        'g1' => floatval($mixture['g1']),
        'admix' => floatval($mixture['admix']),
        'water' => floatval($mixture['water']),
        'air' => floatval($mixture['air'])
    ];

    // Verify all materials are sufficient
    foreach ($required as $key => $value) {
        if ($available[$key] < $value) {
            throw new Exception("Insufficient stock for {$key}. Required: {$value}, Available: {$available[$key]}");
        }
    }

    // 4. Deduct materials from mixture inventory
    $new_cement = $available['cement'] - $required['cement'];
    $new_fly_ash = $available['fly_ash'] - $required['fly_ash'];
    $new_sand = $available['sand'] - $required['sand'];
    $new_3_4 = $available['3_4'] - $required['3_4'];
    $new_3_8 = $available['3_8'] - $required['3_8'];
    $new_g1 = $available['g1'] - $required['g1'];
    $new_admix = $available['admix'] - $required['admix'];
    $new_water = $available['water'] - $required['water'];
    $new_air = $available['air'] - $required['air'];
    $new_total = $new_cement + $new_fly_ash + $new_sand + $new_3_4 + $new_3_8 + $new_g1 + $new_admix + $new_water + $new_air + floatval($mixture['others']);

    $stmt = $conn->prepare("UPDATE sorcom_mixture_inventory 
                           SET cement = ?, fly_ash = ?, sand = ?, `3_4` = ?, `3_8` = ?, 
                               g1 = ?, admix = ?, water = ?, air = ?, total = ? 
                           WHERE id = ?");
    $stmt->bind_param(
        "ddddddddddi",
        $new_cement,
        $new_fly_ash,
        $new_sand,
        $new_3_4,
        $new_3_8,
        $new_g1,
        $new_admix,
        $new_water,
        $new_air,
        $new_total,
        $mixture_id
    );
    $stmt->execute();
    $stmt->close();

    // DEBUG: Check all values before INSERT
    echo "<pre>";
    echo "prefill_mixture_id: $prefill_mixture_id\n";
    echo "design_mix: $design_mix\n";
    echo "curation_days: $curation_days\n";
    echo "concrete_type: $concrete_type\n";
    echo "batch: $batch\n";
    echo "</pre>";
    exit;

    // 5. Insert into ready-mix inventory
    $today = date('Y-m-d');
    $stocks_in = 1;
    $stocks_out = 0;
    $remarks = "Produced from Mixture Inventory";

    $stmt = $conn->prepare("INSERT INTO sorcom_readymix_inventory 
                       (date_added, design_mix_id, curation_days, concrete_type, batch, stocks_in, stocks_out, remarks) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssdssi", $today, $design_psi, $design_days, $design_type, $design_batch, $stocks_in, $stocks_out, $remarks);  // Changed "ssissdis" to "sssssdssi"
    $stmt->execute();
    $stmt->close();

    // 6. Commit transaction
    $conn->commit();

    // Redirect back to mixture inventory with success message
    header("Location: mixture_inventory.php?success=1");
    exit;

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();

    // Redirect with error message
    header("Location: mixture_inventory.php?error=" . urlencode($e->getMessage()));
    exit;
}

$conn->close();
?>