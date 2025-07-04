<?php
session_start();
include 'includes/db.php';
include 'includes/session-user-name.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Health Finder - Home</title>
  <link rel="stylesheet" href="style/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
</head>
<body>

  <?php include 'includes/navbar.php'; ?>

  <?php include 'templates/dashboard-main.php'; ?>

  <?php include 'includes/footer.php'; ?>

  <!-- Chat Bubble -->
  <div id="chat-bubble">
    <div class="chat-header" onclick="toggleChat()">💬 Chat</div>
    <div class="chat-body" id="chatBody">
      <div class="chat-messages" id="chatMessages"></div>
      <form id="chatForm">
        <input type="text" name="message" id="chatInput" placeholder="Type message..." autocomplete="off" required />
        <button type="submit">➤</button>
      </form>
    </div>
  </div>

  <style>
    #chat-bubble {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 280px;
      font-family: Arial, sans-serif;
      z-index: 9999;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .chat-header {
      background: #3498db;
      color: #fff;
      padding: 10px 15px;
      border-radius: 10px 10px 0 0;
      cursor: pointer;
      font-weight: bold;
      text-align: center;
    }
    .chat-body {
      display: none;
      background: #fff;
      border: 1px solid #ccc;
      border-top: none;
      max-height: 320px;
      overflow: hidden;
      border-radius: 0 0 10px 10px;
    }
    .chat-messages {
      height: 220px;
      overflow-y: auto;
      padding: 10px;
      font-size: 14px;
    }
    .chat-messages .msg {
      margin-bottom: 10px;
      max-width: 85%;
      padding: 8px 10px;
      border-radius: 10px;
      line-height: 1.4;
    }
    .chat-messages .user {
      background: #d8eaff;
      align-self: flex-end;
      text-align: right;
      margin-left: auto;
    }
    .chat-messages .medstaff {
      background: #e6ecf0;
      margin-right: auto;
    }
    #chatForm {
      display: flex;
      border-top: 1px solid #ccc;
    }
    #chatInput {
      flex: 1;
      padding: 10px;
      border: none;
      font-size: 14px;
    }
    #chatForm button {
      background: #3498db;
      color: white;
      border: none;
      padding: 10px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>

  <script>
    let chatVisible = false;

    function toggleChat() {
      chatVisible = !chatVisible;
      document.querySelector('.chat-body').style.display = chatVisible ? 'block' : 'none';
      if (chatVisible) fetchMessages();
    }

    document.getElementById('chatForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const input = document.getElementById('chatInput');
      fetch('chat-send-user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `message=${encodeURIComponent(input.value)}`
      }).then(() => {
        input.value = '';
        fetchMessages();
      });
    });

    function fetchMessages() {
      fetch('chat-fetch.php')
        .then(res => res.json())
        .then(data => {
          const box = document.getElementById('chatMessages');
          box.innerHTML = '';
          data.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'msg ' + msg.sender;
            div.textContent = msg.message;
            box.appendChild(div);
          });
          box.scrollTop = box.scrollHeight;
        });
    }

    setInterval(() => {
      if (chatVisible) fetchMessages();
    }, 5000);
  </script>

</body>
</html>
