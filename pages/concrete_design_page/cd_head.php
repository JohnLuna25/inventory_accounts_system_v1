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

        /* TABLE WRAPPER (Enables horizontal scroll) */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin-top: 30px;
        }

        /* MAIN TABLE */
        table {
            width: max-content;
            /* allows table to grow naturally */
            min-width: 100%;
            /* at least full width */
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* HEADERS & CELLS */
        th,
        td {
            border: 1px solid #ccc;
            padding: 3px 10px;
            /* small height */
            font-size: 13px;
            white-space: nowrap;
            /* prevents wrapping */
        }

        th {
            background: #0b63c4;
            color: #fff;
            position: sticky;
            /* optional sticky header */
            top: 0;
            z-index: 2;
        }

        /* BUTTONS */
        a.button {
            padding: 3px 8px;
            /* smaller buttons */
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

        /* DROPBAR STYLE */
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
            border-collapse: collapse;
            color: black;
        }

        .dropbar td {
            border: 1px solid #ccc;
            padding: 6px;
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