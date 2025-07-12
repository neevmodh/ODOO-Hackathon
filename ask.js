const canvas = document.getElementById('snowCanvas');
const ctx = canvas.getContext('2d');
let width = window.innerWidth;
let height = window.innerHeight;
canvas.width = width;
canvas.height = height;

let snowflakes = [];

function createSnowflakes() {
  for (let i = 0; i < 100; i++) {
    const x = Math.random() * width;
    const y = Math.random() * height;
    const radius = Math.random() * 4 + 1;
    const speed = Math.random() * 1 + 0.5;
    const opacity = Math.random();
    snowflakes.push({ x, y, radius, speed, opacity });
  }
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

function animateSnow() {
  drawSnowflakes();
  requestAnimationFrame(animateSnow);
}

window.addEventListener('resize', () => {
  width = window.innerWidth;
  height = window.innerHeight;
  canvas.width = width;
  canvas.height = height;
});

createSnowflakes();
animateSnow();
