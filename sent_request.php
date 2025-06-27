<?php
session_start();
include "config.php";

$sender = $_SESSION['userid'];
$receiver = intval($_POST['friend_id']);
$message = trim($_POST['message']);


$block_check = $con->prepare("SELECT * FROM friends WHERE (
    (sender_id = ? AND receiver_id = ? AND status = 'blocked') OR 
    (sender_id = ? AND receiver_id = ? AND status = 'blocked')
)");
$block_check->bind_param("iiii", $sender, $receiver, $receiver, $sender);
$block_check->execute();
$block_result = $block_check->get_result();

if ($block_result->num_rows > 0) {
    echo "You cannot send messages to this user.";
    exit;
}


$check = $con->prepare("SELECT * FROM friends WHERE (
    (sender_id = ? AND receiver_id = ?) OR 
    (sender_id = ? AND receiver_id = ?)
) AND status = 'accepted'");

$check->bind_param("iiii", $sender, $receiver, $receiver, $sender);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    $stmt = $con->prepare("INSERT INTO messages(sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender, $receiver, $message);
    $stmt->execute();
    echo "Message sent.";
} else {
    echo "You can only message accepted friends.";
}
?>
