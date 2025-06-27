<?php
session_start();
include "config.php";

header('Content-Type: application/json'); 

if (!isset($_SESSION['userid'])) {
    echo json_encode(["status" => "error", "message" => "❌ You are not logged in."]);
    exit;
}

$user_id = $_SESSION['userid'];


if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "❌ Error uploading file."]);
    exit;
}

$upload_dir = "uploads/";
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

$file_tmp = $_FILES['profile_pic']['tmp_name'];
$file_name = basename($_FILES['profile_pic']['name']);
$file_type = mime_content_type($file_tmp);
$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);


if (!in_array($file_type, $allowed_types)) {
    echo json_encode(["status" => "error", "message" => "❌ Only JPG, PNG, and GIF files are allowed."]);
    exit;
}


$new_filename = "profile_" . $user_id . "_" . time() . "." . $file_ext;
$target_file = $upload_dir . $new_filename;


if (move_uploaded_file($file_tmp, $target_file)) {
   
    $stmt = $con->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
    $stmt->bind_param("si", $new_filename, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "file" => $new_filename]);
    } else {
        echo json_encode(["status" => "error", "message" => "❌ Database update failed."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "❌ Failed to move uploaded file."]);
}
?>
