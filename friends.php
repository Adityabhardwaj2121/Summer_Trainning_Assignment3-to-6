<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

$userid = $_SESSION['userid'];


$_SESSION['csrf_token'] = bin2hex(random_bytes(32));


$stmt_user = $con->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt_user->bind_param("i", $userid);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();


$stmt_requests = $con->prepare("
    SELECT u.id, u.name, u.email
    FROM friends f
    JOIN users u ON u.id = f.sender_id
    WHERE f.receiver_id = ? AND f.status = 'pending'
");
$stmt_requests->bind_param("i", $userid);
$stmt_requests->execute();
$result_requests = $stmt_requests->get_result();

$requests = [];
while ($row = $result_requests->fetch_assoc()) {
    $requests[] = $row;
}


$stmt_blocked = $con->prepare("
    SELECT u.id, u.name, u.email, u.profile_pic
    FROM friends f
    JOIN users u ON u.id = f.sender_id
    WHERE f.receiver_id = ? 
    AND f.status = 'blocked'
    AND u.id != ?
");
$stmt_blocked->bind_param("ii", $userid, $userid);
$stmt_blocked->execute();
$result_blocked = $stmt_blocked->get_result();

$blocked_users = [];
while ($row = $result_blocked->fetch_assoc()) {
    $blocked_users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .search-column, .friends-column {
            flex: 1;
            min-width: 300px;
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
        }

        .search-column h3, .friends-column h3 {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .action-buttons button {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-buttons button:hover {
            background-color: #333333;
        }

        .action-buttons .requests-btn {
            background-color: #00cc00;
            border-color: #00cc00;
        }

        .action-buttons .requests-btn:hover {
            background-color: #009900;
        }

        .action-buttons .blocked-btn {
            background-color: #ff4444;
            border-color: #ff4444;
        }

        .action-buttons .blocked-btn:hover {
            background-color: #cc3333;
        }

        #search {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #333333;
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            width: 100%;
            outline: none;
        }

        #search::placeholder {
            color: #000000;
            opacity: 0.7;
        }

        #search:focus {
            border-color: #ffffff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
        }

        #result {
            margin-top: 15px;
            background-color: #333333;
            padding: 10px;
            border-radius: 6px;
            min-height: 50px;
        }

        #result .user-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #1c1c1c;
            border-radius: 6px;
        }

        #result .user-info {
            display: flex;
            flex-direction: column;
        }

        #result .user-info strong {
            color: #ffffff;
            font-size: 16px;
        }

        #result .user-info span {
            color: #cccccc;
            font-size: 14px;
        }

        #result .send-request {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #result .send-request:hover {
            background-color: #333333;
        }

        .friend-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333333;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .friend-info {
            display: flex;
            flex-direction: column;
        }

        .friend-info strong {
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
        }

        .friend-info span {
            color: #cccccc;
            font-size: 14px;
        }

        .no-friends {
            color: #cccccc;
            font-size: 16px;
            text-align: center;
            padding: 20px;
        }

       
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-content h3 {
            color: #ffffff;
            font-size: 22px;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        .modal-content .close {
            float: right;
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
        }

        .request-card, .blocked-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333333;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .request-info, .blocked-info {
            display: flex;
            flex-direction: column;
        }

        .request-info strong, .blocked-info strong {
            color: #ffffff;
            font-size: 16px;
        }

        .request-info span, .blocked-info span {
            color: #cccccc;
            font-size: 14px;
        }

        .blocked-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .request-actions, .blocked-actions {
            display: flex;
            gap: 10px;
        }

        .request-actions button, .blocked-actions button {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 8px 15px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .request-actions button:hover, .blocked-actions button:hover {
            background-color: #333333;
        }

        .request-actions .accept {
            background-color: #00cc00;
            border-color: #00cc00;
        }

        .request-actions .accept:hover {
            background-color: #009900;
        }

        .request-actions .block {
            background-color: #ff4444;
            border-color: #ff4444;
        }

        .request-actions .block:hover {
            background-color: #cc3333;
        }

        .no-requests, .no-blocked {
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
                flex-direction: column;
            }

            .search-column, .friends-column {
                min-width: 100%;
            }

            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .action-buttons button {
                text-align: center;
            }

            #search {
                max-width: 100%;
            }

            .friend-card, #result .user-card, .request-card, .blocked-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            #result .send-request, .request-actions button, .blocked-actions button {
                width: 100%;
                text-align: center;
            }

            .modal-content {
                width: 95%;
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
    <div class="search-column">
        <h3>Search Users</h3>
        <div class="action-buttons">
            <button class="requests-btn" onclick="openModal('friend-requests-modal')"><i class="fas fa-user-plus"></i> View Friend Requests</button>
            <button class="blocked-btn" onclick="openModal('blocked-users-modal')"><i class="fas fa-ban"></i> View Blocked Users</button>
        </div>
        <input type="text" id="search" placeholder="Search by name or email">
        <div id="result"></div>
    </div>
    <div class="friends-column">
        <h3>My Friends</h3>
        <?php
        $stmt = $con->prepare("
            SELECT u.id, u.name, u.email
            FROM friends f
            JOIN users u ON (
                (u.id = f.sender_id AND f.receiver_id = ?)
                OR
                (u.id = f.receiver_id AND f.sender_id = ?)
            )
            WHERE f.status = 'accepted'
            AND u.id != ?
        ");
        $stmt->bind_param("iii", $userid, $userid, $userid);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo "<div class='friend-card'>
                    <div class='friend-info'>
                        <strong>" . htmlspecialchars($row['name']) . "</strong>
                        <span>" . htmlspecialchars($row['email']) . "</span>
                    </div>
                </div>";
            }
        } else {
            echo "<p class='no-friends'>You have no friends yet.</p>";
        }
        ?>
    </div>
</div>


<div id="friend-requests-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('friend-requests-modal')">×</span>
        <h3>Friend Requests</h3>
        <div id="friend-requests-content">
            <?php if (count($requests) > 0): ?>
                <?php foreach ($requests as $row): ?>
                    <div class="request-card" data-id="<?= $row['id'] ?>">
                        <div class="request-info">
                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                            <span><?= htmlspecialchars($row['email']) ?></span>
                        </div>
                        <div class="request-actions">
                            <button class="accept" data-id="<?= $row['id'] ?>">Accept</button>
                            <button class="block" data-id="<?= $row['id'] ?>">Block</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-requests">No pending friend requests.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<div id="blocked-users-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('blocked-users-modal')">×</span>
        <h3>Blocked Users</h3>
        <input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div id="blocked-users-content">
            <?php if (count($blocked_users) > 0): ?>
                <?php foreach ($blocked_users as $row): ?>
                    <div class="blocked-card" data-id="<?= $row['id'] ?>">
                        <img src="Uploads/<?= htmlspecialchars($row['profile_pic']) ?>" class="profile-pic">
                        <div class="blocked-info">
                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                            <span><?= htmlspecialchars($row['email']) ?></span>
                            <span>You blocked this user</span>
                        </div>
                        <div class="blocked-actions">
                            <button class="unblock-btn" data-id="<?= $row['id'] ?>">Unblock</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-blocked">No blocked users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}


document.querySelectorAll(".accept, .block").forEach(button => {
    button.addEventListener("click", function () {
        const from_id = this.dataset.id;
        const action = this.classList.contains("accept") ? "accept" : "block";
        const card = this.closest(".request-card");

        fetch("handle_requests.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `from_id=${from_id}&action=${action}`
        })
        .then(response => response.text())
        .then(data => {
            card.innerHTML = `<p style="color: #00cc00; text-align: center;">${data}</p>`;
            setTimeout(() => card.remove(), 1000);
        })
        .catch(error => {
            card.innerHTML = `<p style="color: #ff4444; text-align: center;">An error occurred.</p>`;
            console.error(error);
        });
    });
});


$(document).ready(function() {
    $(".unblock-btn").on("click", function() {
        let $button = $(this);
        $button.prop("disabled", true).text("Unblocking...");
        if (confirm("Are you sure you want to unblock this user?")) {
            let userId = $button.data("id");
            let csrfToken = $("#csrf_token").val();

            if (!userId || !csrfToken) {
                alert("Error: Missing user ID or CSRF token.");
                $button.prop("disabled", false).text("Unblock");
                return;
            }

            $.post("unblock.php", {
                user_id: userId,
                csrf_token: csrfToken
            }, function(res) {
                alert(res);
                if (res === "User unblocked successfully.") {
                    $button.closest(".blocked-card").remove();
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("Error: " + textStatus + " - " + errorThrown);
            }).always(function() {
                $button.prop("disabled", false).text("Unblock");
            });
        } else {
            $button.prop("disabled", false).text("Unblock");
        }
    });

   
    $('#search').on("keyup", function() {
        let query = $(this).val();
        if (query.length > 0) {
            $.post("search_users.php", {
                query: query
            }, function(data) {
                $("#result").html(data);
            });
        } else {
            $("#result").html("");
        }
    });

    $(document).on("click", ".send-request", function() {
        let to_id = $(this).data("id");
        $.post("send_request.php", {
            to_id: to_id
        }, function(response) {
            alert(response);
        });
    });
});
</script>

</body>
</html>