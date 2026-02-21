<style>
    /* Main content: push buttons down from top */
    #main-content {
        transition: none;
        /* no need for sidebar transition now */
    }

    /* Dashboard buttons: 2 columns, 4 rows layout */
    .dashboard-buttons {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        justify-items: center;
        /* center buttons horizontally */
        align-items: center;
        max-width: 800px;
        /* optional, keeps them from stretching too far */
        margin: 0 auto;
        /* center the grid in the page */
    }

    /* Individual buttons */
    .dashboard-buttons button {
        width: 200px;
        height: 60px;
        font-size: 16px;
        border-radius: 40px;
        /* rounded-pill */
        cursor: pointer;
    }

    /* Bootstrap buttons override if needed */
    .dashboard-buttons .btn-primary {
        background-color: #0d6efd;
        /* standard primary color */
        border: none;
    }

    .dashboard-buttons .btn-primary:hover {
        background-color: #0b5ed7;
    }

    /* Logout button styling */
    #logout-button {
        background-color: #ff2600;
        color: white;
        border-radius: 20px;
        border: none;
        padding: 10px 20px;
        width: 200px;
    }

    #logout-button:hover {
        background-color: #ff0000;
    }

    /* Optional: responsive adjustment */
    @media (max-width: 768px) {
        #main-content {
            padding-top: 800px;
            /* smaller screens, less offset */
        }

        .dashboard-buttons {
            grid-template-columns: 1fr;
            /* single column on mobile */
            gap: 1.5rem;
        }

        .dashboard-buttons button,
        #logout-button {
            width: 80%;
            /* fit mobile screen */
        }
    }

    /* Footer styling */
    footer {
        font-family: Arial, sans-serif;
        font-size: 14px;
        background-color: #0d6efd;
        color: #ffd34e;
        text-align: center;
        padding: 5px 0;
        position: fixed;
        bottom: 0;
        width: 100%;
    }
</style>