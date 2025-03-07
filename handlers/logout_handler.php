<?php
require 'database.php'; // Database connection file

function logoutUser() {
    session_start();
    error_log("Logging out user: " . $_SESSION['user_id']);
    addLog($_SESSION['user_id'], "User logged out");
    session_destroy();
}
?>