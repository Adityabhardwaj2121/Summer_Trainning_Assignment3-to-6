<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid']) || !isset($_POST['content'])) {
    exit("Unauthorized or invalid data.");
}

$user_id = $_SESSION['userid'];
$content = trim($_POST['content']);

if ($content !== "") {
  
    $stmt = $con->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $content);
    
    if ($stmt->execute()) {
        echo "Posted successfully";

        
        $post_id = $con->insert_id;

        
        $friendQuery = $con->prepare("
            SELECT receiver_id AS friend_id FROM friends WHERE sender_id = ? AND status = 'accepted'
            UNION
            SELECT sender_id AS friend_id FROM friends WHERE receiver_id = ? AND status = 'accepted'
        ");
        $friendQuery->bind_param("ii", $user_id, $user_id);
        $friendQuery->execute();
        $friendsResult = $friendQuery->get_result();

       
        $notifyStmt = $con->prepare("INSERT INTO post_notifications (user_id, post_id) VALUES (?, ?)");
        while ($row = $friendsResult->fetch_assoc()) {
            $friend_id = $row['friend_id'];
            $notifyStmt->bind_param("ii", $friend_id, $post_id);
            $notifyStmt->execute();
        }

    } else {
        echo "Error posting content.";
    }

} else {
    echo "Post cannot be empty.";
}
?>
