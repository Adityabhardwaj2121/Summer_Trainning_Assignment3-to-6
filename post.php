<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

$userid = $_SESSION['userid'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Post</title>
</head>
<body>
    <h2>Create Post</h2>
    <form method="POST" action="submit_post.php">
        <textarea name="content" rows="4" cols="50" placeholder="Write your thoughts..."></textarea><br>
        <button type="submit">Post</button>
    </form>
</body>
</html>
