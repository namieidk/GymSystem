<?php
require 'database.php'; // Database connection file

function addLog($user_id, $action) {
    global $conn;
    error_log("Logging action: $action for user: $user_id");
    $stmt = $conn->prepare("CALL AddLog(?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $result = $stmt->execute();
    if (!$result) {
        error_log("Failed to insert log: " . $conn->error);
    }
    return $result;
}
?>