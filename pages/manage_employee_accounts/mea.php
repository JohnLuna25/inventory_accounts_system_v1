<?php
include __DIR__ . '/../../FunctionAndDB/db_connect.php';
include __DIR__ . '/../../FunctionAndDB/Web_Functions.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sorcom_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status update
$statusUpdated = false;
if (isset($_POST['update_status'])) {
    $employee_number = $_POST['employee_number'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE sorcom_users SET status = ? WHERE employee_number = ?");
    $stmt->bind_param("ss", $new_status, $employee_number);
    $stmt->execute();
    $stmt->close();

    $statusUpdated = true; // flag to show message
}

// Fetch employees
$sql = "SELECT employee_number, employee_name, status FROM sorcom_users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees</title>
    <link rel="stylesheet" href="mea.css">
    <style>
        /* inline CSS for demo, can remove if in mea.css */
        .status-message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-top: 20px;
            transition: opacity 1s ease;
            /* smooth fade */
        }
    </style>
</head>

<body style="background-color: white;">
    <?php showHeader(); ?>

    <div class="table-container">
        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr>
            <th>Employee Number</th>
            <th>Employee Name</th>
            <th>Current Status</th>
            <th>Set Status</th>
            <th>Action</th>
          </tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<form method='POST'>";
                echo "<td>" . $row['employee_number'] . "</td>";
                echo "<td>" . $row['employee_name'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>
                <input type='hidden' name='employee_number' value='" . $row['employee_number'] . "'>
                <select name='status'>
                    <option value='Active' " . ($row['status'] == 'Active' ? 'selected' : '') . ">Active</option>
                    <option value='Inactive' " . ($row['status'] == 'Inactive' ? 'selected' : '') . ">Inactive</option>
                    <option value='Retired' " . ($row['status'] == 'Retired' ? 'selected' : '') . ">Retired</option>
                    <option value='Suspended' " . ($row['status'] == 'Suspended' ? 'selected' : '') . ">Suspended</option>
                    <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                </select>
              </td>";
                echo "<td><input type='submit' name='update_status' value='Update'></td>";
                echo "</form>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "<p class='no-employees'>No employees found.</p>";
        }
        $conn->close();
        ?>
    </div>

</body>

</html>