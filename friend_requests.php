<?php
session_start();
include "config.php";
$userid = $_SESSION['userid'];

$stmt = $con->prepare("
    SELECT u.id, u.name, u.email
    FROM friends f
    JOIN users u ON u.id = f.sender_id
    WHERE f.receiver_id = ? AND f.status = 'pending'
");
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friend Requests</title>
</head>
<body>
    <h2>Friend Requests</h2>

    <?php if (count($requests) > 0): ?>
        <?php foreach ($requests as $row): ?>
            <div>
                <strong><?= htmlspecialchars($row['name']) ?></strong> (<?= htmlspecialchars($row['email']) ?>)
                <button class='accept' data-id='<?= $row['id'] ?>'>Accept</button>
                <button class='block' data-id='<?= $row['id'] ?>'>Block</button>
            </div>
            <hr/>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No pending friend requests.</p>
    <?php endif; ?>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".accept, .block").forEach(button => {
            button.addEventListener("click", function () {
                const from_id = this.dataset.id;
                const action = this.classList.contains("accept") ? "accept" : "block";

                fetch("handle_requests.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `from_id=${from_id}&action=${action}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    this.parentElement.innerHTML = data;
                })
                .catch(error => {
                    alert("An error occurred.");
                    console.error(error);
                });
            });
        });
    });
    </script>
</body>
</html>
