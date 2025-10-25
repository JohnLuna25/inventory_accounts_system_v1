<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorcom Log In</title>
</head>

<body style="background-color: blue; text-align: center; font-family: Arial, sans-serif;">


    <fieldset style="top: 100px; width: 95%; height: 400px; margin: 0 auto; border-radius: 10px; background-color: blue; position: relative; padding: 10px;">
        <legend style="color: yellow; font-weight: bold; font-size: 24px;"></legend>
        <div style="left: 85px; position: absolute; top: 50px; text-align: center;">
            <label style="color: yellow; font-weight: bold; display: text-align: left; flex: wrap;">
                <h1 style="font-size: 50px; margin-top: 100px;">SORCOM READY-MIX CONCRETE</h1>
                <h2 style="font-size: 30px; margin-top: -10px;">Inventory and Accounts Management System</h2>
            </label>
        </div>

        <div style="right: 85px; position: absolute; top: 50px; text-align: right;">
            <form action="home.php" method="post" style="margin-top: 50px; text-align:left; display: inline-block; background-color: lightgray; padding: 50px; border-radius: 10px;">
                <label for="username">Username:</label><br>
                <input type="text" id="username" name="username" required><br><br>
                <label for="password">Password:</label><br>
                <input type="password" id="password" name="password" required><br><br>
                <input type="submit" value="Log In">
            </form>
        </div>
    </fieldset>
    

</body>

</html>