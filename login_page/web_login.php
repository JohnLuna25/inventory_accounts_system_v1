<?php
session_start();

// --- Grab session variables set by login_decision.php ---
$invalidError = $_SESSION['login_error'] ?? '';
$errorType = $_SESSION['error_type'] ?? '';
$nextAttemptTime = $_SESSION['next_attempt_time'] ?? 0;

// --- Clear invalid login messages after displaying ---
// Only clear if type is 'invalid'; wait/lock states need to persist
if ($errorType === 'invalid') {
    unset($_SESSION['login_error'], $_SESSION['error_type']);
}

// --- Handle Master Override unlock ---
// If a master override unlocked this user, clear previous failed attempts & locks
if (isset($_SESSION['override_employee_id'])) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
    unset($_SESSION['locked_employee_id'], $_SESSION['error_type'], $_SESSION['login_error']);
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

                <label for="username">Employee ID Number:</label><br>
                <input type="text" id="username" name="username" required autocomplete="off"><br>

                <label for="password">Password:</label><br>
                <input type="password" name="password" autocomplete="current-password" required><br>

                <div class="form-buttons">
                    <input type="submit" value="Log In">
                    <input type="button" value="Create Account"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/login_page/create_account.php';">
                </div>
            </form>
        </div>
    </fieldset>

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
            if ((errorType === "wait" || errorType === "lock") && nextAttempt > 0) {

                if (loginButton) loginButton.disabled = true;

                const timer = setInterval(() => {
                    let remaining = nextAttempt - Math.floor(Date.now() / 1000);

                    if (remaining <= 0) {
                        clearInterval(timer);
                        errorDiv.style.display = "none";
                        if (loginButton) loginButton.disabled = false;
                    } else {
                        if (errorType === "wait") {
                            errorDiv.innerText = `Please wait ${remaining} seconds before next attempt.`;
                        } else if (errorType === "lock") {
                            errorDiv.innerText = "Account locked. Master Override required.";
                        }
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