<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';

$error = "";

// Show error if redirected with ?error=1
if (isset($_GET['error'])) {
    $error = "Invalid username or password.";
}

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
        header("Location: /Inventory-Accounts-System-v1/home_page/home.php");
        exit();
    } else {
        // Redirect back with error to prevent persistent POST
        header("Location: ".$_SERVER['PHP_SELF']."?error=1");
        exit();
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
<body style="background-color: blue; font-family: Arial, sans-serif; margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh;">

<fieldset style="width:98%; max-width:1450px; border-radius:10px; background-color:blue; padding:30px; border:3px solid white; box-sizing:border-box; display:flex; justify-content:space-between; position:relative;">

    <!-- Titles on the LEFT -->
    <div style="text-align:left; color:yellow; padding-left:50px; flex:1; display:flex; flex-direction:column; justify-content:center;">
        <h1 style="font-size:50px; margin:0;">SORCOM READY-MIX CONCRETE</h1>
        <h2 style="font-size:30px; margin-top:10px;">Inventory and Accounts Management System</h2>
    </div>

    <!-- Login FORM on the RIGHT -->
    <div style="flex:1; display:flex; justify-content:flex-end; align-items:center; padding-right:50px;">
        <form action="" method="post" style="background-color:lightgray; padding:50px; border-radius:40px; width:340px; box-sizing:border-box; text-align:left;">
            
            <!-- Error message box -->
            <div id="errorMessage" style="height:20px; margin-bottom:10px; color:red; font-size:14px; text-align:center;">
                <?php echo $error; ?>
            </div>

            <label for="username">Username:</label><br><br>
            <input type="text" id="username" name="username" required style="width:100%; padding:10px; margin-bottom:20px; border-radius:5px; border:1px solid #ccc;"><br>

            <label for="password">Password:</label><br><br>
            <input type="password" id="password" name="password" required style="width:100%; padding:10px; margin-bottom:20px; border-radius:5px; border:1px solid #ccc;"><br>

            <input type="submit" value="Log In" style="width:100%; padding:10px; background-color:blue; color:yellow; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
        </form>
    </div>

</fieldset>

<script>
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
