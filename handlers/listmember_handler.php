<?php
require 'database.php'; // Database connection file

function addMember($name, $email, $phone) {
    global $conn;
    error_log("Adding new member: $name, Email: $email");
    $stmt = $conn->prepare("CALL AddMember(?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $phone);
    $result = $stmt->execute();
    if ($result) {
        error_log("Member added successfully: $name");
    } else {
        error_log("Failed to add member: " . $conn->error);
    }
    return $result;;
}
?>