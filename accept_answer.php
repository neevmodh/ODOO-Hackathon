<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['answer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}
$answer_id = (int)$data['answer_id'];
$user_id = $_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "odoo");
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}
// Get question id and owner
$res = $mysqli->query("SELECT question_id FROM answers WHERE id=$answer_id");
if (!$res || $res->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Answer not found']);
    exit;
}
$row = $res->fetch_assoc();
$question_id = $row['question_id'];
$res2 = $mysqli->query("SELECT user_id FROM questions WHERE id=$question_id");
if (!$res2 || $res2->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Question not found']);
    exit;
}
$row2 = $res2->fetch_assoc();
if ($row2['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Only question owner can accept answers']);
    exit;
}
// Set all answers to not accepted
$mysqli->query("UPDATE answers SET is_accepted=0 WHERE question_id=$question_id");
// Set this answer as accepted
$mysqli->query("UPDATE answers SET is_accepted=1 WHERE id=$answer_id");
// Notify answer owner
$res3 = $mysqli->query("SELECT user_id FROM answers WHERE id=$answer_id");
if ($res3 && $row3 = $res3->fetch_assoc()) {
    $answer_owner = $row3['user_id'];
    if ($answer_owner != $user_id) {
        $msg = "Your answer was accepted!";
        $stmt = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $answer_owner, $msg);
        $stmt->execute();
        $stmt->close();
    }
}
echo json_encode(['success' => true, 'message' => 'Answer accepted']);
?>