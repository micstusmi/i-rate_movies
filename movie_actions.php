<?php
session_start();

require __DIR__ . "/includes/db.php";

// Ensures that we have a movie ID
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$movieId = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: movie.php?id=" . $movieId);
    exit;
}

// Handles POST actions: favourites and reviews

// FAVOURITES (add/remove)
if (isset($_POST['add_favorite']) || isset($_POST['remove_favorite'])) {
    if (!isset($_SESSION["user_id"])) {
        // If not logged in – send back to login with redirect target if you like
        header("Location: login.php");
        exit;
    }

    $currentUserId = (int)$_SESSION["user_id"];

    if (isset($_POST['add_favorite'])) {
        // Insert, ignore duplicates
        $addFavouriteStmt = $conn->prepare("
            INSERT IGNORE INTO user_favorites (user_id, movie_id)
            VALUES (?, ?)
        ");
        if ($addFavouriteStmt) {
            $addFavouriteStmt->bind_param("ii", $currentUserId, $movieId);
            $addFavouriteStmt->execute();
            $addFavouriteStmt->close();
        }
    } elseif (isset($_POST['remove_favorite'])) {
        // Deletes favourite
        $removeFavouriteStmt = $conn->prepare("
            DELETE FROM user_favorites
            WHERE user_id = ? AND movie_id = ?
        ");
        if ($removeFavouriteStmt) {
            $removeFavouriteStmt->bind_param("ii", $currentUserId, $movieId);
            $removeFavouriteStmt->execute();
            $removeFavouriteStmt->close();
        }
    }

    header("Location: movie.php?id=" . $movieId);
    exit;
}

// Reviews (create/update/delete)
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$currentUserId = (int)$_SESSION["user_id"];

// Deletes a review
if (isset($_POST['delete_review'])) {
    $reviewIdToDelete = (int)($_POST['review_id'] ?? 0);

    $deleteReviewStmt = $conn->prepare("
        DELETE FROM reviews
        WHERE review_id = ? AND user_id = ?
    ");
    if ($deleteReviewStmt) {
        $deleteReviewStmt->bind_param("ii", $reviewIdToDelete, $currentUserId);
        $deleteReviewStmt->execute();
        $deleteReviewStmt->close();
    }

    header("Location: movie.php?id=" . $movieId);
    exit;
}

// Create or update review
$ratingValue   = (int)($_POST["rating"] ?? 0);
$reviewComment = $_POST["comment"] ?? '';

if ($ratingValue < 1 || $ratingValue > 5) {
    // Invalid rating; just redirect back for now
    header("Location: movie.php?id=" . $movieId);
    exit;
}

if (isset($_POST['update_review']) && isset($_POST['review_id'])) {
    // Updates existing review
    $reviewIdToUpdate = (int)$_POST['review_id'];

    $updateReviewStmt = $conn->prepare("
        UPDATE reviews
        SET rating = ?, comment = ?
        WHERE review_id = ? AND user_id = ?
    ");
    if ($updateReviewStmt) {
        $updateReviewStmt->bind_param("isii", $ratingValue, $reviewComment, $reviewIdToUpdate, $currentUserId);
        $updateReviewStmt->execute();
        $updateReviewStmt->close();
    }

} elseif (isset($_POST['submit_review'])) {
    // Creates a new review
    $createReviewStmt = $conn->prepare("
        INSERT INTO reviews (user_id, movie_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    if ($createReviewStmt) {
        $createReviewStmt->bind_param("iiis", $currentUserId, $movieId, $ratingValue, $reviewComment);
        $createReviewStmt->execute();
        $createReviewStmt->close();
    }
}

// Redirects back to the movie page
header("Location: movie.php?id=" . $movieId);
exit;