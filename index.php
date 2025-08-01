<?php
session_start();

// Simulate logged-in user (remove in production, use real login system)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // example user id
}

// Connect to database
$mysqli = new mysqli("localhost", "root", "", "odoo");
if ($mysqli->connect_error) {
    die("DB Connect Error: " . $mysqli->connect_error);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Handle search and tag filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

$sql = "
    SELECT q.id, q.title, q.description, q.created_at, u.username,
      GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ',') AS tags,
      IFNULL(SUM(CASE WHEN v.vote_type = 'up' THEN 1 ELSE 0 END), 0) AS upvotes,
      IFNULL(SUM(CASE WHEN v.vote_type = 'down' THEN 1 ELSE 0 END), 0) AS downvotes
    FROM questions q
    JOIN users u ON q.user_id = u.id
    LEFT JOIN question_tags qt ON q.id = qt.question_id
    LEFT JOIN tags t ON qt.tag_id = t.id
    LEFT JOIN answers a ON a.question_id = q.id
    LEFT JOIN votes v ON v.answer_id = a.id
    WHERE 1
";

$params = [];
if ($search !== '') {
    $sql .= " AND (q.title LIKE ? OR q.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}
if ($tag !== '') {
    $sql .= " AND FIND_IN_SET(?, GROUP_CONCAT(t.name))";
    $params[] = $tag;
}

$sql .= "
    GROUP BY q.id
    ORDER BY q.created_at DESC
    LIMIT 20
";

if (count($params)) {
    $stmt = $mysqli->prepare($sql);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>StackIt - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: url('bg.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: "Segoe UI", sans-serif;
      color: #e6edf3;
      margin: 0;
    }

    #snowCanvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
    }

    .container {
      background: rgba(0, 0, 0, 0.65);
      border-radius: 16px;
      padding: 30px;
      backdrop-filter: blur(6px);
      position: relative;
      z-index: 1;
    }

    .navbar {
      background-color: rgba(22, 27, 34, 0.9);
    }

    .btn-success {
      background-color: #238636;
      border: none;
    }

    .card.question {
      background-color: #161b22;
      border-left: 3px solid transparent;
      transition: border 0.3s;
      color: #c9d1d9;
      margin-bottom: 1rem;
    }

    .card.question:hover {
      border-left: 3px solid #238636;
    }

    .question-title {
      color: #58a6ff;
      font-size: 1.2rem;
      font-weight: 500;
      text-decoration: none;
    }

    .badge {
      font-size: 0.75rem;
      margin-right: 0.25rem;
    }

    .badge.tag {
      background-color: #ff6b81;
      cursor: pointer;
      transition: 0.2s;
    }

    .badge.tag:hover {
      background-color: #ff4757;
      transform: scale(1.05);
    }

    body.night-mode {
      background: #f0f0f0 url('bg.jpg') no-repeat center center fixed;
      color: #000;
    }

    body.night-mode .container {
      background: rgba(255, 255, 255, 0.75);
      color: #000;
    }

    body.night-mode .card.question {
      background-color: #fff;
      color: #111;
    }

    body.night-mode .navbar {
      background-color: rgba(255, 255, 255, 0.9);
    }

    .vote-btn {
      cursor: pointer;
      user-select: none;
      margin-right: 15px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark px-4">
  <a class="navbar-brand" href="#">StackIt</a>
  <div class="collapse navbar-collapse">
    <form class="d-flex ms-3 me-auto" method="GET" action="index.php">
      <input class="form-control me-2" id="searchInput" name="search" type="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" />
      <button class="btn btn-outline-light" type="submit">Search</button>
    </form>
    <div class="d-flex align-items-center gap-3">
      <div class="dropdown">
        <span class="fs-4 text-white position-relative dropdown-toggle" role="button" id="notifBellIcon" data-bs-toggle="dropdown" aria-expanded="false">
          🔔<span id="notifCount" style="position:absolute;top:0;right:0;background:#ff4757;color:#fff;border-radius:50%;font-size:0.7em;padding:2px 6px;display:none;"></span>
        </span>
        <ul class="dropdown-menu dropdown-menu-end bg-dark text-light" aria-labelledby="notifBellIcon" id="notifDropdown">
          <li><span class="dropdown-item text-muted">Loading...</span></li>
        </ul>
        <script>
        function loadNotifications() {
          fetch('notifications.php')
            .then(res => res.json())
            .then(data => {
              const dropdown = document.getElementById('notifDropdown');
              dropdown.innerHTML = '';
              if (data.length === 0) {
                dropdown.innerHTML = '<li><span class="dropdown-item text-muted">No notifications</span></li>';
                document.getElementById('notifCount').style.display = 'none';
              } else {
                data.forEach(n => {
                  dropdown.innerHTML += `<li><span class="dropdown-item${n.is_read ? '' : ' fw-bold'}">${n.message}</span></li>`;
                });
                document.getElementById('notifCount').textContent = data.filter(n=>!n.is_read).length;
                document.getElementById('notifCount').style.display = '';
              }
            });
        }
        document.getElementById('notifBellIcon').addEventListener('click', function() {
          fetch('notifications.php?mark_read=1').then(()=>loadNotifications());
        });
        window.onload = loadNotifications;
        </script>
      </div>
      <span id="themeToggle" class="fs-4 text-white" role="button" title="Toggle Theme">🌙</span>
      <form method="GET" action="" class="d-inline">
        <button name="logout" class="btn btn-outline-danger">Logout</button>
      </form>
      <a href="ask.php" class="btn btn-success">Ask Question</a>
    </div>
  </div>
</nav>

<div class="container my-4 shadow-lg">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="btn-group" role="group">
      <button class="btn btn-outline-secondary" onclick="window.location.href='index.php'">Newest</button>
      <button class="btn btn-outline-secondary" onclick="window.location.href='index.php?unanswered=1'">Unanswered</button>
    </div>
    <small class="text-muted">Showing latest questions</small>
  </div>

  <div id="questionList" class="question-list">
    <?php if ($result->num_rows === 0): ?>
      <p class="text-center text-muted">No questions found.</p>
    <?php else: ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card question p-3">
          <a href="answer.php?id=<?= $row['id'] ?>" class="question-title"><?= htmlspecialchars($row['title']) ?></a>
          <?= nl2br(htmlspecialchars(strip_tags($row['description']))) ?>

          <?php if ($row['tags']): ?>
            <div class="mb-2">
              <?php foreach (explode(',', $row['tags']) as $tag): ?>
                <a href="index.php?tag=<?= urlencode($tag) ?>" class="badge tag"><?= htmlspecialchars($tag) ?></a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <small class="text-muted">
            Asked by <strong><?= htmlspecialchars($row['username']) ?></strong> on <?= date('M j, Y', strtotime($row['created_at'])) ?>
          </small>

          <div class="mt-2">
            <span class="vote-btn btn btn-sm btn-outline-success" onclick="vote(<?= $row['id'] ?>, 'up')">⬆️ <?= $row['upvotes'] ?></span>
            <span class="vote-btn btn btn-sm btn-outline-danger" onclick="vote(<?= $row['id'] ?>, 'down')">⬇️ <?= $row['downvotes'] ?></span>
            <a href="answer.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-info">Answer</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>

  <nav class="mt-4">
    <ul class="pagination justify-content-center">
      <li class="page-item"><a class="page-link bg-dark text-light" href="#">1</a></li>
      <li class="page-item"><a class="page-link bg-dark text-light" href="#">2</a></li>
      <li class="page-item"><a class="page-link bg-dark text-light" href="#">Next</a></li>
    </ul>
  </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function vote(questionId, type) {
  fetch('vote.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ question_id: questionId, vote_type: type })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Vote recorded!');
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => {
    console.error('Vote error:', err);
    alert('An error occurred whilea voting.');
  });
}
document.getElementById('themeToggle').addEventListener('click', function() {
  document.body.classList.toggle('night-mode');
  this.textContent = document.body.classList.contains('night-mode') ? '☀️' : '🌙';
});
</script>
<canvas id="snowCanvas"></canvas>
<script>
const canvas = document.getElementById('snowCanvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight; 
const snowflakes = [];
function createSnowflake() {
  const size = Math.random() * 3 + 2;
  const x = Math.random() * canvas.width;
  const y = Math.random() * canvas.height;
  const speed = Math.random() * 1 + 0.5;
  snowflakes.push({ x, y, size, speed });
}
function drawSnowflakes() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
  ctx.beginPath();
  snowflakes.forEach(s => {
    ctx.moveTo(s.x, s.y);
    ctx.arc(s.x, s.y, s.size, 0, Math.PI * 2);
    s.y += s.speed;
    if (s.y > canvas.height) {
      s.y = 0;
      s.x = Math.random() * canvas.width;
    }
  });
  ctx.fill();
}
setInterval(() => {
  if (snowflakes.length < 100) createSnowflake();
  drawSnowflakes();
}, 50);
window.addEventListener('resize', () => {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
});
</script>
</body>
</html>
<?php
$mysqli->close();
?>
