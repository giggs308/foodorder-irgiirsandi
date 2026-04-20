<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Redirect to the new dashboard
header('Location: dashboard.php');
exit();
?>
