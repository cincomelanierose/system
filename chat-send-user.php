<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO chat_logs (user_id, message, sender, sent_at) VALUES (?, ?, 'user', NOW())");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->bind_param("is", $_SESSION['user_id'], $message);
    $stmt->execute();
    $stmt->close();
}
