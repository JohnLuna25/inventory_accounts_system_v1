<?php
session_start();

// --- Grab session variables set by login_decision.php ---
$invalidError = $_SESSION['login_error'] ?? '';
$errorType = $_SESSION['error_type'] ?? '';
$nextAttemptTime = $_SESSION['next_attempt_time'] ?? '';

// --- Clear invalid login messages after displaying ---
if ($errorType === 'invalid') {
    unset($_SESSION['login_error'], $_SESSION['error_type']);
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
    <style>
        /* KEEP ORIGINAL COLORS FROM wl_style.php */
        body {
            background-color: blue;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #clock {
            font-size: 18px;
            font-weight: 500;
            color: yellow;
            position: absolute;
            top: 40px;
            left: 30px;
            margin: 0;
        }

        fieldset {
            width: 98%;
            max-width: 1450px;
            border-radius: 10px;
            background-color: blue;
            padding: 30px;
            border: 3px solid white;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .title-section {
            text-align: left;
            color: yellow;
            padding-left: 50px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .title-section h1 {
            font-size: 50px;
            margin-top: 10px;
            white-space: nowrap;
        }

        .title-section h2 {
            font-size: 30px;
            margin-top: 10px;
            white-space: nowrap;
        }

        .login-section {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-right: 50px;
        }

        form {
            background-color: lightgray;
            padding: 55px;
            border-radius: 40px;
            width: 340px;
            box-sizing: border-box;
            text-align: left;
        }

        input[type="text"],
        input[type="password"] {
            width: 91%;
            padding: 10px;
            margin-bottom: 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        input[type="submit"],
        input[type="button"] {
            flex: 1;
            padding: 10px;
            background-color: blue;
            color: yellow;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
        }

        #errorMessage {
            height: 20px;
            margin-bottom: 10px;
            color: red;
            font-size: 14px;
            text-align: center;
        }

        /* ========== RESPONSIVE STYLES ========== */

        /* Tablet */
        @media (max-width: 992px) {
            .title-section h1 {
                font-size: 40px;
            }

            .title-section h2 {
                font-size: 24px;
            }

            form {
                padding: 40px;
                width: 300px;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            body {
                align-items: flex-start !important;
                padding-top: 20px !important;
            }

            #clock {
                position: fixed !important;
                text-align: left !important;
            }

            fieldset {
                flex-direction: column !important;
                padding: 20px !important;
                border: none !important;
                background: transparent !important;
                width: 100% !important;
                max-width: 100% !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .title-section {
                position: relative !important;
                text-align: center !important;
                margin-top: 100px !important;
                margin-bottom: 30px !important;
                max-width: 100% !important;
                margin-left: auto !important;
                margin-right: auto !important;
                width: 100% !important;
                display: block !important;
                padding-left: 0 !important;
            }

            .title-section h1 {
                font-size: 22px !important;
                /* ✅ Changed from 36px to 28px */
                margin-top: 0 !important;
                margin-bottom: 10px !important;
                display: block !important;
                text-align: center !important;
                white-space: normal !important;
                /* ✅ Allows text to wrap */
            }

            .title-section h2 {
                font-size: 18px !important;
                /* ✅ Changed from 20px to 18px */
                margin-top: 0 !important;
                margin-bottom: 50px !important;
                display: block !important;
                text-align: center !important;
                white-space: normal !important;
                /* ✅ Allows text to wrap */
            }

            .login-section {
                justify-content: center !important;
                padding-right: 0 !important;
                flex: none !important;
                width: 100% !important;
                display: flex !important;
            }

            form {
                width: 100% !important;
                max-width: 340px !important;
                padding: 30px !important;
                border-radius: 30px !important;
            }

            input[type="text"],
            input[type="password"] {
                width: 100% !important;
                margin-bottom: 25px !important;
            }

            .form-buttons {
                flex-direction: column !important;
                gap: 10px !important;
            }

            input[type="submit"],
            input[type="button"] {
                width: 100% !important;
                padding: 12px !important;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {
            .title-section h1 {
                font-size: 20px;
            }

            .title-section h2 {
                font-size: 14px;
            }

            form {
                padding: 25px 20px;
                border-radius: 25px;
            }

            input[type="text"],
            input[type="password"] {
                margin-bottom: 20px;
                padding: 8px;
            }
        }
    </style>
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

    <script src="/Inventory-Accounts-System-v1/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>