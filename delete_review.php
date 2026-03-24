<?php
session_start();
include(__DIR__ . "/includes/db.php");

// Must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Must come from POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $review_id = (int)$_POST["review_id"];
    $user_id = (int)$_SESSION["user_id"];

    // 🔐 Only delete if it belongs to the user
    $stmt = $conn->prepare("
        DELETE FROM reviews 
        WHERE review_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
}

// 🔄 Redirect back
header("Location: my_account.php");
exit;
?>