<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

$userid = $_SESSION['userid'];
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

$stmt = $con->prepare("
    SELECT u.id, u.name 
    FROM users u 
    WHERE u.id IN (
        SELECT CASE 
            WHEN sender_id = ? THEN receiver_id 
            ELSE sender_id 
        END
        FROM friends 
        WHERE (sender_id = ? OR receiver_id = ?) AND status = 'accepted'
    )
");
$stmt->bind_param("iii", $userid, $userid, $userid);
$stmt->execute();
$friends = $stmt->get_result();

$stmt_user = $con->prepare("SELECT name, profile_pic FROM users WHERE id=?");
$stmt_user->bind_param("i", $userid);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .friend-list {
            max-height: 150px;
            overflow-y: auto;
            background-color: #1c1c1c;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .friend-card {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background-color: #333333;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .friend-card:hover {
            background-color: #444444;
        }
        .friend-card.active {
            background-color: #ffffff;
            color: #000000;
        }
        .friend-card i {
            margin-right: 10px;
            color: inherit;
        }
        .friend-card span {
            font-size: 16px;
        }
        #chat-box {
            background-color: #1c1c1c;
            border: 1px solid #333333;
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        #chat-box p {
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
            background-color: #333333;
            color: #ffffff;
            word-wrap: break-word;
        }
        #loading {
            display: none;
            text-align: center;
            color: #cccccc;
            font-size: 14px;
            margin-bottom: 10px;
        }
        #error {
            color: #ff4444;
            display: none;
            margin-bottom: 10px;
            font-size: 14px;
            text-align: center;
        }
        .chat-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        #message {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #333333;
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            flex-grow: 1;
            outline: none;
        }
        #message::placeholder {
            color: #000000;
            opacity: 0.7;
        }
        #message:focus {
            border-color: #ffffff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
        }
        #send {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        #send:hover {
            background-color: #333333;
        }
        #send:disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .content {
                margin-left: 200px;
                padding: 20px;
            }
            .friend-list {
                max-height: 120px;
            }
            .chat-input {
                flex-direction: column;
            }
            #message, #send {
                width: 100%;
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
    <h3>Chat with Friends</h3>
    <div class="friend-list">
        <?php if ($friends->num_rows > 0): ?>
            <?php $friends->data_seek(0); ?>
            <?php while ($f = $friends->fetch_assoc()): ?>
                <div class="friend-card" data-friend-id="<?php echo $f['id']; ?>">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #cccccc; text-align: center;">No friends found.</p>
        <?php endif; ?>
    </div>

    <div id="chat-box"></div>
    <div id="loading">Loading messages...</div>
    <div id="error"></div>

    <div class="chat-input">
        <input type="hidden" id="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="text" id="message" placeholder="Type a message" maxlength="1000">
        <button id="send">Send</button>
    </div>
</div>

<script>
    let interval;
    let selectedFriendId = null;

    function showError(message) {
        $("#error").text(message).show();
        setTimeout(() => $("#error").hide(), 5000);
    }

    function loadMessages(friendId) {
        $("#loading").show();
        $.post("load_message.php", { friend_id: friendId, csrf_token: $("#csrf_token").val() })
            .done(function(data) {
                const chatBox = $("#chat-box")[0];
                const isAtBottom = chatBox.scrollHeight - chatBox.scrollTop <= chatBox.clientHeight + 100;
                $("#chat-box").html(data);
                if (isAtBottom) {
                    $("#chat-box").scrollTop(chatBox.scrollHeight);
                }
            })
            .fail(function() {
                showError("Failed to load messages. Please try again.");
            })
            .always(function() {
                $("#loading").hide();
            });
    }

    $(".friend-card").click(function() {
        clearInterval(interval);
        $(".friend-card").removeClass("active");
        $(this).addClass("active");
        selectedFriendId = $(this).data("friend-id");
        if (selectedFriendId) {
            loadMessages(selectedFriendId);
            interval = setInterval(() => loadMessages(selectedFriendId), 3000);
        } else {
            $("#chat-box").html("");
            $("#loading").hide();
        }
    });

    $("#send").click(function() {
        let friendId = selectedFriendId;
        let msg = $("#message").val().trim();
        let $sendBtn = $(this);
        let csrfToken = $("#csrf_token").val();

        if (!friendId) {
            showError("Please select a friend.");
            return;
        }
        if (!msg) {
            showError("Please enter a message.");
            return;
        }
        if ($sendBtn.prop('disabled')) return;

        $sendBtn.prop('disabled', true);
        $.post("send_message.php", { friend_id: friendId, message: msg, csrf_token: csrfToken }, null, "json")
            .done(function(response) {
                if (response.success) {
                    $("#message").val("");
                    loadMessages(friendId);
                } else {
                    showError(response.error || "Failed to send message.");
                }
            })
            .fail(function() {
                showError("Failed to send message. Please try again.");
            })
            .always(function() {
                $sendBtn.prop('disabled', false);
            });
    });

    $("#message").keypress(function(e) {
        if (e.which === 13) {
            $("#send").click();
        }
    });

    $(window).on('unload', function() {
        clearInterval(interval);
    });
</script>

</body>
</html>
