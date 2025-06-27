<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid']) || !isset($_POST['to_id'])) {
    echo "Session or target missing.";
    exit;
}

$sender = $_SESSION['userid'];
$receiver = intval($_POST['to_id']);


if ($sender == $receiver) {
    echo "You cannot send a friend request to yourself.";
    exit;
}


$check = $con->prepare("SELECT * FROM friends 
    WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)");
$check->bind_param("iiii", $sender, $receiver, $receiver, $sender);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    echo "Friend request already exists.";
    exit;
}


$insert = $con->prepare("INSERT INTO friends(sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
$insert->bind_param("ii", $sender, $receiver);
if ($insert->execute()) {
 
    $msg = $_SESSION['name'] . " sent you a friend request.";
    $notify = $con->prepare("INSERT INTO notifications(user_id, message) VALUES (?, ?)");
    $notify->bind_param("is", $receiver, $msg);
    $notify->execute();

    echo "Friend request sent and notification created.";
} else {
    echo "Database error: " . $insert->error;
}
?>
