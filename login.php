<?php
session_start();
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'odoo');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanitize and fetch input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate user
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Authentication successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php"); // redirect to user dashboard
            exit();
        } else {
            $loginError = "Invalid email or password.";
        }
    } else {
        $loginError = "Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>StackIt Login</title>
  <link rel="stylesheet" href="login.css" />
  <style>
    .popup {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
  animation: fadeIn 0.3s ease-in-out;
}

.popup-content {
  background-color: #fff;
  color: #333;
  margin: 15% auto;
  padding: 20px 30px;
  border-radius: 12px;
  width: 300px;
  text-align: center;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  animation: slideDown 0.4s ease-out;
  font-family: 'Segoe UI', sans-serif;
}

.popup-content.success {
  border-left: 5px solid #4CAF50;
}

.popup-content.error {
  border-left: 5px solid #f44336;
}

@keyframes slideDown {
  from {
    transform: translateY(-50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
  .popup-content p {
    margin: 0;
    font-size: 16px;
  }
  #snowCanvas {
  position: fixed;
  top: 0;
  left: 0;
  pointer-events: none;
  z-index: 0;
}

  </style>
</head>

<body>
  <canvas id="snowCanvas"></canvas>
  <div class="login-container">
    <form class="login-form" method="POST" action="login.php">
      <h2>Welcome to <span class="brand">StackIt</span></h2>
      <p class="subtitle">Minimal Q&A Forum</p>

      <?php if (!empty($loginError)): ?>
        <p class="error-message"><?= htmlspecialchars($loginError) ?></p>
      <?php endif; ?>

      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />

      <button type="submit" id="loginBtn">Login</button>

      <button type="button" id="guestBtn" onclick="window.location.href='guest.php'">Login as Guest</button>

      <p class="register-link">Don't have an account? <a href="register.php">Register</a></p>
    </form>
    <!-- Modal Popup -->
<div id="popup" class="popup">
  <div class="popup-content">
    <p id="popup-message"></p>
  </div>
</div>

  </div>
  <script>
  function showPopup(message, isSuccess = true, redirectURL = null) {
    const popup = document.getElementById('popup');
    const messageBox = document.getElementById('popup-message');

    messageBox.innerText = message;
    popup.style.display = 'block';

    const content = popup.querySelector('.popup-content');
    content.className = 'popup-content ' + (isSuccess ? 'success' : 'error');

    // Auto-hide and redirect if successful
    if (isSuccess && redirectURL) {
      setTimeout(() => {
        popup.style.display = 'none';
        window.location.href = redirectURL;
      }, 1200);
    } else {
      setTimeout(() => {
        popup.style.display = 'none';
      }, 2500);
    }
  }
</script>
<script>
  // Snow animation logic
  const canvas = document.getElementById("snowCanvas");
  const ctx = canvas.getContext("2d");

  let width = window.innerWidth;
  let height = window.innerHeight;

  canvas.width = width;
  canvas.height = height;

  let snowflakes = [];

  function createSnowflakes() {
    for (let i = 0; i < 100; i++) {
      snowflakes.push({
        x: Math.random() * width,
        y: Math.random() * height,
        radius: Math.random() * 4 + 1,
        speed: Math.random() * 1 + 0.5,
        drift: Math.random() * 0.5 - 0.25
      });
    }
  }

  function drawSnowflakes() {
    ctx.clearRect(0, 0, width, height);
    ctx.fillStyle = "#fff";
    ctx.beginPath();

    for (let flake of snowflakes) {
      ctx.moveTo(flake.x, flake.y);
      ctx.arc(flake.x, flake.y, flake.radius, 0, Math.PI * 2);
    }

    ctx.fill();
    moveSnowflakes();
  }

  function moveSnowflakes() {
    for (let flake of snowflakes) {
      flake.y += flake.speed;
      flake.x += flake.drift;

      if (flake.y > height) {
        flake.y = -flake.radius;
        flake.x = Math.random() * width;
      }

      if (flake.x > width || flake.x < 0) {
        flake.x = Math.random() * width;
      }
    }
  }

  function animateSnow() {
    drawSnowflakes();
    requestAnimationFrame(animateSnow);
  }

  createSnowflakes();
  animateSnow();

  window.addEventListener('resize', () => {
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = width;
    canvas.height = height;
  });
</script>
  

</body>
</html>
