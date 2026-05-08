<?php
session_start();

// --- Grab session variables set by login_decision.php ---
$invalidError = $_SESSION['login_error'] ?? '';
$errorType = $_SESSION['error_type'] ?? '';
$nextAttemptTime = $_SESSION['next_attempt_time'] ?? '';

// --- Clear invalid login messages after displaying ---
if (!empty($invalidError)) {
    unset($_SESSION['login_error'], $_SESSION['error_type'], $_SESSION['next_attempt_time']);
}

// --- Handle Master Override unlock ---
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
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Giga:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php include 'wl_style.php'; ?>
</head>

<body>
    <p id="clock"></p>

    <fieldset>
        <!-- Title Section -->
        <div class="title-section">
            <h1 id="title"></h1>
            <h2 id="subtitle"></h2>
        </div>

        <!-- Login Form Section -->
        <div class="login-section">
            <form action="login_decision.php" method="post">
                <?php if (!empty($invalidError)): ?>
                    <div id="errorMessage"><?php echo htmlspecialchars($invalidError); ?></div>
                <?php endif; ?>

                <label for="username">Employee ID Number:</label><br>
                <input type="text" id="username" name="username" required autocomplete="off"><br>

                <label for="password">Password:</label><br>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                    <i class="toggle-password fa-regular fa-eye-slash" id="togglePassword"></i>
                </div>

                <div class="form-buttons">
                    <input type="submit" value="Log In">
                    <input type="button" value="Create Account"
                        onclick="window.location.href='/Inventory-Accounts-System-v1/login_page/create_account.php';">
                </div>

                <!-- 2nd row: Forgot Password button -->
                <div class="forgot-password-container">
                    <a href="forgot_pass.php" class="forgot-password-btn">Forgot Password?</a>
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

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        // Default: password hidden (type="password")
        // Eye icon shows closed eye (fa-eye-slash)

        // Mouse down - show password
        togglePassword.addEventListener('mousedown', function () {
            passwordInput.setAttribute('type', 'text');
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        });

        // Mouse up - hide password
        togglePassword.addEventListener('mouseup', function () {
            passwordInput.setAttribute('type', 'password');
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        });

        // Mouse leave - hide password (in case user holds and moves mouse away)
        togglePassword.addEventListener('mouseleave', function () {
            passwordInput.setAttribute('type', 'password');
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        });

        // Prevent default browser behavior
        togglePassword.addEventListener('dragstart', function (e) {
            e.preventDefault();
        });
    </script>

    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>