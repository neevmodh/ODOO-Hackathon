<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
$user_id = $_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "odoo");
if ($mysqli->connect_error) {
    echo json_encode([]);
    exit;
}
if (isset($_GET['mark_read'])) {
    $mysqli->query("UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
}
$res = $mysqli->query("SELECT id, message, is_read, created_at FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 10");
$notifs = [];
while ($row = $res->fetch_assoc()) {
    $notifs[] = $row;
}
echo json_encode($notifs);
?>