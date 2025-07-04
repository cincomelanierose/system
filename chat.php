<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT message, sender FROM chat_logs WHERE user_id = ? ORDER BY sent_at ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'message' => $row['message'],
        'sender' => $row['sender'] // user or medstaff
    ];
}

header('Content-Type: application/json');
echo json_encode($messages);
