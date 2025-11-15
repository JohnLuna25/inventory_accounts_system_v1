<?php
echo
    '<head>
        <meta charset="UTF-8">
        <title>SORCOM Inventory Management</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <?php showHeader(); ?>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                background: #BFC3AA;
                color: #000;
                text-align: center;
            }

            table {
                border-collapse: collapse;
                width: 90%;
                margin: 20px auto;
                background: #ffffffff
            }

            table th,
            table td {
                border: 1px solid #ccc;
                padding: 8px;
                text-align: left;
            }

            table th {
                background: #0b63c4;
                color: #fff;
            }

            button {
                padding: 5px 10px;
                margin: 2px;
                cursor: pointer;
                border-radius: 5px;
            }

            .modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
            }

            .modal-content {
                background: white;
                width: 450px;
                margin: 100px auto;
                padding: 20px;
                border-radius: 5px;
            }

            .modal-content h3 {
                margin-top: 0;
            }

            .modal-content input,
            .modal-content textarea {
                width: 100%;
                margin: 5px 0 10px 0;
                padding: 5px;
                box-sizing: border-box;
            }

            .modal-content button {
                margin-right: 5px;
            }
        </style>
    </head>'
    ?>