<style>
    body {
        font-family: Arial, sans-serif;
        background-color: blue;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .form-container {
        background-color: lightgray;
        padding: 50px;
        border-radius: 40px;
        width: 340px;
        box-sizing: border-box;
        text-align: left;
    }

    input[type="text"],
    input[type="password"] {
        width: 91%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    input[type="submit"] {
        width: 100%;
        padding: 10px;
        background-color: blue;
        color: yellow;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
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
</style>