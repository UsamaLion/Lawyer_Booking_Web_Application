<?php
session_start();
require_once "includes/config.php";
require_once "includes/functions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["id"];
$notifications = get_notifications($user_id);

echo json_encode($notifications);
