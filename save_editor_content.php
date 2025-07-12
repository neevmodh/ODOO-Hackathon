<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $html = $_POST['content'] ?? '';
  echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Preview</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head><body class='p-5 bg-dark text-white'>
        <h2>📝 Submitted Content</h2>
        <div class='border bg-light text-dark p-3 mt-3'>$html</div>
        <a class='btn btn-outline-light mt-4' href='editor.php'>← Back to Editor</a>
        </body></html>";
} else {
  header("Location: editor.php");
}
