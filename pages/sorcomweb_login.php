<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $hashed_password = hash('sha256', $password);

    $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['username'] = $username;
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sorcom Log In</title>
</head>
<body style="background-color: blue; text-align: center; font-family: Arial, sans-serif; margin:0; padding:0;">

<fieldset style="position: fixed; top: 100px; width: 95%; height: 400px; margin: 0 auto; border-radius: 10px; background-color: blue; padding: 10px;">
    <legend style="color: yellow; font-weight: bold; font-size: 24px;"></legend>

    <div style="left: 85px; position: absolute; top: 50px; text-align: center;">
        <h1 style="font-size: 50px; margin-top: 100px; color: yellow;">SORCOM READY-MIX CONCRETE</h1>
        <h2 style="font-size: 30px; margin-top: -10px; color: yellow;">Inventory and Accounts Management System</h2>
    </div>

    <div style="right: 85px; position: absolute; top: 30px; text-align: right;">
        <form action="" method="post" style="margin-top: 50px; text-align:left; display: inline-block; background-color: lightgray; padding: 50px; border-radius: 40px; width: 300px; box-sizing: border-box; position: relative;">
            
            <!-- Error message box -->
            <div id="errorMessage" style="height: 20px; margin-bottom: 10px; color: red; font-size: 14px;">
                <?php echo $error; ?>
            </div>

            <label for="username">Username:</label><br><br>
            <input type="text" id="username" name="username" required><br><br>
            <label for="password">Password:</label><br><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" value="Log In">
        </form>
    </div>
</fieldset>

<script>
    // Hide error message automatically after 5 seconds
    window.onload = function() {
        var errorDiv = document.getElementById('errorMessage');
        if (errorDiv.textContent.trim() !== "") {
            setTimeout(function() {
                errorDiv.style.display = "none";
            }, 5000); // 5 seconds
        }
    };
</script>

</body>
</html>
