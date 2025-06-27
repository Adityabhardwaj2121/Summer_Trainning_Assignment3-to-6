<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['userid'];

$stmt_user = $con->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #000000;
            color: #ffffff;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #1c1c1c;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(255, 255, 255, 0.1);
            position: fixed;
        }

        .sidebar .profile-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
            border: 2px solid #ffffff;
        }

        .sidebar h3 {
            color: #ffffff;
            font-size: 18px;
            font-weight: normal;
        }

        .sidebar hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin: 20px 0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #ffffff;
            padding: 12px 15px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 6px;
            margin: 5px 0;
            transition: background-color 0.2s;
        }

        .sidebar a:hover {
            background-color: #333333;
        }

        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            background-color: #000000;
        }

        .post-section {
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .post-section h2 {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .post-form {
            margin-bottom: 20px;
        }

        .post-form textarea {
            width: 100%;
            background-color: #333333;
            color: #ffffff;
            border: 1px solid #cccccc;
            border-radius: 6px;
            padding: 10px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
            outline: none;
        }

        .post-form textarea::placeholder {
            color: #cccccc;
        }

        .post-form textarea:focus {
            border-color: #ffffff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
        }

        .post-form button {
            background-color: #00cc00;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            width: 100%;
        }

        .post-form button:hover {
            background-color: #009900;
        }

        #postMessage {
            color: #00cc00;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }

        .post-filter {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .post-filter button {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .post-filter button:hover {
            background-color: #333333;
        }

        .post-filter button.active {
            background-color: #ffffff;
            color: #000000;
        }

        .post-feed {
            margin-top: 20px;
        }

        .post-card {
            background-color: #333333;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .post-info {
            display: flex;
            flex-direction: column;
        }

        .post-info strong {
            color: #ffffff;
            font-size: 16px;
        }

        .post-info em {
            color: #cccccc;
            font-size: 14px;
            font-style: normal;
        }

        .post-info span {
            color: #ffffff;
            font-size: 14px;
            margin-top: 5px;
        }

        .no-posts {
            color: #cccccc;
            font-size: 16px;
            text-align: center;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 200px;
                padding: 20px;
            }

            .post-section {
                min-width: 100%;
            }

            .post-filter {
                flex-direction: column;
                align-items: stretch;
            }

            .post-filter button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: 180px;
            }

            .content {
                margin-left: 180px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="profile-section">
            <img src="Uploads/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile">
            <h3><?= htmlspecialchars($user['name']) ?></h3>
        </div>
        <hr>
        <a href="dashboard.php"><i class="fas fa-home"></i> Home</a>
        <a href="my_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="friends.php"><i class="fas fa-users"></i> Friends</a>
        <a href="chat.php"><i class="fas fa-comment"></i> Chat</a>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        <a href="view_posts.php"><i class="fas fa-file-alt"></i> Post</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="content">
        <div class="post-section">
            <h2>Post Something</h2>
            <form id="postForm" class="post-form">
                <textarea name="content" placeholder="What's on your mind?" required></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i> Post</button>
            </form>
            <div id="postMessage"></div>
            <h2>Post Feed</h2>
            <div class="post-filter">
                <button onclick="loadPosts('all')" class="active">All Posts</button>
                <button onclick="loadPosts('friends')">Friends' Posts</button>
            </div>
            <div id="postFeed" class="post-feed"></div>
        </div>
    </div>

    <script>
        document.getElementById("postForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch("submit_post.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById("postMessage").innerText = data;
                document.getElementById("postForm").reset();
                loadPosts('all');
            });
        });

        function loadPosts(type) {
            const buttons = document.querySelectorAll(".post-filter button");
            buttons.forEach(btn => btn.classList.remove("active"));
            event.target.classList.add("active");

            fetch("load_posts.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "type=" + type
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById("postFeed").innerHTML = data;
            });
        }

        loadPosts('all');
    </script>
</body>
</html>