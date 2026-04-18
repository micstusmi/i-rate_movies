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
$SUPER_REVIEWER_THRESHOLD = 11;

// Fetch movie data WITH average rating + review count
$movieStmt = $conn->prepare("
    SELECT m.*,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.review_id)        AS review_count
    FROM movies m
    LEFT JOIN reviews r ON m.movie_id = r.movie_id
    WHERE m.movie_id = ?
");
if (!$movieStmt) {
    die("Prepare failed: " . $conn->error);
}
$movieStmt->bind_param("i", $movieId);
$movieStmt->execute();
$movieResult = $movieStmt->get_result();

if ($movieResult->num_rows == 0) {
    echo "Movie not found.";
    include("includes/footer.php");
    exit;
}

$movie        = $movieResult->fetch_assoc();
$movieStmt->close();

$averageRating = isset($movie['avg_rating']) ? (float)$movie['avg_rating'] : 0;
$reviewCount   = isset($movie['review_count']) ? (int)$movie['review_count'] : 0;

// Fetch reviews for this movie
$userReview       = null;
$otherReviews     = [];
$loggedInUserId   = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null;

// How many reviews has the logged-in user written in total?
$userTotalReviews    = 0;
$isLoggedInUserSuper = false;

if ($loggedInUserId) {
    $userReviewCountStmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM reviews
        WHERE user_id = ?
    ");
    if ($userReviewCountStmt) {
        $userReviewCountStmt->bind_param("i", $loggedInUserId);
        $userReviewCountStmt->execute();
        $userReviewCountRes = $userReviewCountStmt->get_result();
        if ($row = $userReviewCountRes->fetch_assoc()) {
            $userTotalReviews = (int)$row['cnt'];
        }
        $userReviewCountStmt->close();
    }

    $isLoggedInUserSuper = ($userTotalReviews >= $SUPER_REVIEWER_THRESHOLD);
}

// Fetch all reviews with user's info and total reviews per user
$movieReviewsStmt = $conn->prepare("
SELECT
    r.review_id,
    r.user_id,
    r.rating,
    r.comment,
    r.created_at,
    u.alias,
    (
        SELECT COUNT(*)
        FROM reviews r2
        WHERE r2.user_id = r.user_id
    ) AS total_reviews_by_user
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
        $userReview = $reviewRow;          // current user's review at the top
    } else {
        $otherReviews[] = $reviewRow;      // everyone else's reviews below
    }
}

// Sort other reviews: super reviewers first, then by created_at DESC
usort($otherReviews, function($a, $b) use ($SUPER_REVIEWER_THRESHOLD) {
    $aSuper = isset($a['total_reviews_by_user']) && (int)$a['total_reviews_by_user'] >= $SUPER_REVIEWER_THRESHOLD;
    $bSuper = isset($b['total_reviews_by_user']) && (int)$b['total_reviews_by_user'] >= $SUPER_REVIEWER_THRESHOLD;

    // Super reviewers first
    if ($aSuper && !$bSuper) return -1;
    if ($bSuper && !$aSuper) return 1;

    // If both same type (both super or both not), newest first
    return strcmp($b['created_at'], $a['created_at']);
});

$movieReviewsStmt->close();

// Check if this movie is in the logged-in user's favourites
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

        <!-- Average rating block -->
        <p class="mb-1">
          <strong>Average rating:</strong>
          <?php echo number_format($averageRating, 1); ?> / 5
          <?php if ($reviewCount > 0): ?>
            <small class="text-muted">
              (based on <?php echo $reviewCount; ?> review<?php echo $reviewCount === 1 ? '' : 's'; ?>)
            </small>
          <?php else: ?>
            <small class="text-muted">(no reviews yet)</small>
          <?php endif; ?>
        </p>
      </div>

      <!-- Favourite / Unfavourite button -->
      <div class="text-end">
        <?php if (isset($_SESSION["user_id"])): ?>
          <form method="POST" action="movie_actions.php?id=<?php echo $movieId; ?>">
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

      <?php $superThreshold = $SUPER_REVIEWER_THRESHOLD; ?>

      <p class="small mb-3">
        You have written <strong><?php echo $userTotalReviews; ?></strong> review<?php echo $userTotalReviews === 1 ? '' : 's'; ?>.
        <?php if ($userTotalReviews >= $superThreshold): ?>
          <span class="ms-1" title="Super Reviewer">Congrats on becoming a 🏅 Super Reviewer and thanks for your contributions!</span>
        <?php else: ?>
          <?php $remaining = max(0, $superThreshold - $userTotalReviews); ?>
          Earn the title <strong>🏅 Super Reviewer</strong> by writing
          <strong><?php echo $remaining; ?></strong> more review<?php echo $remaining === 1 ? '' : 's'; ?>.
        <?php endif; ?>
      </p>

      <?php if (!$userReview): ?>
        <!-- No review yet: show create form -->
        <form method="POST" action="movie_actions.php?id=<?php echo $movieId; ?>" id="new-review-form">
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
        <!-- If user already has a review - show the review plus the edit/delete controls -->

        <div class="card mb-4" id="my-review-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>

  <?php
  // just above the heading in the "Your review" card
  $isUserSuper = isset($userReview['total_reviews_by_user']) &&
                 (int)$userReview['total_reviews_by_user'] >= $SUPER_REVIEWER_THRESHOLD;
?>

<?php
  $isUserSuper = isset($userReview['total_reviews_by_user']) &&
                 (int)$userReview['total_reviews_by_user'] >= $SUPER_REVIEWER_THRESHOLD;
?>
<h5 class="card-title mb-1">
  Your review
</h5>
<p class="mb-0 small">
  <strong class="<?php echo $isLoggedInUserSuper ? 'super-reviewer-alias' : ''; ?>">
    <?php echo htmlspecialchars($_SESSION['alias']); ?>
  </strong>
  <?php if ($isLoggedInUserSuper): ?>
  <span class="ms-1" title="Super Reviewer">🏅</span>
<?php endif; ?>
</p>
                <div class="mb-2 mt-1">
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
                <!-- Edit button (pencil icon) -->
                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        id="edit-my-review-btn">
                  <i class="bi bi-pencil"></i>
                </button>

                <!-- Delete form (trash icon) -->
                <form method="POST" action="movie_actions.php?id=<?php echo $movieId; ?>" class="d-inline"
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
            <form method="POST" action="movie_actions.php?id=<?php echo $movieId; ?>" id="edit-my-review-form" class="mt-3 d-none">
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
        <?php
          $isSuper = isset($review['total_reviews_by_user']) &&
                     (int)$review['total_reviews_by_user'] >= $SUPER_REVIEWER_THRESHOLD;
        ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h6 class="mb-1">
                  <span class="<?php echo $isSuper ? 'super-reviewer-alias' : ''; ?>">
                    <?php echo htmlspecialchars($review['alias']); ?>
                  </span>
                  <?php if ($isSuper): ?>
                    <span class="ms-1" title="Super Reviewer">🏅</span>
                  <?php endif; ?>
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