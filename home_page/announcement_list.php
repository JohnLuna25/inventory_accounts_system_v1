<?php
    // Start session only if it's not already active
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Initialize announcements array if it doesn't exist
    if (!isset($_SESSION['announcements'])) {
        $_SESSION['announcements'] = [];
    }

    // Handle adding announcement
    if (isset($_POST['add_announcement']) && !empty(trim($_POST['announcement']))) {
        array_unshift($_SESSION['announcements'], htmlspecialchars($_POST['announcement']));
        header("Location: home.php");
        exit();
    }

    // Handle deleting announcement
    if (isset($_POST['delete_announcement'])) {
        $index = $_POST['delete_announcement'];
        if (isset($_SESSION['announcements'][$index])) {
            unset($_SESSION['announcements'][$index]);
            $_SESSION['announcements'] = array_values($_SESSION['announcements']); // reindex
        }
        header("Location: home.php");
        exit();
    }
?>