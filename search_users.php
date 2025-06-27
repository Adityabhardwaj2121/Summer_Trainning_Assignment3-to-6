<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid']) || !isset($_POST['query'])) {
    echo "Invalid request.";
    exit;
}

$userid = $_SESSION['userid'];
$query = '%' . htmlspecialchars($_POST['query']) . '%';



$sql = "
    SELECT id, name, email FROM users 
    WHERE id != ? 
    AND (name LIKE ? OR email LIKE ?) 
    AND id NOT IN (
        SELECT receiver_id FROM friends WHERE sender_id = ? AND status = 'blocked'
        UNION
        SELECT sender_id FROM friends WHERE receiver_id = ? AND status = 'blocked'
    )
";

$stmt = $con->prepare($sql);
$stmt->bind_param("issii", $userid, $query, $query, $userid, $userid);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Search Users</title>
</head>
<body>
<h3>Search Results</h3>

<?php if (count($users) > 0): ?>
    <?php foreach ($users as $user): ?>
        <div>
            <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
            <button class="send-request" data-id="<?= $user['id'] ?>">Send Friend Request</button>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No users found.</p>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".send-request").forEach(btn => {
        btn.addEventListener("click", function () {
            const to_id = this.dataset.id;

            fetch("send_request.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "to_id=" + encodeURIComponent(to_id)
            })
            .then(res => res.text())
            .then(data => {
                alert(data);
                this.disabled = true;
                this.innerText = "Request Sent";
            })
            .catch(err => {
                alert("Error sending request.");
                console.error(err);
            });
        });
    });
});
</script>
</body>
</html>
