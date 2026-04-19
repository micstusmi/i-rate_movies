<?php
session_start();
include(__DIR__ . "/includes/db.php");

// User MUST be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// User MUST come from POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $review_id = (int)$_POST["review_id"];
    $user_id = (int)$_SESSION["user_id"];

    // Deletes the review if it belongs to the user
    $stmt = $conn->prepare("
        DELETE FROM reviews 
        WHERE review_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
}

// Redirects the user back to the "My Account" page (specifically the "My Reviews" tab)
header("Location: my_account.php?tab=my-reviews");
exit;
?>