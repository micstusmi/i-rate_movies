<?php
session_start();

include "includes/header.php";
include __DIR__ . "/includes/db.php";

// Check if ID exists
if (!isset($_GET["movie_id"])) {
    echo "Movie not found.";
    exit;
}

$movie_id = (int)$_GET["movie_id"];  // movie id from URL

// If review form submitted
if (isset($_POST["submit_review"])) {

    if (!isset($_SESSION["user_id"])) {
        echo "You must be logged in to leave a review.";
        exit;
    }

    $user_id = (int)$_SESSION["user_id"];
    $rating  = (int)$_POST["rating"];
    $comment = $_POST["comment"];

    // Prepare and execute INSERT
    $stmt = $conn->prepare(
        "INSERT INTO reviews (user_id, movie_id, rating, comment)
         VALUES (?, ?, ?, ?)"
    );
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiis", $user_id, $movie_id, $rating, $comment);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();
}

// Fetch movie data
$stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Movie not found.";
    exit;
}

$movie = $result->fetch_assoc();
$stmt->close();
?>