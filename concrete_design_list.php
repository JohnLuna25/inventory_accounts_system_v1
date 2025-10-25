<?php
// db_connect.php — make sure you have this file with $conn
include('db_connect.php');
include('Web_Functions.php');

// Fetch all rows
$result = $conn->query("SELECT * FROM concrete_designs");
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php showHeader(); ?>
<meta charset="UTF-8">
<title>Concrete Design List</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f6f6f6; }
table { border-collapse: collapse; width: 100%; background: #fff; }
th, td { border:1px solid #ddd; padding:8px; text-align:center; font-size:14px; }
th { background:#0b63c4; color: #fff; }
tr:hover { background:#f0f8ff; }
</style>
</head>
<body>
    <br><br>
<h2 style="font-size: 24px; text-align: center; top:">Concrete Design List</h2>
<br><br>
<table>
<thead>
<tr>
    <th>Materials</th>
    <th>Cement</th>
    <th>Fly Ash</th>
    <th>Water</th>
    <th>Admix</th>
    <th>Air</th>
    <th>G1</th>
    <th>3/4"</th>
    <th>3/8"</th>
    <th>FA</th>
</tr>
</thead>
<tbody>
<?php
while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>'.$row['Materials'].'</td>';
    echo '<td>'.$row['Cement'].'</td>';
    echo '<td>'.$row['Fly_ash'].'</td>';
    echo '<td>'.$row['Water'].'</td>';
    echo '<td>'.$row['Admix'].'</td>';
    echo '<td>'.$row['Air'].'</td>';
    echo '<td>'.$row['G1'].'</td>';
    echo '<td>'.$row['three_quarter'].'</td>';
    echo '<td>'.$row['three_eighth'].'</td>';
    echo '<td>'.$row['FA'].'</td>';
    echo '</tr>';
}
?>
</tbody>
</table>

</body>
</html>

<?php $conn->close(); ?>
