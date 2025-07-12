const mockQuestions = [
  {
    title: "How to join 2 columns in a dataset in SQL?",
    description: "I want to combine First Name and Last Name into a single column...",
    tags: ["SQL", "Beginner"],
    user: "User123",
    time: "2 mins ago"
  },
  {
    title: "What is the difference between == and === in JavaScript?",
    description: "Can someone explain the difference with examples...",
    tags: ["JavaScript", "Operators"],
    user: "CoderX",
    time: "10 mins ago"
  }
];
function vote(questionId, type) {
  fetch('vote.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ question_id: questionId, vote_type: type })
  })
  .then(res => {
    if (!res.ok) throw new Error('Network response was not OK');
    return res.json(); // this throws if not valid JSON
  })
  .then(data => {
    if (data.success) {
      alert(data.message);
      location.reload();
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => {
    alert('Request error: ' + err.message);
  });
}

function renderQuestions(questions) {
  const list = document.getElementById("questionList");
  list.innerHTML = "";
  questions.forEach(q => {
    const tags = q.tags.map(tag => `<span class="badge tag text-white" data-tag="${tag}">${tag}</span>`).join(" ");
    list.innerHTML += `
      <div class="card question p-3">
        <div class="d-flex justify-content-between align-items-start">
          <a href="#" class="question-title">${q.title}</a>
          <div>
            <button class="btn btn-sm btn-outline-success" title="Upvote">⬆</button>
            <button class="btn btn-sm btn-outline-danger" title="Downvote">⬇</button>
          </div>
        </div>
        <p class="mt-2">${q.description}</p>
        <div class="d-flex justify-content-between align-items-center">
          <div>${tags}</div>
          <small>Asked by <strong>${q.user}</strong> • ${q.time}</small>
        </div>
      </div>
    `;
  });

  document.querySelectorAll('.badge.tag').forEach(el => {
    el.addEventListener('click', () => {
      const tag = el.dataset.tag.toLowerCase();
      const filtered = mockQuestions.filter(q =>
        q.tags.some(t => t.toLowerCase() === tag)
      );
      renderQuestions(filtered);
    });
  });
}

function filterQuestions(type) {
  if (type === "unanswered") {
    alert("Showing unanswered questions (mock)");
  }
  renderQuestions(mockQuestions);
}

function searchQuestions() {
  const query = document.getElementById("searchInput").value.toLowerCase();
  const filtered = mockQuestions.filter(q =>
    q.title.toLowerCase().includes(query) ||
    q.description.toLowerCase().includes(query)
  );
  renderQuestions(filtered);
}

document.getElementById("themeToggle").addEventListener("click", () => {
  document.body.classList.toggle("night-mode");
  localStorage.setItem("theme", document.body.classList.contains("night-mode") ? "night" : "day");
});

function loadTheme() {
  const theme = localStorage.getItem("theme");
  if (theme === "night") document.body.classList.add("night-mode");
}

// ❄️ Snow Animation
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

loadTheme();
createSnowflakes();
animateSnow();
renderQuestions(mockQuestions);
