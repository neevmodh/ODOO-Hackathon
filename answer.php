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

// Get question owner
$qowner = $mysqli->query("SELECT user_id FROM questions WHERE id=$question_id");
$question_owner_id = $qowner && $qowner->num_rows ? $qowner->fetch_assoc()['user_id'] : 0;

// Handle new answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer_text = trim($_POST['answer']);
    if (!empty($answer_text)) {
        $user_id = $_SESSION['user_id'];
        $stmt = $mysqli->prepare("INSERT INTO answers (question_id, user_id, description, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $question_id, $user_id, $answer_text);
        $stmt->execute();
        $stmt->close();

        // Notification for question owner
        if ($question_owner_id && $question_owner_id != $user_id) {
            $msg = "Someone answered your question.";
            $stmt2 = $mysqli->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt2->bind_param("is", $question_owner_id, $msg);
            $stmt2->execute();
            $stmt2->close();
        }

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

// Fetch answers with username, vote counts, and accepted status
$stmt = $mysqli->prepare("
    SELECT a.id, a.description, a.created_at, u.username,
      IFNULL(SUM(CASE WHEN v.vote_type = 'up' THEN 1 ELSE 0 END), 0) AS upvotes,
      IFNULL(SUM(CASE WHEN v.vote_type = 'down' THEN 1 ELSE 0 END), 0) AS downvotes,
      a.is_accepted
    FROM answers a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN votes v ON v.answer_id = a.id
    WHERE a.question_id = ?
    GROUP BY a.id
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
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/quill-emoji@0.1.7/dist/quill-emoji.css" rel="stylesheet" />
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
    .accepted {
      color: #2ed573;
      font-weight: bold;
    }
    .vote-btn {
      cursor: pointer;
      user-select: none;
      margin-right: 10px;
    }
  </style>
</head>
<body>
<div class="container">
  <h2><?= htmlspecialchars($title) ?></h2>
  <div><?= $description ?></div>
  <small>Asked by <strong><?= htmlspecialchars($asker_username) ?></strong> on <?= date('M j, Y', strtotime($created_at)) ?></small>
  <hr />

  <h4>Answers</h4>
  <?php if (count($answers) === 0): ?>
    <p class="text-muted">No answers yet. Be the first to answer!</p>
  <?php else: ?>
    <?php foreach ($answers as $answer): ?>
      <div class="answer-card">
        <?= $answer['is_accepted'] ? '<span class="accepted">✔ Accepted Answer</span><br>' : '' ?>
        <div><?= $answer['description'] ?></div>
        <div>
          <span class="answer-username"><?= htmlspecialchars($answer['username']) ?></span> •
          <span class="answer-date"><?= date('M j, Y H:i', strtotime($answer['created_at'])) ?></span>
        </div>
        <div class="mt-2">
          <button class="vote-btn btn btn-sm btn-outline-success" onclick="voteAnswer(<?= $answer['id'] ?>, 'up')">⬆️ <?= $answer['upvotes'] ?></button>
          <button class="vote-btn btn btn-sm btn-outline-danger" onclick="voteAnswer(<?= $answer['id'] ?>, 'down')">⬇️ <?= $answer['downvotes'] ?></button>
          <?php if ($_SESSION['user_id'] == $question_owner_id && !$answer['is_accepted']): ?>
            <button class="btn btn-sm btn-outline-primary" onclick="acceptAnswer(<?= $answer['id'] ?>)">Accept</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <hr />
  <h4>Your Answer</h4>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['user_id'])): ?>
  <form method="POST" action="answer.php?id=<?= $question_id ?>">
    <div class="mb-3">
      <!-- Quill Editor -->
      <div id="toolbar">
        <span class="ql-formats">
          <select class="ql-font"></select>
          <select class="ql-size"></select>
        </span>
        <span class="ql-formats">
          <button class="ql-bold"></button>
          <button class="ql-italic"></button>
          <button class="ql-strike"></button>
        </span>
        <span class="ql-formats">
          <button class="ql-list" value="ordered"></button>
          <button class="ql-list" value="bullet"></button>
        </span>
        <span class="ql-formats">
          <select class="ql-align"></select>
        </span>
        <span class="ql-formats">
          <button class="ql-link"></button>
          <button class="ql-image"></button>
          <button class="ql-emoji"></button>
        </span>
        <span class="ql-formats">
          <button class="ql-clean"></button>
        </span>
      </div>
      <div id="editor" style="height:150px;background:#fff;color:#000;border-radius:6px;"></div>
      <input type="hidden" name="answer" id="answer" />
    </div>
    <button type="submit" class="btn btn-success">Submit Answer</button>
  </form>
  <?php else: ?>
    <div class="alert alert-warning">You must be logged in to post an answer.</div>
  <?php endif; ?>

  <div class="mt-3">
    <a href="index.php" class="btn btn-outline-light">Back to Home</a>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill-emoji@0.1.7/dist/quill-emoji.min.js"></script>
<script>
const quill = new Quill('#editor', {
  modules: {
    toolbar: '#toolbar',
    "emoji-toolbar": true,
    "emoji-textarea": false,
    "emoji-shortname": true
  },
  theme: 'snow'
});
document.querySelector("form").onsubmit = function () {
  document.getElementById("answer").value = quill.root.innerHTML;
};

// Image upload handler for Quill
const toolbar = quill.getModule('toolbar');
toolbar.addHandler('image', () => {
  const input = document.createElement('input');
  input.setAttribute('type', 'file');
  input.setAttribute('accept', 'image/*');
  input.click();
  input.onchange = () => {
    const file = input.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('image', file);
    fetch('upload_image.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const range = quill.getSelection();
        quill.insertEmbed(range.index, 'image', data.url);
      } else {
        alert('Image upload failed: ' + data.error);
      }
    });
  };
});

function voteAnswer(answerId, type) {
  fetch('vote_answer.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ answer_id: answerId, vote_type: type })
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    if (data.success) location.reload();
  });
}
function acceptAnswer(answerId) {
  fetch('accept_answer.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ answer_id: answerId })
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    if (data.success) location.reload();
  });
}
</script>
</body>
</html>