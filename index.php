<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>StackIt - Home</title>
  <link rel="stylesheet" href="index.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<canvas id="snowCanvas"></canvas>

<nav class="navbar navbar-expand-lg navbar-dark px-4">
  <a class="navbar-brand" href="#">StackIt</a>
  <div class="collapse navbar-collapse">
    <form class="d-flex ms-3 me-auto">
      <input class="form-control me-2" id="searchInput" type="search" placeholder="Search..." />
      <button class="btn btn-outline-light" type="button" onclick="searchQuestions()">Search</button>
    </form>
    <div class="d-flex align-items-center gap-3">
      <div class="dropdown">
        <span class="fs-4 text-white dropdown-toggle" role="button" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false">🔔</span>
        <ul class="dropdown-menu dropdown-menu-end bg-dark text-light" aria-labelledby="notifBell">
          <li><a class="dropdown-item text-white" href="#">💬 Someone answered your question</a></li>
          <li><a class="dropdown-item text-white" href="#">👍 Your answer got upvoted</a></li>
        </ul>
      </div>
      <span id="themeToggle" class="fs-4 text-white" role="button" title="Toggle Theme">🌙</span>
      <button class="btn btn-outline-info">Login</button>
      <a href="ask.php" class="btn btn-success">Ask Question</a>
    </div>
  </div>
</nav>

<div class="container my-4 shadow-lg">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group">
      <button class="btn btn-outline-secondary" onclick="filterQuestions('newest')">Newest</button>
      <button class="btn btn-outline-secondary" onclick="filterQuestions('unanswered')">Unanswered</button>
    </div>
    <small class="text-muted">Showing latest questions</small>
  </div>

  <div id="questionList" class="question-list">
    <!-- JavaScript will inject questions here -->
  </div>

  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <li class="page-item"><a class="page-link bg-dark text-light" href="#">1</a></li>
      <li class="page-item"><a class="page-link bg-dark text-light" href="#">2</a></li>
      <li class="page-item"><a class="page-link bg-dark text-light" href="#">Next</a></li>
    </ul>
  </nav>
</div>

<script src="index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
