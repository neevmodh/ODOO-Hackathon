<?php
session_start();
$registerError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'odoo');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $registerError = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $registerError = "Email or username already taken.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const popup = document.getElementById('popup');
                        const messageBox = document.getElementById('popup-message');
                        const content = popup.querySelector('.popup-content');
                        messageBox.innerText = '🎉 Registration successful! Redirecting to login...';
                        content.className = 'popup-content success';
                        popup.style.display = 'block';
                        setTimeout(() => {
                            popup.style.display = 'none';
                            window.location.href = 'login.php';
                        }, 1800);
                    });
                </script>";
            } else {
                $registerError = "Error registering user. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>StackIt Register</title>
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", sans-serif;
      background: url('bg.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .login-container {
      background: rgba(0, 0, 0, 0.75);
      padding: 40px 30px;
      border-radius: 16px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(5px);
      position: relative;
      z-index: 1;
    }

    .login-form h2 {
      color: #ffffff;
      text-align: center;
      margin-bottom: 8px;
      font-size: 1.8rem;
    }

    .brand {
      color: #ff6b81;
    }

    .subtitle {
      color: #ffffff;
      font-size: 1rem;
      text-align: center;
      margin-bottom: 25px;
      opacity: 0.85;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 92%;
      padding: 14px;
      margin-bottom: 18px;
      border: none;
      border-radius: 8px;
      background: #2c2c2c;
      color: #ffffff;
      font-size: 1rem;
      transition: background 0.3s ease;
    }

    input:focus {
      background: #3c3c3c;
      outline: none;
    }

    button[type="submit"],
    #guestBtn {
      width: 100%;
      padding: 14px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    button[type="submit"] {
      background: linear-gradient(135deg, #ff6b81, #ff4757);
      color: white;
      border: none;
    }

    button[type="submit"]:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(255, 107, 129, 0.3);
    }

    #guestBtn {
      margin-top: 12px;
      background: transparent;
      color: #ffffff;
      border: 2px solid #ff6b81;
      font-weight: 500;
    }

    #guestBtn:hover {
      background: #ff6b81;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(255, 107, 129, 0.3);
    }

    .register-link {
      margin-top: 18px;
      text-align: center;
      font-size: 0.95rem;
      color: #ffffff;
      opacity: 0.8;
    }

    .register-link a {
      color: #ff6b81;
      text-decoration: none;
      font-weight: 600;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

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
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .popup-content p {
      margin: 0;
      font-size: 16px;
    }

    .strength {
      margin-top: -12px;
      margin-bottom: 12px;
      font-size: 0.9rem;
      font-weight: 500;
      text-align: left;
      padding-left: 4px;
      transition: color 0.3s ease;
    }

    .strength.weak { color: #ff4757; }
    .strength.medium { color: #ffa502; }
    .strength.strong { color: #2ed573; }

    #snowCanvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
    }
  </style>
</head>
<body>
<canvas id="snowCanvas"></canvas>

<div class="login-container">
  <form class="login-form" method="POST" action="register.php">
    <h2>Create your <span class="brand">StackIt</span> account</h2>
    <p class="subtitle">Minimal Q&A Forum</p>

    <?php if (!empty($registerError)): ?>
      <script>window.onload = () => showPopup("<?= htmlspecialchars($registerError) ?>", false);</script>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Username" required />
    <input type="email" name="email" placeholder="Email" required />
    <input type="password" name="password" id="password" placeholder="Password" required />
    <div id="strengthMessage" class="strength"></div>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />

    <button type="submit">Register</button>
    <button type="button" id="guestBtn" onclick="window.location.href='guest.php'">Continue as Guest</button>

    <p class="register-link">Already have an account? <a href="login.php">Login</a></p>
  </form>

  <!-- Modal Popup -->
  <div id="popup" class="popup">
    <div class="popup-content">
      <p id="popup-message"></p>
    </div>
  </div>
</div>

<script>
  // Password Strength Indicator
  document.getElementById('password').addEventListener('input', function () {
    const pwd = this.value;
    const message = document.getElementById('strengthMessage');

    let strength = 0;
    if (pwd.length >= 6) strength++;
    if (/[A-Z]/.test(pwd)) strength++;
    if (/[0-9]/.test(pwd)) strength++;
    if (/[^A-Za-z0-9]/.test(pwd)) strength++;

    if (pwd.length === 0) {
      message.textContent = '';
      message.className = 'strength';
    } else if (strength <= 1) {
      message.textContent = 'Strength: Weak';
      message.className = 'strength weak';
    } else if (strength <= 3) {
      message.textContent = 'Strength: Medium';
      message.className = 'strength medium';
    } else {
      message.textContent = 'Strength: Strong';
      message.className = 'strength strong';
    }
  });

  function showPopup(message, isSuccess = true) {
    const popup = document.getElementById('popup');
    const messageBox = document.getElementById('popup-message');
    const content = popup.querySelector('.popup-content');
    messageBox.innerText = message;
    content.className = 'popup-content ' + (isSuccess ? 'success' : 'error');
    popup.style.display = 'block';
    setTimeout(() => {
      popup.style.display = 'none';
    }, 2500);
  }

  // Snow animation
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
