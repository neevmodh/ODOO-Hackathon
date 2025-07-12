# ODOO-Hackathon Submission

## 🚀 Problem Statement 2: StackIt – A Minimal Q&A Forum Platform

---

### 👥 Team Details

- **Team Name:** Team 0598  
- **Team Leader:** Neev Modh  
  - 📧 Email: neevmodh205@gmail.com  
  - 📞 Phone: 7016111267

---

### 👨‍💻 Team Members

1. **Jaimin Parmar**  
   - 📧 Email: jaiminparmar2687@gmail.com  
   - 📞 Phone: 6354378559

2. **Rishika Chaudhary**  
   - 📧 Email: rishikachaudharyyyy@gmail.com  
   - 📞 Phone: 8200427330

3. **Raj Odedra**  
   - 📧 Email: rajodedra804@gmail.com  
   - 📞 Phone: 9664502656

---

### 🧑‍⚖️ Reviewer
- **Name:** Ansari Mahamadasif Anvarali (maan)

## 🚀 Project Purpose

*StackIt* is a lightweight, visually appealing Q&A platform inspired by Stack Overflow.  
It supports rich text question/answer posting, user interaction via voting, tag-based filtering, and notification simulation — all wrapped in a modern UI with dark/light theme support and snowfall animation.

---

## 🗂 Features

### ✅ Core Functionalities
- *User Authentication (Session-based)*
  - Simulated login (can be integrated with real login later)
- *Post Questions*
  - Rich text editor (Quill.js + Emoji support)
  - Tag input and management
- *Answer Questions*
  - Submit text-based answers per question
- *Voting System*
  - Upvote/downvote answers (one per user per answer)
- *Tagging*
  - Tags are created dynamically and linked to questions
- *Filter & Search*
  - Filter questions by tags and keyword
- *Notification Bell (Simulated)*
- *Night Mode Toggle*
- *Snowfall Animation* ❄

---

## 💾 Database Schema

### 📌 users
| Column      | Type                       | Description                  |
|-------------|----------------------------|------------------------------|
| id          | INT (Primary Key)          | User ID                      |
| username    | VARCHAR(50), UNIQUE, NOT NULL | Username                  |
| email       | VARCHAR(100), UNIQUE, NOT NULL | Email address             |
| password    | VARCHAR(255) NOT NULL      | Hashed password              |
| role        | ENUM('guest','user','admin') | Default 'user'          |
| created_at  | TIMESTAMP DEFAULT NOW()    | Account creation time        |

### 📌 questions
| Column      | Type          | Description                        |
|-------------|---------------|------------------------------------|
| id          | INT (PK)      | Question ID                        |
| user_id     | INT (FK)      | Foreign key to users               |
| title       | VARCHAR(255)  | Question title                     |
| description | TEXT          | Rich HTML content                  |
| created_at  | TIMESTAMP     | Creation timestamp                 |

### 📌 answers
| Column      | Type          | Description                         |
|-------------|---------------|-------------------------------------|
| id          | INT (PK)      | Answer ID                           |
| question_id | INT (FK)      | Related question                    |
| user_id     | INT (FK)      | Answered by                         |
| description | TEXT          | Answer content                      |
| is_accepted | BOOLEAN       | Is accepted answer                  |
| created_at  | TIMESTAMP     | Posted time                         |

### 📌 tags
| Column  | Type         | Description        |
|---------|--------------|--------------------|
| id      | INT (PK)     | Tag ID             |
| name    | VARCHAR(50)  | Tag name (unique)  |

### 📌 question_tags (Many-to-Many)
| Column       | Type | Description               |
|--------------|------|---------------------------|
| question_id  | INT  | FK to questions           |
| tag_id       | INT  | FK to tags                |

### 📌 votes
| Column     | Type                        | Description                         |
|------------|-----------------------------|-------------------------------------|
| id         | INT (PK)                    | Vote ID                             |
| answer_id  | INT (FK)                    | Vote is linked to an answer         |
| user_id    | INT (FK)                    | Who voted                           |
| vote_type  | ENUM('up', 'down')          | Vote type                           |
| created_at | TIMESTAMP                   | Vote time                           |

### 📌 notifications
| Column    | Type      | Description              |
|-----------|-----------|--------------------------|
| id        | INT (PK)  | Notification ID          |
| user_id   | INT (FK)  | Recipient user           |
| message   | TEXT      | Message                  |
| is_read   | BOOLEAN   | Read status              |
| created_at| TIMESTAMP | Time                     |

---

## 📁 File Structure Overview

StackIt/
├── index.php # Homepage - list of questions
├── ask.php # Form to ask a new question
├── answer.php # Question details + submit answers
├── editor.php # Rich text editor playground
├── save_editor_content.php # Preview saved content from editor
├── submit_question.php # Handles question submission
├── vote.php # Handles vote logic
├── bg.jpg # Background image
├── editor.css # Quill editor styles
├── editor.js # Editor + snow animation script
├── style.css # Global styling (merged with inline in index.php)
├── odoo.sql # SQL schema (optional file)
└── README.md # You're reading it!



---

## ⚙ Setup Instructions

### ✅ Requirements
- *XAMPP / LAMP / WAMP* (Apache + MySQL)
- *PHP 7.x+*
- *MySQL*
- *Modern browser (Chrome, Edge, Firefox)*

---

### 🔧 Installation Steps

1. ✅ *Clone the repository*
```bash
git clone https://github.com/your-username/StackIt.git
✅ Import the database

Open phpMyAdmin

Run the SQL schema from the odoo.sql or directly paste in the SQL from this README

✅ Start Apache and MySQL

Using XAMPP control panel

✅ Navigate to


http://localhost/StackIt/index.php
