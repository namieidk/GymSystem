<?php
require 'database.php'; // Database connection file

function recordAttendance($member_id) {
    global $conn;
    error_log("Recording attendance for member: $member_id");
    $stmt = $conn->prepare("CALL RecordAttendance(?)");
    $stmt->bind_param("i", $member_id);
    $result = $stmt->execute();
    if ($result) {
        error_log("Attendance recorded for member: $member_id");
    } else {
        error_log("Failed to record attendance: " . $conn->error);
    }
    return $result;
}
?>