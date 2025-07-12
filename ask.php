<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>StackIt – Ask FAQ</title>

  <!-- Styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/quill-emoji@0.1.7/dist/quill-emoji.css" rel="stylesheet" />
  <link rel="stylesheet" href="editor.css" />
</head>
<body>

<canvas id="snowCanvas"></canvas>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark px-4">
  <a class="navbar-brand" href="index.php">← StackIt</a>
</nav>

<!-- Ask Form -->
<div class="container my-5 shadow-lg">
  <h3 class="text-info mb-4">❓ Ask a Question</h3>

  <form id="askForm" method="POST" action="submit_question.php">
    <!-- Title -->
    <div class="mb-3">
      <label for="title" class="form-label text-light">🧠 Question Title</label>
      <input type="text" class="form-control" id="title" name="title" placeholder="E.g., What is JWT?" required>
    </div>

    <!-- Tags -->
    <div class="mb-3">
      <label for="tags" class="form-label text-light">🏷️ Tags</label>
      <select class="form-select" id="tags" name="tags[]" multiple>
        <option value="React">React</option>
        <option value="JWT">JWT</option>
        <option value="PHP">PHP</option>
        <option value="Bootstrap">Bootstrap</option>
        <option value="Quill">Quill</option>
      </select>
    </div>

    <!-- Quill Toolbar -->
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

    <!-- Editor -->
    <div id="editor" class="mb-3"></div>
    <input type="hidden" name="description" id="description" />

    <button type="submit" class="btn btn-success">🚀 Submit Question</button>
  </form>
</div>

<!-- Scripts -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill-emoji@0.1.7/dist/quill-emoji.min.js"></script>
<script>
// Quill Setup
const quill = new Quill('#editor', {
  modules: {
    toolbar: '#toolbar',
    "emoji-toolbar": true,
    "emoji-textarea": false,
    "emoji-shortname": true
  },
  theme: 'snow'
});

document.getElementById("askForm").onsubmit = function () {
  document.getElementById("description").value = quill.root.innerHTML;
};

// Snowfall Animation
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
</script>
</body>
</html>
