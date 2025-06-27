<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

$userid = $_SESSION['userid'];

$stmt_user = $con->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt_user->bind_param("i", $userid);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

$stmt_notifications = $con->prepare("SELECT message, timestamp, 'general' AS type FROM notifications WHERE user_id = ? AND seen = FALSE");
$stmt_notifications->bind_param("i", $userid);
$stmt_notifications->execute();
$result_notifications = $stmt_notifications->get_result();

$stmt_post_notifications = $con->prepare("
    SELECT p.content AS message, p.timestamp, u.name, 'post' AS type
    FROM post_notifications pn
    JOIN posts p ON pn.post_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE pn.user_id = ? AND pn.seen = FALSE
");
$stmt_post_notifications->bind_param("i", $userid);
$stmt_post_notifications->execute();
$result_post_notifications = $stmt_post_notifications->get_result();

$all_notifications = [];
while ($row = $result_notifications->fetch_assoc()) {
    $all_notifications[] = $row;
}
while ($row = $result_post_notifications->fetch_assoc()) {
    $all_notifications[] = $row;
}

usort($all_notifications, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

$con->query("UPDATE notifications SET seen = TRUE WHERE user_id = $userid");
$con->query("UPDATE post_notifications SET seen = TRUE WHERE user_id = $userid");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
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

        .notifications-section {
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .notifications-section h3 {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .notification-card {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            background-color: #333333;
        }

        .notification-card.general-notification {
            border: 1px solid #cccccc;
        }

        .notification-card.post-notification {
            border: 1px solid #ff9900;
        }

        .notification-icon {
            margin-right: 10px;
            font-size: 18px;
            color: #ffffff;
        }

        .notification-info {
            flex-grow: 1;
        }

        .notification-info strong {
            color: #ffffff;
            font-size: 16px;
            display: block;
        }

        .notification-info span {
            color: #cccccc;
            font-size: 14px;
        }

        .no-notifications {
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

            .notifications-section {
                min-width: 100%;
            }

            .notification-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
        <div class="notifications-section">
            <h3>Notifications</h3>
            <?php if (count($all_notifications) > 0): ?>
                <?php foreach ($all_notifications as $row): ?>
                    <div class="notification-card <?= $row['type'] === 'general' ? 'general-notification' : 'post-notification' ?>">
                        <i class="notification-icon fas <?= $row['type'] === 'general' ? 'fa-bell' : 'fa-file-alt' ?>"></i>
                        <div class="notification-info">
                            <?php if ($row['type'] === 'post'): ?>
                                <strong><?= htmlspecialchars($row['name']) ?> posted:</strong>
                                <span><?= htmlspecialchars($row['message']) ?></span>
                            <?php else: ?>
                                <strong><?= htmlspecialchars($row['message']) ?></strong>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($row['timestamp']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-notifications">No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>