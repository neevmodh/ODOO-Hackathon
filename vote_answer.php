<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['answer_id'], $data['vote_type'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$answer_id = (int)$data['answer_id'];
$vote_type = $data['vote_type'];
$user_id = $_SESSION['user_id'];

if (!in_array($vote_type, ['up', 'down'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid vote type']);
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "odoo");
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Check if answer exists
$res = $mysqli->query("SELECT id FROM answers WHERE id = $answer_id");
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Answer not found']);
    exit;
}

// Check if user already voted on this answer
$stmt = $mysqli->prepare("SELECT id, vote_type FROM votes WHERE user_id = ? AND answer_id = ?");
$stmt->bind_param("ii", $user_id, $answer_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($vote_id, $existing_vote);
    $stmt->fetch();

    if ($existing_vote === $vote_type) {
        // Remove vote (toggle off)
        $stmt2 = $mysqli->prepare("DELETE FROM votes WHERE id = ?");
        $stmt2->bind_param("i", $vote_id);
        $stmt2->execute();
        $stmt2->close();

        echo json_encode(['success' => true, 'message' => 'Vote removed']);
        exit;
    } else {
        // Update vote type
        $stmt2 = $mysqli->prepare("UPDATE votes SET vote_type = ? WHERE id = ?");
        $stmt2->bind_param("si", $vote_type, $vote_id);
        $stmt2->execute();
        $stmt2->close();

        echo json_encode(['success' => true, 'message' => 'Vote updated']);
        exit;
    }
} else {
    // Insert new vote
    $stmt2 = $mysqli->prepare("INSERT INTO votes (user_id, answer_id, vote_type) VALUES (?, ?, ?)");
    $stmt2->bind_param("iis", $user_id, $answer_id, $vote_type);
    $stmt2->execute();
    $stmt2->close();

    echo json_encode(['success' => true, 'message' => 'Vote recorded']);
    exit;
}