<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}
if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No file']);
    exit;
}
$img = $_FILES['image'];
if ($img['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error']);
    exit;
}
$ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit;
}
$target = 'C:\xampp\htdocs\odoo\uploads\\';
if (!is_dir($target)) mkdir($target, 0777, true);
$filename = uniqid('img_', true) . '.' . $ext;
$filepath = $target . $filename;
if (move_uploaded_file($img['tmp_name'], $filepath)) {
    echo json_encode(['success' => true, 'url' => $filepath]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save']);
}
?>