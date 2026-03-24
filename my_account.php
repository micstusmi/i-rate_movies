<?php
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

// Require login
if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to view your account.";
    include("includes/footer.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

// Which section to show: 'reviews' tab or 'favourites' tab
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'reviews';


// Get user's info

$userQuery = $conn->prepare("
    SELECT alias, email, created_at
    FROM users
    WHERE user_id = ?
");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userQuery->close();

if ($userResult->num_rows !== 1) {
    echo "User not found.";
    include("includes/footer.php");
    exit;
}

$userData = $userResult->fetch_assoc();


// Get user's reviews

$reviewsQuery = "
    SELECT
        r.review_id,
        r.movie_id,
        r.rating,
        r.comment,
        r.created_at,
        m.title
    FROM reviews r
    JOIN movies m ON r.movie_id = m.movie_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
";

$reviewsStmt = $conn->prepare($reviewsQuery);
$reviewsStmt->bind_param("i", $userId);
$reviewsStmt->execute();
$userReviewsResult = $reviewsStmt->get_result();
$reviewsStmt->close();


// Get user's favourites

$favouritesQuery = "
    SELECT
        m.movie_id,
        m.title,
        m.image_url,
        m.genre,
        m.year
    FROM user_favorites uf
    JOIN movies m ON uf.movie_id = m.movie_id
    WHERE uf.user_id = ?
    ORDER BY m.title ASC
";

$favouritesStmt = $conn->prepare($favouritesQuery);
$favouritesStmt->bind_param("i", $userId);
$favouritesStmt->execute();
$favouritesResult = $favouritesStmt->get_result();
$favouritesStmt->close();
?>

<h2>My Account</h2>

<div class="mb-4">
  <h4>Account details</h4>
  <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['alias']); ?></p>
  <?php if (!empty($userData['email'])): ?>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($userData['email']); ?></p>
  <?php endif; ?>
  <?php if (!empty($userData['created_at'])): ?>
    <p><strong>Member since:</strong> <?php echo htmlspecialchars($userData['created_at']); ?></p>
  <?php endif; ?>
</div>

<hr>

<!-- Tabs (My reviews / My favourites) -->
<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?php echo ($activeTab === 'reviews') ? 'active' : ''; ?>"
       href="my_account.php?tab=reviews">
      My reviews
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($activeTab === 'favourites') ? 'active' : ''; ?>"
       href="my_account.php?tab=favourites">
      My favourites
    </a>
  </li>
</ul>

<?php if ($activeTab === 'reviews'): ?>

  <h4>My reviews</h4>

  <?php if ($userReviewsResult->num_rows === 0): ?>
    <p>You haven’t written any reviews yet.</p>
  <?php else: ?>
    <div class="list-group mb-4">
      <?php while ($review = $userReviewsResult->fetch_assoc()): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5>
                <a href="movie.php?id=<?php echo (int)$review['movie_id']; ?>">
                  <?php echo htmlspecialchars($review['title']); ?>
                </a>
              </h5>

              <!-- read-only Stars display -->
              <div>
                <?php for ($starIndex = 1; $starIndex <= 5; $starIndex++): ?>
                  <?php if ($starIndex <= $review['rating']): ?>
                    <span class="text-warning">&#9733;</span>
                  <?php else: ?>
                    <span class="text-secondary">&#9733;</span>
                  <?php endif; ?>
                <?php endfor; ?>
              </div>

              <?php if (!empty($review['comment'])): ?>
                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
              <?php endif; ?>

              <small class="text-muted">
                <?php echo htmlspecialchars($review['created_at']); ?>
              </small>

              <!-- Edit Form (hidden) -->
              <div id="edit-form-<?php echo $review['review_id']; ?>" style="display:none;" class="mt-3">
                <form method="POST" action="handlers/update_review.php">
                  <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">

                  <!-- Editable rating -->
                  <div class="mb-2">
                    <label class="form-label d-block">Rating (1–5)</label>

                    <!-- hidden input that PHP will read -->
                    <input type="hidden"
                           name="rating"
                           id="rating-value-<?php echo $review['review_id']; ?>"
                           value="<?php echo (int)$review['rating']; ?>"
                           required>

                    <!-- clickable stars, unique per review -->
                    <div id="star-rating-<?php echo $review['review_id']; ?>"
                         data-initial-rating="<?php echo (int)$review['rating']; ?>">
                      <i class="bi bi-star star" data-value="1"></i>
                      <i class="bi bi-star star" data-value="2"></i>
                      <i class="bi bi-star star" data-value="3"></i>
                      <i class="bi bi-star star" data-value="4"></i>
                      <i class="bi bi-star star" data-value="5"></i>
                    </div>

                    <small id="rating-text-<?php echo $review['review_id']; ?>" class="text-muted"></small>
                  </div>

                  <div class="mb-2">
                    <label>Comment</label>
                    <textarea name="comment" class="form-control"><?php echo htmlspecialchars($review['comment']); ?></textarea>
                  </div>

                  <button type="submit" name="update_review" class="btn btn-success btn-sm">
                    Save changes
                  </button>
                  <button type="button"
                          class="btn btn-sm btn-secondary"
                          onclick="cancelEdit(<?php echo $review['review_id']; ?>)">
                    Cancel
                  </button>
                </form>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-end">
              <!-- Toggle edit -->
              <button class="btn btn-sm btn-outline-primary mb-1"
                      onclick="toggleEdit(<?php echo $review['review_id']; ?>)">
                <i class="bi bi-pencil"></i>
              </button>

              <!-- Delete -->
              <form method="POST" action="delete_review.php">
                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Delete this review?');">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

<?php elseif ($activeTab === 'favourites'): ?>

  <h4>My favourites</h4>

  <?php if ($favouritesResult->num_rows === 0): ?>
    <p>You don’t have any favourite movies yet.</p>
  <?php else: ?>
    <div class="row mb-4">
      <?php while ($favouriteMovie = $favouritesResult->fetch_assoc()): ?>
        <div class="col-md-3 mb-3">
          <div class="card h-100">
            <?php if (!empty($favouriteMovie['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($favouriteMovie['image_url']); ?>"
                   class="card-img-top"
                   alt="Movie image">
            <?php endif; ?>
            <div class="card-body">
              <h6 class="card-title">
                <a href="movie.php?id=<?php echo (int)$favouriteMovie['movie_id']; ?>">
                  <?php echo htmlspecialchars($favouriteMovie['title']); ?>
                </a>
              </h6>
              <?php if (!empty($favouriteMovie['genre']) || !empty($favouriteMovie['year'])): ?>
                <p class="card-text small text-muted mb-0">
                  <?php if (!empty($favouriteMovie['genre'])): ?>
                    <?php echo htmlspecialchars($favouriteMovie['genre']); ?>
                  <?php endif; ?>
                  <?php if (!empty($favouriteMovie['year'])): ?>
                    <?php if (!empty($favouriteMovie['genre'])) echo ' • '; ?>
                    <?php echo (int)$favouriteMovie['year']; ?>
                  <?php endif; ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<hr>

<h4>Permanently delete my account &amp; data</h4>

<form method="POST"
      action="handlers/deleted_account.php"
      onsubmit="return confirm('Are you sure you want to delete your account and all associated data? This cannot be undone.');">
  <button type="submit" class="btn btn-danger">
    Delete my account
  </button>
</form>

<!-- Toggle script -->
<script>
function toggleEdit(reviewId) {
    const form = document.getElementById("edit-form-" + reviewId);
    if (!form) return;
    form.style.display = (form.style.display === "none" || form.style.display === "")
        ? "block"
        : "none";
}
function cancelEdit(reviewId) {
    const form = document.getElementById("edit-form-" + reviewId);
    if (!form) return;
    form.style.display = "none";
}
</script>

<?php include("includes/footer.php"); ?>