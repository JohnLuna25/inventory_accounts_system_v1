<?php
echo '
<style>
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
        font-family: "JetBrains Mono", "Roboto Mono", "Courier New", monospace;
        font-weight: 400px;
        color: yellow;
        position: absolute;
        top: 40px;
        left: 30px;
        margin: 0;
        color: #ffffff;
        
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
        width: 100%;
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
    color: white;
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
    margin-bottom: 20px;
    padding: 8px;
    color: red;
    font-size: 14px;
    text-align: center;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 5px;
}

    

    /* Row 1: Login and Create Account buttons - SAME ROW */
.form-buttons {
    display: flex;
    gap: 10px;
}

input[type="submit"],
input[type="button"] {
    flex: 1;
    padding: 10px;
    background-color: blue;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

/* Row 2: Forgot Password button - FULL WIDTH, OWN ROW */
.forgot-password-container {
    margin-top: 15px;
}

.forgot-password-btn {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: blue;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s ease;
    box-sizing: border-box;
}

.forgot-password-btn:hover {
    background-color: #0000cc;
    color: white;
}
    
    .forgot-password-container {
    margin-top: 15px;
}

    /* Apply fonts to animated title and subtitle */
    #title {
        font-family: "Iosevka Charon Mono", "Courier New", monospace !important;
        font-weight: bold;
        font-size: 50px;
        white-space: nowrap;
        margin-right: 20px;
        margin-bottom: 20px;
        color: white;
    }

    #subtitle {
        font-family: "Lexend Giga", "Segoe UI", sans-serif;
        font-size: 20px;
        margin-top: 10px;
        margin-bottom: 20px;
        white-space: nowrap;
        margin-right: 20px;
        color: white;
    }

    /* ========== RESPONSIVE STYLES ========== */

    /* Tablet */
    @media (max-width: 992px) {
        #title {
            font-size: 40px;
        }

        #subtitle {
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
            color: #ffffff;
            font-size: 12px;
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

        #title {
            font-size: 22px !important;
            margin-top: 0 !important;
            margin-bottom: 10px !important;
            display: block !important;
            text-align: center !important;
            white-space: normal !important;
        }

        #subtitle {
            font-size: 18px !important;
            margin-top: 0 !important;
            margin-bottom: 50px !important;
            display: block !important;
            text-align: center !important;
            white-space: normal !important;
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
        #title {
            font-size: 20px;
        }

        #subtitle {
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
';
?>