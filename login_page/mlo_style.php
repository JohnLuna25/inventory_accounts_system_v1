<style>
    body {
        font-family: Arial, sans-serif;
        background-color: blue;
        display: flex;
        justify-content: center;
        align-items: center;
        overflow: hidden;
        /* prevents scrolling */
    }

    .form-container {
        margin-top: 8%;
        background: #fff;
        padding: 30px;
        border-radius: 20px;
        width: 350px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    input {
        width: 94%;
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    input[type="submit"] {
        background: blue;
        color: white;
        cursor: pointer;
        border: none;
        font-weight: bold;
    }

    .error {
        color: red;
        text-align: center;
    }

    .success {
        color: green;
        text-align: center;
        font-weight: bold;
    }

    .unlock-btn {
        display: block;
        margin: 0 auto;
        background-color: blue;
        color: yellow;
        /* text color */
        padding: 12px;
        width: 50%;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        border: none;
        transition: all 0.3s ease;
        -webkit-appearance: none;
        /* remove default input styles in WebKit browsers */
        -moz-appearance: none;
        /* remove default input styles in Firefox */
        appearance: none;
    }

    .unlock-btn:hover {
        background: #4154ffff;
        /* light blue to light yellow */
        color: #edff4fff;
        /* text color changes to yellow */
    }

    .pc_r {
        margin-top: 7px;
    }

    .password-alert {

        text-align: center;
        /* center the text */
        font: italic;
        /* optional: make it bold */
        color: #8a0500ff;
        /* optional: red color for alert */
        margin: 30px 0;
        margin-top: -15px;
        /* optional: spacing above/below */
        font-size: 11px;
        /* optional: adjust size */

    }
</style>