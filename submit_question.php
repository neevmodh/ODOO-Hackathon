<?php
// This is just a placeholder script to demonstrate POST handling.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = htmlspecialchars($_POST['title']);
  $description = htmlspecialchars($_POST['description']);
  $tags = htmlspecialchars($_POST['tags']);

  // TODO: Save to database
  echo "<h2>Question Submitted</h2>";
  echo "<p><strong>Title:</strong> $title</p>";
  echo "<p><strong>Description:</strong> $description</p>";
  echo "<p><strong>Tags:</strong> $tags</p>";
  echo "<p><a href='index.html'>← Go back to Home</a></p>";
} else {
  header("Location: ask.php");
  exit();
}
?>

