<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit;
}


$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$userid = (int)$_SESSION['userid'];


$stmt = $con->prepare("
    SELECT u.id, u.name, u.email, u.profile_pic,
           CASE 
               WHEN f.sender_id = ? THEN 'you_blocked' 
               ELSE 'they_blocked' 
           END AS block_direction
    FROM friends f
    JOIN users u ON (u.id = f.receiver_id OR u.id = f.sender_id)
    WHERE (f.sender_id = ? OR f.receiver_id = ?) 
    AND f.status = 'blocked'
    AND u.id != ?
");
if (!$stmt) {
    die("Prepare failed: " . $con->error);
}
$stmt->bind_param("iiii", $userid, $userid, $userid, $userid); 
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blocked Users</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .user-card { display: flex; align-items: center; margin: 10px 0; }
        .profile-pic { width: 50px; height: 50px; border-radius: 50%; margin-right: 10px; }
        .user-info { flex-grow: 1; }
        .unblock-btn { padding: 5px 10px; cursor: pointer; }
        .no-blocked { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>Blocked Users</h2>
    <input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="user-card">
                <img src="<?= htmlspecialchars($row['profile_pic']) ?>" class="profile-pic">
                <div class="user-info">
                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                    <div><?= htmlspecialchars($row['email']) ?></div>
                    <div><?= $row['block_direction'] === 'you_blocked' ? 'You blocked this user' : 'This user blocked you' ?></div>
                </div>
                <button class="unblock-btn" data-id="<?= $row['id'] ?>">Unblock</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-blocked">
            <p>No blocked users found.</p>
        </div>
    <?php endif; ?>

    <script>
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
                            $button.parent().remove(); 
                        }
                    }).fail(function(jqXHR, textStatus, errorThrown) {
                        alert("AJAX error: " + textStatus + " - " + errorThrown);
                    }).always(function() {
                        $button.prop("disabled", false).text("Unblock");
                    });
                } else {
                    $button.prop("disabled", false).text("Unblock");
                }
            });
        });
    </script>
</body>
</html>