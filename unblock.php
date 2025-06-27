<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    echo "Error: You are not logged in.";
    exit;
}

$userid = (int)$_SESSION['userid'];
$blocked_user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;


if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "Error: Invalid CSRF token.";
    exit;
}

if ($blocked_user_id <= 0) {
    echo "Error: Invalid user ID.";
    exit;
}


error_log(date('Y-m-d H:i:s') . " - Unblock attempt: User $userid tried to unblock $blocked_user_id from IP {$_SERVER['REMOTE_ADDR']}");


$check_stmt = $con->prepare("
    SELECT 1 
    FROM friends 
    WHERE (sender_id = ? AND receiver_id = ? AND status = 'blocked') 
       OR (sender_id = ? AND receiver_id = ? AND status = 'blocked')
");
if (!$check_stmt) {
    error_log("Check prepare failed: " . $con->error);
    echo "Error: Database prepare error.";
    $con->close();
    exit;
}
$check_stmt->bind_param("iiii", $userid, $blocked_user_id, $blocked_user_id, $userid);
$check_stmt->execute();
if ($check_stmt->get_result()->num_rows === 0) {
    error_log("No blocked relationship found for user $userid and $blocked_user_id");
    echo "Error: No blocked relationship found.";
    $check_stmt->close();
    $con->close();
    exit;
}
$check_stmt->close();


$stmt = $con->prepare("
    DELETE FROM friends 
    WHERE (sender_id = ? AND receiver_id = ? AND status = 'blocked') 
       OR (sender_id = ? AND receiver_id = ? AND status = 'blocked')
");
if (!$stmt) {
    error_log("Delete prepare failed: " . $con->error);
    echo "Error: Database prepare error.";
    $con->close();
    exit;
}

$stmt->bind_param("iiii", $userid, $blocked_user_id, $blocked_user_id, $userid);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "User unblocked successfully.";
    } else {
        error_log("No rows affected: No blocked relationship found for user $userid and $blocked_user_id");
        echo "Error: No blocked relationship found.";
    }
} else {
    error_log("Execute failed: " . $stmt->error);
    echo "Error: Could not unblock user.";
}

$stmt->close();
$con->close();
?>