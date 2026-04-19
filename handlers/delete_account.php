<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

// Is the user actually logged in?
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Deletes the user from the database
// DB has "Foreign Key Constraints" with "Cascade Delete" so this will also remove all their reviews, favourites, etc.
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    // Success: account is gone. 
    session_unset();
    session_destroy();

    // Redirects to the register page with a "deleted" flag in the URL
    header("Location: ../register.php?deleted=success");
    exit;
} else {
    // Upon error, something went wrong in the DB
    $_SESSION['flash_message'] = "Error: Could not delete account.";
    header("Location: ../my_account.php?tab=settings");
    exit;
}
