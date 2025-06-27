<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid']) || !isset($_POST['type'])) {
    exit("Unauthorized or missing data");
}

$user_id = $_SESSION['userid'];
$type = $_POST['type'];

if ($type === 'all') {
    $query = "SELECT posts.*, users.name FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.timestamp DESC";
} else {
   
    $query = "
        SELECT posts.*, users.name 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE posts.user_id IN (
            SELECT CASE 
                WHEN sender_id = ? THEN receiver_id
                WHEN receiver_id = ? THEN sender_id
            END
            FROM friends 
            WHERE (sender_id = ? OR receiver_id = ?) AND status = 'accepted'
        )
        ORDER BY posts.timestamp DESC
    ";
}

$stmt = $con->prepare($query);

if ($type === 'all') {
    $stmt->execute();
} else {
    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
}

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px;'>
        <strong>{$row['name']}</strong> <em>{$row['timestamp']}</em><br>
        {$row['content']}
    </div>";
}
?>
