<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["notification_id"])) {
    $notification_id = intval($_POST["notification_id"]);
    $success = mark_notification_as_read($notification_id);
    
    echo json_encode(["success" => $success]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
}
