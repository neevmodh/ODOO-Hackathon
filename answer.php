<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // example user id, replace with real login in production
}

$mysqli = new mysqli("localhost", "root", "", "odoo");
if ($mysqli->connect_error) {
    die("DB Connect Error: " . $mysqli->connect_error);
}

// Get question ID from URL
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id <= 0) {
    die("Invalid question ID.");
}

// Handle new answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer_text = trim($_POST['answer']);
    if (!empty($answer_text)) {
        $user_id = $_SESSION['user_id'];
        $stmt = $mysqli->prepare("INSERT INTO answers (question_id, user_id, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $question_id, $user_id, $answer_text);
        $stmt->execute();
        $stmt->close();
        header("Location: answer.php?id=$question_id");
        exit;
    } else {
        $error = "Answer cannot be empty.";
    }
}

// Fetch question details
$stmt = $mysqli->prepare("
    SELECT q.title, q.description, u.username, q.created_at
    FROM questions q
    JOIN users u ON q.user_id = u.id
    WHERE q.id = ?
");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$stmt->bind_result($title, $description, $asker_username, $created_at);
if (!$stmt->fetch()) {
    die("Question not found.");
}
$stmt->close();

// Fetch answers with username
$stmt = $mysqli->prepare("
    SELECT a.description, a.created_at, u.username
    FROM answers a
    JOIN users u ON a.user_id = u.id
    WHERE a.question_id = ?
    ORDER BY a.created_at ASC
");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();
$answers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Answer Question - <?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: url('bg.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: "Segoe UI", sans-serif;
      color: #e6edf3;
      margin: 0;
    }
    .container {
      background: rgba(0, 0, 0, 0.65);
      border-radius: 16px;
      padding: 30px;
      backdrop-filter: blur(6px);
      margin-top: 20px;
    }
    .answer-card {
      background-color: #161b22;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      color: #c9d1d9;
    }
    .answer-username {
      font-weight: 600;
      color: #58a6ff;
    }
    .answer-date {
      font-size: 0.8rem;
      color: #8b949e;
    }
  </style>
</head>
<body>
<div class="container">
  <h2><?= htmlspecialchars($title) ?></h2>
  <?= nl2br(htmlspecialchars(strip_tags($description))) ?>
  <small>Asked by <strong><?= htmlspecialchars($asker_username) ?></strong> on <?= date('M j, Y', strtotime($created_at)) ?></small>
  <hr />

  <h4>Answers</h4>
  <?php if (count($answers) === 0): ?>
    <p class="text-muted">No answers yet. Be the first to answer!</p>
  <?php else: ?>
    <?php foreach ($answers as $answer): ?>
      <div class="answer-card">
        <?= nl2br(htmlspecialchars(strip_tags($answer['description']))) ?>
        <div>
          <span class="answer-username"><?= htmlspecialchars($answer['username']) ?></span> •
          <span class="answer-date"><?= date('M j, Y H:i', strtotime($answer['created_at'])) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <hr />
  <h4>Your Answer</h4>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST" action="answer.php?id=<?= $question_id ?>">
    <div class="mb-3">
      <textarea name="answer" rows="5" class="form-control" required></textarea>
    </div>
    <button type="submit" class="btn btn-success">Submit Answer</button>
  </form>

  <div class="mt-3">
    <a href="index.php" class="btn btn-outline-light">Back to Home</a>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
