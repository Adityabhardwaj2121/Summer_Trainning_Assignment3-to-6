<?php
session_start();
include "config.php";


if (!isset($_SESSION['userid']) || !isset($_POST['friend_id']) || !isset($_POST['message']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$sender = $_SESSION['userid'];
$receiver = intval($_POST['friend_id']);
$message = trim($_POST['message']);


if (empty($message) || strlen($message) > 1000) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is empty or too long']);
    exit;
}


$stmt = $con->prepare("
    SELECT 1 FROM friends 
    WHERE (sender_id = ? AND receiver_id = ? OR sender_id = ? AND receiver_id = ?) 
    AND status = 'accepted'
");
$stmt->bind_param("iiii", $sender, $receiver, $receiver, $sender);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'You are not friends with this user']);
    exit;
}


$stmt = $con->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $sender, $receiver, $message);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send message']);
}

$stmt->close();
$con->close();
?>