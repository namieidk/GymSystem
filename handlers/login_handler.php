<?php
require 'database.php'; // Database connection file

// User Login Handler
function loginUser($username, $password) {
    global $conn;
    error_log("Attempting to log in user: $username");
    $stmt = $conn->prepare("CALL UserLogin(?, ?)");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['message'] === 'Login successful') {
            session_start();
            $_SESSION['user_id'] = $row['user_id'];
            addLog($row['user_id'], "User logged in");
            error_log("Login successful for user: " . $row['user_id']);
            return true;
        }
    }
    error_log("Login failed for user: $username");
    return false;
}
?>