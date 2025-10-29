<?php
// Include required files
include __DIR__ . '/../FunctionAndDB/db_connect.php';
include __DIR__ . '/../FunctionAndDB/Web_Functions.php';

// Ensure connection is valid
if (!isset($conn)) {
    die("Database connection not established. Check db_connect.php.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Concrete Design List</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #BFC3AA; margin:0; }
        table { width: 90%; margin: 20px auto; border-collapse: collapse; background: #fff; text-align: center; }
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 14px; }
        th { background: #0b63c4; color: #fff; }
        tr:hover { background: #f0f8ff; }
        button { cursor: pointer; }
        #concreteModal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center; }
        #concreteModalContent { background:#fff; width:80%; max-width:800px; padding:20px; border-radius:8px; position:relative; }
        #concreteModalContent span { position:absolute; top:10px; right:20px; cursor:pointer; font-size:20px; }
    </style>
</head>
<body>

<?php showHeader(); ?>

<h2 style="font-size: 24px; text-align: center; margin-top: 65px;">Concrete Design List</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>PSI</th>
            <th>Curration Type</th>
            <th>Type</th>
            <th>Cubic Meter</th>
            <th>Slump</th>
            <th>W/C Ratio</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql = "SELECT * FROM concrete_designs ORDER BY date_created DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['concrete_psi']}</td>
                        <td>{$row['curration_type']}</td>
                        <td>{$row['concrete_type']}</td>
                        <td>{$row['cubic_meter']}</td>
                        <td>{$row['concrete_slump']}</td>
                        <td>{$row['wc_ratio']}</td>
                        <td>
                            <button type='button' onclick='showConcreteDetails({$row['id']})' 
                                style='padding:5px 10px; background:#4CAF50; color:white; border:none; border-radius:4px; margin-right:5px;'>
                                View
                            </button>
                            <a href='edit_design.php?id={$row['id']}' 
                                style='padding:5px 10px; background:#2196F3; color:white; text-decoration:none; border-radius:4px;'>
                                Edit
                            </a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='8'>No concrete designs found.</td></tr>";
        }

        $conn->close();
        ?>
    </tbody>
</table>

<!-- Modal for viewing details -->
<div id="concreteModal">
    <div id="concreteModalContent">
        <span onclick="closeModal()">✖</span>
        <div id="concreteDetails"></div>
    </div>
</div>

<script>
// Show modal and fetch details
function showConcreteDetails(id) {
    fetch('view_design_ajax.php?id=' + id)
        .then(response => response.text())
        .then(data => {
            document.getElementById('concreteDetails').innerHTML = data;
            document.getElementById('concreteModal').style.display = 'flex';
        })
        .catch(err => {
            alert('Error fetching design details.');
            console.error(err);
        });
}

// Close modal
function closeModal() {
    document.getElementById('concreteModal').style.display = 'none';
}
</script>

</body>
</html>
