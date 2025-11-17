<style>
    /* Sidebar container styling */
    #sidebar {
        background-color: lightblue;
        padding: 0;
        top: 131px;
        width: 0;
        /* Sidebar initially hidden */
        min-height: 100vh;
        position: fixed;
        overflow: hidden;
        transition: width 0.2s, padding 0.2s;
    }

    /* Sidebar list styling */
    #sidebar ul {
        display: flex;
        flex-direction: column;
        margin-top: 200px;
    }

    /* Buttons inside sidebar */
    .sidebar-button {
        margin: 5px 0;
        background-color: cornflowerblue;
        border-radius: 40px;
        padding: 10px;
        font-size: 16px;
        cursor: pointer;
    }

    /* Hover effect for sidebar buttons */
    .sidebar-button:hover {
        background-color: #0004ffff;
        color: white;
    }

    /* Main content area transitions when sidebar opens/closes */
    #main-content {
        transition: margin-left 0.2s;
    }


    /* Logout button appearance */
    #logout-button {
        margin-top: 75px;
        margin-bottom: 20px;
        color: black;
        background-color: #ff2600ff;
        border-radius: 20px;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
    }

    #logout-button:hover {
        background-color: #ff0000ff;
        color: white;
    }

    /* Welcome message styling in sidebar */
    #welcome-user {
        font-size: 17px;
        font-weight: bold;
        color: darkblue;
        text-align: center;
        margin-top: -180px;
        margin-bottom: 30px;
        border-radius: 20px;
        padding: 10px;
        background-color: cornflowerblue;
    }
</style>