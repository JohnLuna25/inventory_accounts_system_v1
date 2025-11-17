<?php
echo '
<style>
        body {
            background-color: blue;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
            margin-bottom: 20px;
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
    </style>
    ';
?>