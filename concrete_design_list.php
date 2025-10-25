<?php include('db_connect.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Concrete Design List</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      margin: 0;
      padding: 20px;
      background-color: #f0f0f0;
    }
    table {
      margin: 20px auto;
      border-collapse: collapse;
      width: 80%;
      background-color: white;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
    }
    th {
      background-color: blue;
      color: yellow;
    }
    tr:hover {
      background-color: #eef;
    }
  </style>
</head>
<body>

  <h2>Concrete Design List</h2>

  <table>
    <tr>
      <th>ID</th>
      <th>Design Name</th>
      <th>Strength</th>
      <th>Date Created</th>
    </tr>

    <?php
      $sql = "SELECT * FROM concrete_designs";
      $result = $conn->query($sql);

      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          echo "<tr>
                  <td>".$row["id"]."</td>
                  <td>".$row["design_name"]."</td>
                  <td>".$row["strength"]."</td>
                  <td>".$row["date_created"]."</td>
                </tr>";
        }
      } else {
        echo "<tr><td colspan='4'>No records found</td></tr>";
      }

      $conn->close();
    ?>
  </table>

</body>
</html>
