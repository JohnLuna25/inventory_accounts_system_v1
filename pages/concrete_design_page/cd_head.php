<head>
    <meta charset="UTF-8">
    <title>Concrete Design List</title>
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
            width: 95%;
            max-width: 1200px;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            font-size: 14px;
        }

        th {
            background: #0b63c4;
            color: #fff;
        }

        a.button {
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a.view {
            background: #4CAF50;
            margin-right: 5px;
        }

        a.edit {
            background: #2196F3;
        }

        /* Dropbar style */
        .dropbar {
            display: none;
            background-color: #0b63c4;
            color: yellow;
            padding: 10px;
            text-align: left;
            border-radius: 0 0 8px 8px;
        }

        .dropbar table {
            width: 100%;
            background: #fff;
            border-collapse: collapse;
            color: black;
        }

        .dropbar td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .dropbar td:first-child {
            font-weight: bold;
            text-align: left;
        }

        .dropbar td:last-child {
            text-align: center;
        }
    </style>
</head>