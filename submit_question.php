<?php
session_start();

// 1. Check login
if (!isset($_SESSION['user_id'])) {
  die("❌ You must be logged in to submit a question.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 2. Sanitize and retrieve inputs
  $user_id = $_SESSION['user_id'];
  $title = trim($_POST['title']);
  $description = $_POST['description']; // rich HTML
  $tags = $_POST['tags'] ?? [];

  if (empty($title) || empty($description)) {
    die("❌ Title and description are required.");
  }

  // 3. Connect to DB
  $mysqli = new mysqli("localhost", "root", "", "odoo");
  if ($mysqli->connect_error) {
    die("Database error: " . $mysqli->connect_error);
  }

  // 4. Insert question
  $stmt = $mysqli->prepare("INSERT INTO questions (user_id, title, description) VALUES (?, ?, ?)");
  $stmt->bind_param("iss", $user_id, $title, $description);
  $stmt->execute();
  $question_id = $stmt->insert_id;
  $stmt->close();

  // 5. Handle tags
  foreach ($tags as $tagName) {
    $tagName = trim($tagName);

    // 5.1 Check if tag exists
    $tagStmt = $mysqli->prepare("SELECT id FROM tags WHERE name = ?");
    $tagStmt->bind_param("s", $tagName);
    $tagStmt->execute();
    $tagStmt->store_result();

    if ($tagStmt->num_rows > 0) {
      $tagStmt->bind_result($tag_id);
      $tagStmt->fetch();
    } else {
      // 5.2 Insert new tag
      $insertTagStmt = $mysqli->prepare("INSERT INTO tags (name) VALUES (?)");
      $insertTagStmt->bind_param("s", $tagName);
      $insertTagStmt->execute();
      $tag_id = $insertTagStmt->insert_id;
      $insertTagStmt->close();
    }
    $tagStmt->close();

    // 5.3 Insert into question_tags
    $pivotStmt = $mysqli->prepare("INSERT IGNORE INTO question_tags (question_id, tag_id) VALUES (?, ?)");
    $pivotStmt->bind_param("ii", $question_id, $tag_id);
    $pivotStmt->execute();
    $pivotStmt->close();
  }

  // ✅ FIXED: Redirect to home with success flag
  header("Location: index.php?submitted=1");
  exit;

} else {
  header("Location: ask.php");
  exit;
}
?>
