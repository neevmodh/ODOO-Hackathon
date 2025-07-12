<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ask a Question - StackIt</title>
  <link rel="stylesheet" href="ask.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<canvas id="snowCanvas"></canvas>

<nav class="navbar navbar-expand-lg navbar-dark px-4">
  <a class="navbar-brand" href="index.html">← StackIt</a>
</nav>

<div class="container my-5 shadow-lg">
  <h2 class="mb-4 text-info">📝 Ask a Question</h2>

  <form id="questionForm" method="POST" action="submit_question.php">
    <div class="mb-3">
      <label for="title" class="form-label">Question Title</label>
      <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., How to join two tables in SQL?" />
    </div>

    <div class="mb-3">
      <label for="description" class="form-label">Description (use formatting)</label>
      <textarea class="form-control" id="description" name="description" rows="8" required></textarea>
    </div>

    <div class="mb-3">
      <label for="tags" class="form-label">Tags (comma-separated)</label>
      <input type="text" class="form-control" id="tags" name="tags" required placeholder="e.g., SQL, JOIN, Beginner" />
    </div>

    <button type="submit" class="btn btn-success">Post Question</button>
    <a href="index.html" class="btn btn-outline-light ms-2">Cancel</a>
  </form>
</div>

<script src="ask.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
