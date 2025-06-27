<?php
$con = new mysqli("localhost", "root", "", "chat_app");
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
