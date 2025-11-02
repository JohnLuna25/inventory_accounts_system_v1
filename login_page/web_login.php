<?php
session_start();
require_once '../FunctionAndDB/db_connect.php';
require_once '../FunctionAndDB/Web_Functions.php';

$invalidError = ""; // default empty

// If there’s a login error in session, copy it and then clear it
if (isset($_SESSION['login_error'])) {
    $invalidError = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $hashed_password = hash('sha256', $password);

    $stmt = $conn->prepare("SELECT * FROM sorcom_users WHERE employee_number = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login success
        $user = $result->fetch_assoc();

        // Store session info
        $_SESSION['username'] = $user['employee_number'];
        $_SESSION['employee_name'] = $user['employee_name']; // for sidebar "Welcome, John"

        // Redirect to homepage
        header("Location: /Inventory-Accounts-System-v1/home_page/home.php");
        exit();
    } else {
        // Login failed
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: " . $_SERVER['PHP_SELF']);
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

<body
    style="background-color: blue; font-family: Arial, sans-serif; margin:0; padding:0; display:flex; justify-content:center; align-items:center; height:100vh;">

    <p id="clock"
        style="font-size: 18px; font-weight: 500; color: yellow; position: absolute; top: 40px; left: 30px; margin: 0;">
        <!-- JS will fill this -->
    </p>

    <fieldset
        style="width:98%; max-width:1450px; border-radius:10px; background-color:blue; padding:30px; border:3px solid white; box-sizing:border-box; display:flex; justify-content:space-between; position:relative;">

        <!-- Titles on the LEFT -->
        <div
            style="text-align:left; color:yellow; padding-left:50px; flex:1; display:flex; flex-direction:column; justify-content:center;">
            <h1 id="title" style="font-size:50px; margin-top:10px; color:yellow; white-space:nowrap;"></h1>
            <h2 id="subtitle"
                style="font-size:30px; margin-top:10px; color:yellow; text-align:left; white-space:nowrap;"></h2>
        </div>

        <!-- Login FORM on the RIGHT -->
        <div style="flex:1; display:flex; justify-content:flex-end; align-items:center; padding-right:50px;">
            <form action="" method="post"
                style="background-color:lightgray; padding:55px; border-radius:40px; width:340px; box-sizing:border-box; text-align:left;">

                <?php if (!empty($invalidError)): ?>
                    <div id="errorMessage"
                        style="height:20px; margin-bottom:10px; color:red; font-size:14px; text-align:center;">
                        <?php echo htmlspecialchars($invalidError); ?>
                    </div>
                <?php endif; ?>

                <label for="username">Username:</label><br><br>
                <input type="text" id="username" name="username" required autocomplete="off"
                    style="width:100%; padding:10px; margin-bottom:20px; border-radius:5px; border:1px solid #ccc;"><br>

                <label for="password">Password:</label><br><br>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    style="width:100%; padding:10px; margin-bottom:20px; border-radius:5px; border:1px solid #ccc;"><br>

                <div style="display: flex; gap: 10px;">
                    <input type="submit" value="Log In" autocomplete="off"
                        style="flex: 1; padding:10px; background-color:blue; color:yellow; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">

                    <input type="button" value="Create Account" autocomplete="off"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/login_page/create_account.php';"
                        style="flex: 1; padding:10px; background-color:blue; color:yellow; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
                </div>


            </form>
        </div>

    </fieldset>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var errorDiv = document.getElementById('errorMessage');
            if (errorDiv && errorDiv.innerText.trim() !== "") {
                setTimeout(() => {
                    errorDiv.style.transition = "opacity 0.5s ease";
                    errorDiv.style.opacity = "0";
                    setTimeout(() => errorDiv.style.display = "none", 500);
                }, 5000);
            }
        });
    </script>

</body>

</html>


<script>

    var iSorcom = 0;
    var iIAMS = 0;
    var txtSorcom = 'SORCOM READY-MIX CONCRETE';
    var txtIAMS = 'Inventory and Accounts Management System';
    var speedTitle = 30;
    var speedSubtitle = 20;

    function sorcomWriter() {
        if (iSorcom < txtSorcom.length) {
            document.getElementById("title").innerHTML += txtSorcom.charAt(iSorcom);
            iSorcom++;
            setTimeout(sorcomWriter, speedTitle);
        } else {
            setTimeout(iamsWriter, 500);
        }
    }

    function iamsWriter() {
        if (iIAMS < txtIAMS.length) {
            document.getElementById("subtitle").innerHTML += txtIAMS.charAt(iIAMS);
            iIAMS++;
            setTimeout(iamsWriter, speedSubtitle);
        }
    }
    window.onload = sorcomWriter();

    function realClockTime() {
        const now = new Date();

        // Format date as "MMM. dd, YYYY HH:mm:ss"
        const options = { month: 'short', day: '2-digit', year: 'numeric' };
        const dateStr = now.toLocaleDateString('en-US', options).replace(',', '.');

        let hours = now.getHours().toString().padStart(2, '0');
        let minutes = now.getMinutes().toString().padStart(2, '0');
        let seconds = now.getSeconds().toString().padStart(2, '0');

        document.getElementById('clock').textContent = `${dateStr} || ${hours}:${minutes}:${seconds}`;
    }

    // Update every second
    setInterval(realClockTime, 1000);
    realClockTime(); // initial call
</script>