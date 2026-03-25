<?php
session_start();
include(__DIR__ . "/includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Deletes user
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// Destroys Session
session_unset();
session_destroy();

// Redirects to homepage or login
header("Location: index.php");
exit;
?>