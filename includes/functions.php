<?php
require_once "config.php";

function add_notification($user_id, $message) {
    global $conn;
    $sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "is", $user_id, $message);
        if (mysqli_stmt_execute($stmt)) {
            return true;
        }
    }
    return false;
}

function get_notifications($user_id) {
    global $conn;
    $notifications = array();
    $sql = "SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $notifications[] = $row;
            }
        }
    }
    return $notifications;
}

function mark_notification_as_read($notification_id) {
    global $conn;
    $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $notification_id);
        if (mysqli_stmt_execute($stmt)) {
            return true;
        }
    }
    return false;
}
