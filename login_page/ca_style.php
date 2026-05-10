<style>
    body {
        font-family: Arial, sans-serif;
        background-color: blue;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        padding: 20px;
        box-sizing: border-box;
    }

    .form-container {
        background-color: lightgray;
        padding: 50px;
        border-radius: 40px;
        width: 470px;
        box-sizing: border-box;
        text-align: left;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }

    .password-wrapper {
        position: relative;
        display: block;
        width: 100%;
    }

    .password-wrapper input {
        width: 100%;
        padding: 10px;
        padding-right: 35px;
        border-radius: 5px;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 37%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #808080;
        font-size: 18px;
        z-index: 10;
    }

    input[type="submit"] {
        width: 100%;
        padding: 10px;
        background-color: blue;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }

    input[type="submit"]:hover {
        background-color: #0000ff;
        color: #ffff00;
    }

    .message {
        text-align: center;
        margin-bottom: 15px;
    }

    .error {
        color: red;
    }

    .success-box {
        background-color: #BFC3AA;
        width: 70%;
        height: 30%;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 40px;
        font-size: 30px;
        font-weight: bold;
        color: blue;
        text-align: center;
    }

    .required-field {
        color: red;
        font-size: 16px;
        font-weight: bold;
        margin-left: 2px;
    }


    .form-container label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .role-selection {
        margin-bottom: 15px;
        align-items: center;
    }

    .role-label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        color: #000000;
        margin-top: 10px;

    }

    .radio-group {
        display: flex;
        gap: 30px;
        align-items: center;
        justify-content: center;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: normal;
        font-size: 14px;
    }

    .radio-option input[type="radio"] {
        accent-color: blue;
        width: auto;
        margin: 0;
        padding: 0;
        cursor: pointer;
    }


    /* ========== RESPONSIVE STYLES ========== */

    /* Tablet */
    @media (max-width: 992px) {
        .form-container {
            width: 500px;
            padding: 70px 60px;
        }

        .form-container h2 {
            font-size: 28px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 18px;
            margin-bottom: 22px;
            font-size: 20px;
        }

        input[type="submit"] {
            padding: 18px;
            font-size: 24px;
        }
    }

    /* Mobile - MEGA BIG FORM */
    @media (max-width: 768px) {
        body {
            align-items: flex-start;
            padding: 10px;
        }

        .form-container {
            width: 100%;
            max-width: 100%;
            height: 100vh;
            max-height: 100vh;
            padding: 60px 40px;
            border-radius: 0;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-container h2 {
            font-size: 42px;
            margin-bottom: 50px;
            text-align: center;
        }

        .form-container label {
            font-size: 28px;
            font-weight: bold;
            color: blue;
            display: block;
            margin-bottom: 12px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 28px;
            margin-bottom: 40px;
            font-size: 28px;
            border-radius: 20px;
            height: 80px;
        }

        input[type="submit"] {
            padding: 28px;
            font-size: 34px;
            margin-top: 20px;
            border-radius: 20px;
            height: 90px;
        }

        .success-box {
            width: 98%;
            padding: 80px 50px;
            font-size: 38px;
            min-height: 400px;
            border-radius: 50px;
        }
    }

    /* Small Mobile - STILL MEGA BIG */
    @media (max-width: 480px) {
        .form-container {
            padding: 50px 30px;
        }

        .form-container h2 {
            font-size: 36px;
            margin-bottom: 40px;
        }

        .form-container label {
            font-size: 24px;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 30px;
            margin-bottom: 35px;
            font-size: 30px;
            border-radius: 25px;
            height: 90px;
        }

        input[type="submit"] {
            padding: 30px;
            font-size: 36px;
            border-radius: 25px;
            height: 100px;
        }

        .success-box {
            font-size: 32px;
            padding: 70px 40px;
            width: 98%;
            min-height: 380px;
            border-radius: 45px;
        }
    }

    /* Very Small Mobile */
    @media (max-width: 360px) {
        .form-container {
            padding: 40px 25px;
        }

        .form-container h2 {
            font-size: 32px;
            margin-bottom: 35px;
        }

        .form-container label {
            font-size: 22px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 26px;
            font-size: 26px;
            margin-bottom: 30px;
            height: 80px;
        }

        input[type="submit"] {
            padding: 26px;
            font-size: 32px;
            height: 90px;
        }
    }
</style>