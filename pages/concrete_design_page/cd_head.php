<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concrete Design List</title>
    <link rel="stylesheet" href="/Inventory-Accounts-System-v1/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">


    <style>
        body {
            font-family: Arial, sans-serif;
            background: #BFC3AA;
            color: #000;
            margin: 0;
            padding: 0;
            padding-bottom: 60px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin: 15px;
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
            padding: 8px;
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

        .btn-view {
            background: #4CAF50;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
            margin-right: 5px;
        }

        .btn-edit {
            background: #2196F3;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            display: inline-block;
        }

        .dropbar {
            display: none;
            background-color: #f9f9f9;
            padding: 10px;
        }

        .dropbar table {
            border-collapse: collapse;
            width: 100%;
            color: black;
            box-shadow: none;
        }

        .dropbar td:first-child {
            font-weight: bold;
            text-align: left;
            width: 40%;
            background: #f0f0f0;
        }

        .dropbar td:last-child {
            text-align: right;
        }

        @media (max-width: 576px) {
            .table-wrapper {
                margin: 10px;
            }

            th,
            td {
                padding: 6px;
                font-size: 11px;
            }

            .btn-view,
            .btn-edit {
                padding: 4px 6px;
                font-size: 10px;
            }

            .dropbar td:first-child {
                font-size: 11px;
            }
        }
    </style>
</head>