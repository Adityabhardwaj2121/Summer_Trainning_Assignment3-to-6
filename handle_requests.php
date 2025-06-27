<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid']) || !isset($_POST['from_id']) || !isset($_POST['action'])) {
    echo "Invalid request.";
    exit;
}

$user_id = $_SESSION['userid'];
$from_id = intval($_POST['from_id']);
$action = $_POST['action'];

if (!in_array($action, ['accept', 'block'])) {
    echo "Invalid action.";
    exit;
}

$new_status = $action === 'accept' ? 'accepted' : 'blocked';


$stmt = $con->prepare("UPDATE friends 
    SET status = ? 
    WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'");
$stmt->bind_param("sii", $new_status, $from_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo ucfirst($action) . "ed successfully.";
} else {
    echo "Action failed or already processed.";
}
?>
