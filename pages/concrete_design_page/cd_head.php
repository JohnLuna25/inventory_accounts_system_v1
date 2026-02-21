<head>
    <meta charset="UTF-8">
    <title>Concrete Design List</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php showHeader(); ?>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            color: #000;

        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-top: 30px;
        }

        table {
            width: max-content;
            min-width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 3px 10px;
            font-size: 13px;
            white-space: nowrap;
        }

        th {
            background: #0b63c4;
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        a.button {
            padding: 3px 8px;
            font-size: 12px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }

        a.view {
            background: #4CAF50;
            margin-right: 5px;
        }

        a.edit {
            background: #2196F3;
        }

        .dropbar {
            display: none;
            background-color: #0b63c4;
            color: yellow;
            padding: 10px;
            border-radius: 0 0 8px 8px;
        }

        .dropbar table {
            border-collapse: collapse;
            width: 100%;
            color: black;
        }

        .dropbar td:first-child {
            font-weight: bold;
            text-align: left;
        }

        .dropbar td:last-child,
        .dropbar td:nth-child(even) {
            text-align: center;
        }
    </style>
</head>