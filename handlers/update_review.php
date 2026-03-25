<?php
session_start();
include(__DIR__ . "/../includes/db.php");

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $review_id = (int)$_POST["review_id"];
    $rating = (int)$_POST["rating"];
    $comment = $_POST["comment"];
    $user_id = (int) $_SESSION["user_id"];

    $stmt = $conn->prepare("
        UPDATES reviews 
        SET rating = ?, comment = ? 
        WHERE review_id = ? AND user_id = ?
    ");
    $stmt->bind_param("isii", $rating, $comment, $review_id, $user_id);
    $stmt->execute();
}

// Redirects the user back to the account page
header("Location: ../my_account.php");
exit;