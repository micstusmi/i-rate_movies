<?php
session_start();

include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

// Checks if ID exists in URL
if (!isset($_GET["id"])) {
    echo "Movie not found.";
    include("includes/footer.php");
    exit;
}

$movieId = (int)$_GET["id"];  // movie_id

// Fetches movie data first
$movieQuery = $conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
if (!$movieQuery) {
    die("Prepare failed: " . $conn->error);
}
$movieQuery->bind_param("i", $movieId);
$movieQuery->execute();
$movieResult = $movieQuery->get_result();

if ($movieResult->num_rows !== 1) {
    echo "Movie not found.";
    include("includes/footer.php");
    exit;
}

$movie = $movieResult->fetch_assoc();
$movieQuery->close();

// --- Handles 'POST' actions: favourites + reviews ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 'FAVOURITES': do not require a rating or a comment, just requires login
    if (isset($_POST['add_favorite']) || isset($_POST['remove_favorite'])) {
        if (!isset($_SESSION["user_id"])) {
            echo "You must be logged in to perform this action.";
            include("includes/footer.php");
            exit;
        }

        $currentUserId = (int)$_SESSION["user_id"];

        if (isset($_POST['add_favorite'])) {
            // Insert, ignore duplicates
            $addFavouriteStmt = $conn->prepare("
                INSERT IGNORE INTO user_favorites (user_id, movie_id)
                VALUES (?, ?)
            ");
            if (!$addFavouriteStmt) {
                die("Prepare failed: " . $conn->error);
            }
            $addFavouriteStmt->bind_param("ii", $currentUserId, $movieId);
            $addFavouriteStmt->execute();
            $addFavouriteStmt->close();

        } elseif (isset($_POST['remove_favorite'])) {
            // Deletes favourite
            $removeFavouriteStmt = $conn->prepare("
                DELETE FROM user_favorites
                WHERE user_id = ? AND movie_id = ?
            ");
            if (!$removeFavouriteStmt) {
                die("Prepare failed: " . $conn->error);
            }
            $removeFavouriteStmt->bind_param("ii", $currentUserId, $movieId);
            $removeFavouriteStmt->execute();
            $removeFavouriteStmt->close();
        }

        // Redirects back to avoid form resubmission
        header("Location: movie.php?id=" . $movieId);
        exit;
    }

    // Review Actions (create / update / delete)
    if (!isset($_SESSION["user_id"])) {
        echo "You must be logged in to perform this action.";
        include("includes/footer.php");
        exit;
    }

    $currentUserId = (int)$_SESSION["user_id"];

    // Deletes review
    if (isset($_POST['delete_review'])) {
        $reviewIdToDelete = (int)$_POST['review_id'];

        $deleteReviewStmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
        if (!$deleteReviewStmt) {
            die("Prepare failed: " . $conn->error);
        }
        $deleteReviewStmt->bind_param("ii", $reviewIdToDelete, $currentUserId);
        $deleteReviewStmt->execute();
        $deleteReviewStmt->close();

        header("Location: movie.php?id=" . $movieId);
        exit;
    }

    // Creates or Updates user's review
    $ratingValue   = (int)($_POST["rating"] ?? 0);
    $reviewComment = $_POST["comment"] ?? '';

    if ($ratingValue < 1 || $ratingValue > 5) {
        echo "Invalid rating.";
        include("includes/footer.php");
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
        if (!$updateReviewStmt) {
            die("Prepare failed: " . $conn->error);
        }

        $updateReviewStmt->bind_param("isii", $ratingValue, $reviewComment, $reviewIdToUpdate, $currentUserId);
        $updateReviewStmt->execute();
        $updateReviewStmt->close();

    } elseif (isset($_POST['submit_review'])) {
        // Creates a new review
        $createReviewStmt = $conn->prepare("
            INSERT INTO reviews (user_id, movie_id, rating, comment)
            VALUES (?, ?, ?, ?)
        ");
        if (!$createReviewStmt) {
            die("Prepare failed: " . $conn->error);
        }

        $createReviewStmt->bind_param("iiis", $currentUserId, $movieId, $ratingValue, $reviewComment);
        $createReviewStmt->execute();
        $createReviewStmt->close();
    }

    // Avoids resubmit on refresh
    header("Location: movie.php?id=" . $movieId);
    exit;
}

// --- Fetches reviews for this movie ---
$userReview       = null;
$otherReviews     = [];
$loggedInUserId   = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null;

// Fetches all reviews with user's info
$movieReviewsStmt = $conn->prepare("
    SELECT r.review_id, r.user_id, r.rating, r.comment, r.created_at,
           u.alias
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.movie_id = ?
    ORDER BY r.created_at DESC
");
if (!$movieReviewsStmt) {
    die("Prepare failed: " . $conn->error);
}

$movieReviewsStmt->bind_param("i", $movieId);
$movieReviewsStmt->execute();
$movieReviewsResult = $movieReviewsStmt->get_result();

while ($reviewRow = $movieReviewsResult->fetch_assoc()) {
    if ($loggedInUserId && $reviewRow['user_id'] == $loggedInUserId) {
        $userReview = $reviewRow;          // current user's review
    } else {
        $otherReviews[] = $reviewRow;      // everyone else's reviews
    }
}
$movieReviewsStmt->close();

// --- Checks if this movie is in the logged-in user's favourites ---
$isFavorite = false;
if ($loggedInUserId) {
    $favouriteCheckStmt = $conn->prepare("
        SELECT 1
        FROM user_favorites
        WHERE user_id = ? AND movie_id = ?
        LIMIT 1
    ");
    if ($favouriteCheckStmt) {
        $favouriteCheckStmt->bind_param("ii", $loggedInUserId, $movieId);
        $favouriteCheckStmt->execute();
        $favouriteCheckResult = $favouriteCheckStmt->get_result();
        $isFavorite = ($favouriteCheckResult->num_rows > 0);
        $favouriteCheckStmt->close();
    }
}
?>

<div class="row">

  <!-- Movie Image -->
  <div class="col-md-4">
    <img src="<?php echo htmlspecialchars($movie['image_url']); ?>" class="img-fluid rounded shadow" alt="Movie Image">
  </div>

  <!-- Movie Details -->
  <div class="col-md-8">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
        <p><strong>Year:</strong> <?php echo htmlspecialchars($movie['year']); ?></p>
        <p><strong>Actors:</strong> <?php echo htmlspecialchars($movie['actors']); ?></p>
      </div>

      <!-- Favourite / Unfavourite button -->
      <div class="text-end">
        <?php if (isset($_SESSION["user_id"])): ?>
          <form method="POST">
            <?php if ($isFavorite): ?>
              <button type="submit" name="remove_favorite" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-heart-fill"></i> Remove from favourites
              </button>
            <?php else: ?>
              <button type="submit" name="add_favorite" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-heart"></i> Add to favourites
              </button>
            <?php endif; ?>
          </form>
        <?php else: ?>
          <small class="text-muted">
            <a href="login.php">Log in</a> to add to favourites
          </small>
        <?php endif; ?>
      </div>
    </div>

    <hr>

    <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
    <hr>

    <h4>Leave a Review</h4>

    <?php if (isset($_SESSION["user_id"])): ?>

      <?php if (!$userReview): ?>
        <!-- No review yet: show create form -->
        <form method="POST" id="new-review-form">
          <div class="mb-3">
            <label class="form-label d-block">Rating (1–5)</label>

            <!-- hidden input that PHP reads as $_POST['rating'] -->
            <input type="hidden" name="rating" id="rating-value" required>

            <!-- clickable stars -->
            <div id="star-rating">
              <i class="bi bi-star star" data-value="1"></i>
              <i class="bi bi-star star" data-value="2"></i>
              <i class="bi bi-star star" data-value="3"></i>
              <i class="bi bi-star star" data-value="4"></i>
              <i class="bi bi-star star" data-value="5"></i>
            </div>

            <small id="rating-text" class="text-muted"></small>
          </div>

          <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea name="comment" class="form-control"></textarea>
          </div>

          <button type="submit" name="submit_review" class="btn btn-primary">
            Submit Review
          </button>
        </form>

      <?php else: ?>
        <!-- If user already has a review: show it plus edit/delete controls -->

        <div class="card mb-4" id="my-review-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h5 class="card-title mb-1">Your review</h5>
                <div class="mb-2">
                  <?php
                  // simple star display for user ratings
                  for ($starIndex = 1; $starIndex <= 5; $starIndex++) {
                      if ($starIndex <= (int)$userReview['rating']) {
                          echo '<i class="bi bi-star-fill text-warning"></i>';
                      } else {
                          echo '<i class="bi bi-star text-muted"></i>';
                      }
                  }
                  ?>
                  <small class="text-muted">
                    (<?php echo (int)$userReview['rating']; ?>/5)
                  </small>
                </div>
              </div>

              <div>
                <!-- Edit button (pencil) -->
                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="edit-my-review-btn">
                  <i class="bi bi-pencil"></i>
                </button>

                <!-- Delete form (trash) -->
                <form method="POST" class="d-inline"
                      onsubmit="return confirm('Delete your review?');">
                  <input type="hidden" name="review_id"
                         value="<?php echo (int)$userReview['review_id']; ?>">
                  <button type="submit" name="delete_review"
                          class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </div>

            <p class="card-text mt-2" id="my-review-comment-display">
              <?php echo nl2br(htmlspecialchars($userReview['comment'])); ?>
            </p>

            <small class="text-muted">
              Posted on <?php echo htmlspecialchars($userReview['created_at']); ?>
            </small>

            <!-- Hidden edit form (shown when clicking the pencil) -->
            <form method="POST" id="edit-my-review-form" class="mt-3 d-none">
              <input type="hidden" name="review_id"
                     value="<?php echo (int)$userReview['review_id']; ?>">

              <div class="mb-2">
                <label class="form-label d-block">Rating (1–5)</label>
                <input type="hidden" name="rating" id="edit-rating-value" required>
                <div id="edit-star-rating" data-initial-rating="<?php echo (int)$userReview['rating']; ?>">
                  <i class="bi bi-star star" data-value="1"></i>
                  <i class="bi bi-star star" data-value="2"></i>
                  <i class="bi bi-star star" data-value="3"></i>
                  <i class="bi bi-star star" data-value="4"></i>
                  <i class="bi bi-star star" data-value="5"></i>
                </div>
                <small class="text-muted" id="edit-rating-text"></small>
              </div>

              <div class="mb-2">
                <label class="form-label">Comment</label>
                <textarea name="comment" class="form-control"
                          rows="3"><?php echo htmlspecialchars($userReview['comment']); ?></textarea>
              </div>

              <button type="submit" name="update_review"
                      class="btn btn-sm btn-primary">
                Save changes
              </button>
              <button type="button" class="btn btn-sm btn-secondary"
                      id="cancel-edit-my-review">
                Cancel
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p>Please <a href="login.php">log in</a> to leave a review.</p>
    <?php endif; ?>

    <h4 class="mt-4">All Reviews</h4>

    <?php if (!$userReview && count($otherReviews) === 0): ?>
      <p>No reviews yet. Be the first to review this movie!</p>
    <?php else: ?>

      <?php foreach ($otherReviews as $review): ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">
                  <?php echo htmlspecialchars($review['alias']); ?>
                </h6>
                <div class="mb-1">
                  <?php
                  for ($starIndex = 1; $starIndex <= 5; $starIndex++) {
                      if ($starIndex <= (int)$review['rating']) {
                          echo '<i class="bi bi-star-fill text-warning"></i>';
                      } else {
                          echo '<i class="bi bi-star text-muted"></i>';
                      }
                  }
                  ?>
                  <small class="text-muted">
                    (<?php echo (int)$review['rating']; ?>/5)
                  </small>
                </div>
              </div>
              <small class="text-muted">
                <?php echo htmlspecialchars($review['created_at']); ?>
              </small>
            </div>

            <p class="card-text mt-2">
              <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>

    <?php endif; ?>
  </div>
</div>

<?php include("includes/footer.php"); ?>