// Login validation
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  if (email === "admin@stackit.com" && password === "admin123") {
    showPopup("✅ Login successful! Redirecting...", "success");
    setTimeout(() => {
      window.location.href = "index.php";
    }, 1500);
  } else {
    showPopup("❌ Invalid email or password!", "error");
  }
});

// Guest login
document.getElementById("guestBtn").addEventListener("click", function () {
  showPopup("👤 Logging in as Guest...", "success");
  setTimeout(() => {
    window.location.href = "index.php";
  }, 1200);
});

// Show popup
function showPopup(message, type) {
  const popup = document.createElement("div");
  popup.className = `popup ${type}`;
  popup.innerText = message;

  document.body.appendChild(popup);

  setTimeout(() => {
    popup.classList.add("show");
  }, 50);

  setTimeout(() => {
    popup.classList.remove("show");
    popup.remove();
  }, 2500);
}

// Snowfall animation remains unchanged
const canvas = document.getElementById('snowCanvas');
const ctx = canvas.getContext('2d');

let width = window.innerWidth;
let height = window.innerHeight;
canvas.width = width;
canvas.height = height;

let snowflakes = [];

function createSnowflakes() {
  const x = Math.random() * width;
  const y = Math.random() * height;
  const radius = Math.random() * 4 + 1;
  const speed = Math.random() * 1 + 0.5;
  const opacity = Math.random();
  snowflakes.push({ x, y, radius, speed, opacity });
}

function drawSnowflakes() {
  ctx.clearRect(0, 0, width, height);
  ctx.fillStyle = 'white';
  ctx.beginPath();
  for (let flake of snowflakes) {
    ctx.globalAlpha = flake.opacity;
    ctx.moveTo(flake.x, flake.y);
    ctx.arc(flake.x, flake.y, flake.radius, 0, Math.PI * 2);
  }
  ctx.fill();
  moveSnowflakes();
}

function moveSnowflakes() {
  for (let flake of snowflakes) {
    flake.y += flake.speed;
    if (flake.y > height) {
      flake.y = 0;
      flake.x = Math.random() * width;
    }
  }
}

function update() {
  drawSnowflakes();
  requestAnimationFrame(update);
}

function init() {
  for (let i = 0; i < 100; i++) {
    createSnowflakes();
  }
  update();
}

init();

window.addEventListener('resize', () => {
  width = window.innerWidth;
  height = window.innerHeight;
  canvas.width = width;
  canvas.height = height;
});
