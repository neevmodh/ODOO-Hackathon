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
