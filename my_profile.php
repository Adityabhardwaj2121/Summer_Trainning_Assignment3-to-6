<?php
session_start();
include "config.php";

if (!isset($_SESSION['userid'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['userid'];

$stmt = $con->prepare("SELECT name, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
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

        .content h2 {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }

        .profile-container {
            background-color: #1c1c1c;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
            max-width: 600px;
            margin-bottom: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .profile-header img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            border: 2px solid #ffffff;
        }

        .profile-info {
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
        }

        .upload-section {
            background-color: #333333;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        #profilePicForm {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        #profilePicForm label {
            color: #ffffff;
            font-size: 16px;
        }

        #profileInput {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #333333;
            padding: 8px;
            font-size: 16px;
            border-radius: 6px;
            max-width: 250px;
        }

        #profileInput:focus {
            border-color: #ffffff;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.2);
            outline: none;
        }

        #profilePicForm button {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #profilePicForm button:hover {
            background-color: #333333;
        }

        #uploadResult {
            margin-top: 10px;
            font-size: 14px;
            color: #ffffff;
            text-align: center;
        }

        #uploadResult.success {
            color: #00cc00;
        }

        #uploadResult.error {
            color: #ff4444;
        }

        .view-posts-form {
            display: flex;
            justify-content: center;
        }

        .view-posts-form button {
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #ffffff;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .view-posts-form button:hover {
            background-color: #333333;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .content {
                margin-left: 200px;
                padding: 20px;
            }

            .profile-container {
                max-width: 100%;
            }

            .profile-header {
                flex-direction: column;
                align-items: center;
            }

            #profileInput {
                max-width: 100%;
            }

            #profilePicForm {
                flex-direction: column;
                align-items: center;
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
    <h2>My Profile</h2>
    <div class="profile-container">
        <div class="profile-header">
            <img src="Uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" id="profilePic" alt="Profile Picture">
            <div class="profile-info">
                <?php echo htmlspecialchars($user['name']); ?>
            </div>
        </div>
        <div class="upload-section">
            <form id="profilePicForm" enctype="multipart/form-data">
                <label for="profileInput">Change Profile Picture:</label>
                <input type="file" name="profile_pic" id="profileInput" accept="image/*" required>
                <button type="submit">Upload</button>
            </form>
            <div id="uploadResult"></div>
        </div>
    </div>
    <hr>
   
</div>

<script>
document.getElementById("profilePicForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = document.getElementById("profilePicForm");
    const formData = new FormData(form);
    const resultDiv = document.getElementById("uploadResult");

    fetch("upload_profile.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const img = document.getElementById("profilePic");
            img.src = "Uploads/" + data.file + "?t=" + new Date().getTime();
            resultDiv.className = "success";
            resultDiv.textContent = "Profile picture updated successfully!";
        } else {
            resultDiv.className = "error";
            resultDiv.textContent =的就是data.message || "Upload failed. Please try again.";
        }
    })
    .catch(() => {
        resultDiv.className = "error";
        resultDiv.textContent = "Upload failed. Please try again.";
    });
});
</script>

</body>
</html>