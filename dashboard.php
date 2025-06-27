<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

include "config.php";
$userid = $_SESSION['userid'];

$stmt = $con->prepare("SELECT name, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $userid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
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

        .content h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        .welcome-box {
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            max-width: 500px;
        }

        .welcome-box p {
            color: #ffffff;
            font-size: 16px;
        }

       
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 200px;
                padding: 20px;
            }

            .content h1 {
                font-size: 24px;
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
    <a href="#"><i class="fas fa-home"></i> Home</a>
    <a href="my_profile.php"><i class="fas fa-user"></i> Profile</a>
    <a href="friends.php"><i class="fas fa-users"></i> Friends</a>
    <a href="chat.php"><i class="fas fa-comment"></i> Chat</a>
    <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
    <a href="view_posts.php"><i class="fas fa-file-alt"></i> Post</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
    <div class="welcome-box">
        <p>Your dashboard is ready! Click the links on the left to explore.</p>
    </div>
</div>

</body>
</html>