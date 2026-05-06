<?php
session_start();  // ← MUST be FIRST

// Include DB connection and functions
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Get user type for role-based access
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'regular';

// Fetch all VB data
$vb_result = $conn->query("SELECT * FROM scd_vb_list ORDER BY sort_order ASC");

// Fetch customized mix data
$customized_result = $conn->query("SELECT * FROM cdl_customized_mix_list ORDER BY created_at DESC");

// Fetch all SSD data into an array
$ssd_result = $conn->query("SELECT * FROM scd_ssd_list ORDER BY ssd_cement_mix ASC");
$ssd_rows = [];
while ($row = $ssd_result->fetch_assoc()) {
    $ssd_rows[] = $row;
}



// Check VB query
if (!$vb_result) {
    echo "VB Query Error: " . $conn->error;
}

// Check SSD query
if (!$ssd_result) {
    echo "SSD Query Error: " . $conn->error;
}

// Check Customized Mix query (ADDED)
if (!$customized_result) {
    echo "Customized Mix Query Error: " . $conn->error;
}

// Debug: show row counts (REMOVED - was showing on page)
// $vb_count = $vb_result ? $vb_result->num_rows : 0;
// $ssd_count = count($ssd_rows);
// echo "VB Rows: $vb_count, SSD Rows: $ssd_count";


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Design Masterlist</title>

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
    <link rel="stylesheet" href="cdl_style.css">


</head>

<body>
    <?php showHeader() ?>
    <div class="main-content">

        <div class="page-title-bar">
            <h2 class="text-center mb-3" style="font-family: 'Poppins', sans-serif;">
                Concrete Design Masterlist
            </h2>
            <div class="search-container">
                <input type="text" id="vbSearchInput" placeholder="   Search PSI, Days, Type...">
                <button onclick="filterVBTable()" class="btn btn-primary" style="padding: 10px 20px;">Search</button>
            </div>
        </div>

        <div class="table-container">
            <table id="vbTable">
                <thead>
                    <tr class="list-row">
                        <th>Concrete PSI</th>
                        <th>Curation Days</th>
                        <th>Concrete Type</th>
                        <th>Total ( kgs/m³ )</th>
                        <th>Concrete Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $row_index = 0;
                    while ($vb_row = $vb_result->fetch_assoc()) {
                        $ssd_row = $ssd_rows[$row_index] ?? []; // use corresponding SSD row if exists
                        $row_index++;

                        echo "<tr>";
                        echo "<td>" . ($vb_row['vb_concrete_psi'] ?? '') . "</td>";

                        // Extract only the number from curation_days
                        $curation = preg_replace('/[^0-9]/', '', $vb_row['vb_curation_days'] ?? '0');
                        echo "<td>" . $curation . "</td>";

                        echo "<td>" . ($vb_row['vb_concrete_type'] ?? '') . "</td>";

                        // Extract numeric value from string (remove units like "kgs/m³")
                        $total_value = preg_replace('/[^0-9.]/', '', $vb_row['vb_total'] ?? '0');
                        echo "<td>" . number_format((float) $total_value, 1, '.', ',') . "</td>";

                        echo "<td>" . ($vb_row['vb_concrete_price'] ?? '') . "</td>";
                        echo "<td>";
                        echo "<a href='#' class='btn-view' onclick='toggleDropbar($row_index); return false;'>View</a>";

                        // Check user type from session
                        $user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'regular';

                        // Only show Edit button for master users (Plant Managers)
                        if ($user_type === 'master') {
                            echo "<a href='edit_design.php?id=" . $vb_row['vb_id'] . "' class=\"btn-edit\">Edit</a>";
                        }

                        echo "</td>";
                        echo "</tr>";

                        // Dropbar row
                        echo "<tr id='dropbar-$row_index' class='dropbar-row'>";
                        echo "<td colspan='6'>";
                        echo "<table class='dropbar-inner'>";
                        echo "<tr><td></td><td class='ssd-col'>Saturated Surface Dry (SSD)</td><td class='vb-col'>Volume Batch</td></tr>";




                        // Fields to merge (SSD and VB in one cell)
                        $merged_fields = [
                            "Cubic Meter" => "cubic_meter",
                            "Cement Source" => "cement_source",
                            "Concrete Slump" => "concrete_slump",
                            "Admix Source" => "admix_source"
                        ];

                        // Fields that remain separate (SSD left, VB right)
                        $separate_fields = [
                            "Cement Mix" => "cement_mix",
                            "Fly Ash" => "fly_ash",
                            "3/4\"" => "three_fourth",
                            "3/8\"" => "three_eight",
                            "Water" => "water",
                            "Sand" => "sand",
                            "G1" => "G1",
                            "Air" => "air",
                            "W/C Ratio" => "wc_ratio",
                            "Admix" => "admix",
                            "Others" => "others"
                        ];

                        // Render merged fields first
                        foreach ($merged_fields as $label => $col) {
                            $ssd_value = $ssd_row['ssd_' . $col] ?? '';
                            if ($ssd_value == 0)
                                $ssd_value = '';
                            $vb_value = $vb_row['vb_' . $col] ?? '';
                            if ($vb_value == 0)
                                $vb_value = '';

                            // Combine both values
                            $combined = trim($ssd_value . ' ' . $vb_value);

                            echo "<tr><td>$label</td><td colspan='2' class='merged-cell'>$combined</td></tr>";

                        }

                        // Render separate fields
                        foreach ($separate_fields as $label => $col) {
                            $ssd_value = $ssd_row['ssd_' . $col] ?? '';
                            if ($ssd_value == 0)
                                $ssd_value = '';
                            $vb_value = $vb_row['vb_' . $col] ?? '';
                            if ($vb_value == 0)
                                $vb_value = '';
                            echo "<tr><td>$label</td><td class='ssd-data'>$ssd_value</td><td class='vb-data'>$vb_value</td></tr>";
                        }
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- ========== CUSTOMIZED CONCRETE DESIGN SECTION ========== -->
    <div class="main-content">
        <div class="page-title-bar">
            <h2 class="text-center mb-3 style=font-family: 'Poppins', sans-serif;">Customized Concrete Design</h2>
            <div class="search-container">
                <a href="cdl_customizedMix.php" class="btn"
                    style="background: #2e3590; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px;">+
                    Add Customized Mix</a>
                <input type="text" id="customizedSearchInput" placeholder="Search Client, PSI, Remarks...">
                <button onclick="filterCustomizedTable()" class="btn btn-primary"
                    style="padding: 10px 20px;">Search</button>
            </div>
        </div>

        <!-- Customized Mix Table -->
        <div class="table-container">
            <table id="customizedTable">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Concrete PSI</th>
                        <th>Curation Days</th>
                        <th>Concrete Type</th>
                        <th>Remarks</th>
                        <th>Is Active</th>
                        <th>Created At</th>
                        <th>Concrete Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 👈 NEW: Separate index for CUSTOMIZED table only
                    $customized_row_index = 0;
                    while ($customized_row = $customized_result->fetch_assoc()) {
                        $customized_row_index++;  // 👈 Use custom index
                    
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($customized_row['cdl_client_name'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($customized_row['cdl_vb_concrete_psi'] ?? '') . "</td>";

                        // Extract only the number from curation_days
                        $curation = preg_replace('/[^0-9]/', '', $customized_row['cdl_vb_curation_days'] ?? '0');
                        echo "<td>" . $curation . "</td>";

                        echo "<td>" . htmlspecialchars($customized_row['cdl_vb_concrete_type'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($customized_row['cdl_experiment_notes'] ?? '') . "</td>";
                        echo "<td>" . ($customized_row['cdl_is_active'] == 1 ? 'Active' : 'Inactive') . "</td>";
                        echo "<td>" . date('M j, Y', strtotime($customized_row['created_at'] ?? '')) . "</td>";
                        echo "<td>" . htmlspecialchars($customized_row['cdl_concrete_price'] ?? '') . "</td>";
                        echo "<td>";
                        echo "<a href='#' class='btn-view' onclick='toggleCustomizedDropbar($customized_row_index); return false;'>View</a>";

                        // Only show Edit and Delete buttons for master users (Plant Managers)
                        if ($user_type === 'master') {
                            echo "<a href='edit_customized_mix.php?id=" . $customized_row['id'] . "' class='btn-edit'>Edit</a>";
                            echo "<a href='delete_customized_mix.php?id=" . $customized_row['id'] . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this customized mix?\");'>Delete</a>";
                        }

                        echo "</td>";
                        echo "</tr>";

                        // 👈 NEW: Use custom ID prefix
                        echo "<tr id='customized-dropbar-$customized_row_index' class='dropbar-row'>";
                        echo "<td colspan='9'>";
                        echo "<table class='dropbar-inner'>";
                        echo "<tr><td></td><td class='ssd-col'>Saturated Surface Dry (SSD)</td><td class='vb-col'>Volume Batch</td></tr>";

                        // Fields to merge (SSD and VB in one cell)
                        $merged_fields = [
                            "Cubic Meter" => "cubic_meter",
                            "Cement Source" => "cement_source",
                            "Concrete Slump" => "concrete_slump",
                            "Admix Source" => "admix_source"
                        ];

                        // Fields that remain separate (SSD left, VB right)
                        $separate_fields = [
                            "Cement Mix" => "cement_mix",
                            "Fly Ash" => "fly_ash",
                            "3/4\"" => "three_fourth",
                            "3/8\"" => "three_eight",
                            "Water" => "water",
                            "Sand" => "sand",
                            "G1" => "G1",
                            "Air" => "air",
                            "W/C Ratio" => "wc_ratio",
                            "Admix" => "admix",
                            "Others" => "others"
                        ];

                        // Render merged fields first
                        foreach ($merged_fields as $label => $col) {
                            $ssd_value = $customized_row['cdl_ssd_' . $col] ?? '';
                            if ($ssd_value == 0)
                                $ssd_value = '';
                            $vb_value = $customized_row['cdl_vb_' . $col] ?? '';
                            if ($vb_value == 0)
                                $vb_value = '';
                            $combined = trim($ssd_value . ' ' . $vb_value);
                            echo "<tr><td>$label</td><td colspan='2' class='merged-cell'>$combined</td></tr>";
                        }

                        // Render separate fields
                        foreach ($separate_fields as $label => $col) {
                            $ssd_value = $customized_row['cdl_ssd_' . $col] ?? '';
                            if ($ssd_value == 0)
                                $ssd_value = '';
                            $vb_value = $customized_row['cdl_vb_' . $col] ?? '';
                            if ($vb_value == 0)
                                $vb_value = '';
                            echo "<tr><td>$label</td><td class='ssd-data'>$ssd_value</td><td class='vb-data'>$vb_value</td></tr>";
                        }
                        echo "</table>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // 🔥 DEBOUNCED REAL-TIME SEARCH FOR VB TABLE
        let vbSearchTimeout;
        const vbSearchInput = document.getElementById("vbSearchInput");

        vbSearchInput.addEventListener("input", function () {
            clearTimeout(vbSearchTimeout);
            vbSearchTimeout = setTimeout(filterVBTable, 300);

            if (!this.value.trim()) {
                showAllVBTable();
            }
        });

        vbSearchInput.addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                clearTimeout(vbSearchTimeout);
                filterVBTable();
            }
        });

        // 🔥 DEBOUNCED REAL-TIME SEARCH FOR CUSTOMIZED TABLE
        let customizedSearchTimeout;
        const customizedSearchInput = document.getElementById("customizedSearchInput");

        customizedSearchInput.addEventListener("input", function () {
            clearTimeout(customizedSearchTimeout);
            customizedSearchTimeout = setTimeout(filterCustomizedTable, 300);

            if (!this.value.trim()) {
                showAllCustomizedTable();
            }
        });

        customizedSearchInput.addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                clearTimeout(customizedSearchTimeout);
                filterCustomizedTable();
            }
        });

        function filterVBTable() {
            const input = vbSearchInput;
            const filter = input.value.toLowerCase().trim();
            const table = document.querySelector(".table-container table"); // First table
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const tds = tr[i].getElementsByTagName("td");
                let found = false;

                // Skip dropbar rows
                if (tds.length === 1 && tds[0].colSpan === 6) {
                    tr[i].style.display = "none";
                    continue;
                }

                for (let j = 0; j < tds.length; j++) {
                    const txtValue = tds[j].textContent || tds[j].innerText;
                    if (txtValue.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        }

        function filterCustomizedTable() {
            const input = customizedSearchInput;
            const filter = input.value.toLowerCase().trim();
            const table = document.getElementById("customizedTable"); // ← FIXED
            if (!table) return;

            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                const tds = tr[i].getElementsByTagName("td");
                let found = false;

                // Skip dropbar rows
                if (tds.length === 1 && tds[0].colSpan === 9) {
                    tr[i].style.display = "none";
                    continue;
                }

                for (let j = 0; j < tds.length; j++) {
                    const txtValue = tds[j].textContent || tds[j].innerText;
                    if (txtValue.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }
                tr[i].style.display = found ? "" : "none";
            }
        }

        function showAllVBTable() {
            const table = document.querySelector(".table-container table");
            const tr = table.getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                tr[i].style.display = "";
            }
        }

        function showAllCustomizedTable() {
            const table = document.getElementById("customizedTable"); // ← FIXED
            if (!table) return;

            const tr = table.getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                tr[i].style.display = "";
            }
        }

        // Original toggle functions
        function toggleDropbar(index) {
            var row = document.getElementById('dropbar-' + index);
            if (!row) return;
            row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
        }

        function toggleCustomizedDropbar(index) {
            var row = document.getElementById('customized-dropbar-' + index);
            if (!row) return;
            row.style.display = (row.style.display === 'table-row') ? 'none' : 'table-row';
        }
    </script>


</body>

</html>