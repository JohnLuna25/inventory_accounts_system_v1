<?php
session_start();

// Grab session variables set by login_decision.php
$invalidError = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$errorType = isset($_SESSION['error_type']) ? $_SESSION['error_type'] : '';
$nextAttemptTime = isset($_SESSION['next_attempt_time']) ? $_SESSION['next_attempt_time'] : 0;

// Clear only AFTER showing and JS fading out
if ($errorType === 'invalid') {
    unset($_SESSION['login_error'], $_SESSION['error_type']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorcom Log In</title>
    <?php include 'wl_style.php'; ?>
</head>

<body>
    <p id="clock"></p>

    <fieldset>
        <div class="title-section">
            <h1 id="title" style="font-size:50px; margin-top:10px; white-space:nowrap;"></h1>
            <h2 id="subtitle" style="font-size:30px; margin-top:10px; white-space:nowrap;"></h2>
        </div>

        <div class="login-section">
            <form action="login_decision.php" method="post">
                <?php if (!empty($invalidError)): ?>
                        <div id="errorMessage"><?php echo htmlspecialchars($invalidError); ?></div>
                <?php endif; ?>

                <label for="username">Username:</label><br><br>
                <input type="text" id="username" name="username" required autocomplete="off"><br>

                <label for="password">Password:</label><br><br>
                <input type="password" name="password" autocomplete="current-password" required><br>

                <div class="form-buttons">
                    <input type="submit" value="Log In">
                    <input type="button" value="Create Account"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/login_page/create_account.php';">
                </div>
            </form>
        </div>
    </fieldset>

    <!-- FINAL, WORKING JS BELOW -->
    <script>
    window.addEventListener("DOMContentLoaded", () => {
        const errorDiv = document.getElementById('errorMessage');
        const loginButton = document.querySelector("input[type='submit']");
        const errorType = "<?php echo $errorType; ?>";
        const nextAttempt = Number("<?php echo $nextAttemptTime; ?>");

        if (!errorDiv) return;

        // -------------------
        // WAIT STATE COUNTDOWN
        // -------------------
        if (errorType === "wait" && nextAttempt > 0) {

            if (loginButton) loginButton.disabled = true;

            const timer = setInterval(() => {
                let remaining = nextAttempt - Math.floor(Date.now() / 1000);

                if (remaining <= 0) {
                    clearInterval(timer);
                    errorDiv.style.display = "none";
                    if (loginButton) loginButton.disabled = false;

                } else {
                    errorDiv.innerText = `Please wait ${remaining} seconds before next attempt.`;
                }

            }, 1000);
        }

        // -------------------------
        // INVALID LOGIN FADE AFTER 5 SEC
        // -------------------------
        if (errorType === "invalid") {
            setTimeout(() => {
                errorDiv.style.transition = "opacity 0.5s ease";
                errorDiv.style.opacity = 0;
                setTimeout(() => { errorDiv.style.display = "none"; }, 500);
            }, 5000);
        }
    });
    </script>

    <script src="login_function.js"></script>

    <script>
        window.onload = function () {
            sorcomWriter();
            realClockTime();
            setInterval(realClockTime, 1000);
        };
    </script>

</body>

</html>
