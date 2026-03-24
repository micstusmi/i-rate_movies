<?php
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

// Require login
if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to view your account.";
    include("includes/footer.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

// Which tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'reviews';

// Requires login
if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to view your account.";
    include("includes/footer.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

// Which section to show: 'reviews' tab or 'favourites' tab
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'reviews';

//
// Gets user's info
//
$stmt = $conn->prepare("SELECT alias, email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$stmt->close();

if ($userResult->num_rows !== 1) {
    echo "User not found.";
    include("includes/footer.php");
    exit;
}

$user = $userResult->fetch_assoc();

//
// Gets user's reviews
//
$sql = "
    SELECT r.review_id, r.movie_id, r.rating, r.comment, r.created_at,
           m.title
    FROM reviews r
    JOIN movies m ON r.movie_id = m.movie_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviewsResult = $stmt->get_result();
$stmt->close();

// Gets user's favourites

$favSql = "
    SELECT m.movie_id, m.title, m.image_url, m.genre, m.year
    FROM user_favorites uf
    JOIN movies m ON uf.movie_id = m.movie_id
    WHERE uf.user_id = ?
    ORDER BY m.title ASC
";
$stmt = $conn->prepare($favSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favouritesResult = $stmt->get_result();
$stmt->close();
?>

<h2>My Account</h2>

<div class="mb-4">
  <h4>Account details</h4>
  <p><strong>Username:</strong> <?php echo htmlspecialchars($user['alias']); ?></p>
  <?php if (!empty($user['email'])): ?>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
  <?php endif; ?>
  <?php if (!empty($user['created_at'])): ?>
    <p><strong>Member since:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
  <?php endif; ?>
</div>

<hr>

<!-- Tabs (My reviews / My favourites) -->
<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?php echo ($tab === 'reviews') ? 'active' : ''; ?>"
       href="my_account.php?tab=reviews">
      My reviews
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($tab === 'favourites') ? 'active' : ''; ?>"
       href="my_account.php?tab=favourites">
      My favourites
    </a>
  </li>
</ul>

<?php if ($tab === 'reviews'): ?>

  <h4>My reviews</h4>

  <?php if ($reviewsResult->num_rows === 0): ?>
    <p>You haven’t written any reviews yet.</p>
  <?php else: ?>
    <div class="list-group mb-4">
      <?php while ($row = $reviewsResult->fetch_assoc()): ?>
        <div class="list-group-item">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h5>
                <a href="movie.php?id=<?php echo (int)$row['movie_id']; ?>">
                  <?php echo htmlspecialchars($row['title']); ?>
                </a>
              </h5>

              <!-- read-only Stars display  -->
              <div>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <?php if ($i <= $row['rating']): ?>
                    <span class="text-warning">&#9733;</span>
                  <?php else: ?>
                    <span class="text-secondary">&#9733;</span>
                  <?php endif; ?>
                <?php endfor; ?>
              </div>

              <?php if (!empty($row['comment'])): ?>
                <p><?php echo nl2br(htmlspecialchars($row['comment'])); ?></p>
              <?php endif; ?>

              <small class="text-muted">
                <?php echo htmlspecialchars($row['created_at']); ?>
              </small>

              <!-- Edit Form (form is hidden) -->
              <div id="edit-form-<?php echo $row['review_id']; ?>" style="display:none;" class="mt-3">
                <form method="POST" action="handlers/update_review.php">
                  <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">

                  <!-- Editable rating -->
                  <div class="mb-2">
                    <label class="form-label d-block">Rating (1–5)</label>

                    <!-- hidden input that PHP will read -->
                    <input type="hidden"
                           name="rating"
                           id="rating-value-<?php echo $row['review_id']; ?>"
                           value="<?php echo (int)$row['rating']; ?>"
                           required>

                    <!-- clickable stars, unique per review -->
                    <div id="star-rating-<?php echo $row['review_id']; ?>"
                         data-initial-rating="<?php echo (int)$row['rating']; ?>">
                      <i class="bi bi-star star" data-value="1"></i>
                      <i class="bi bi-star star" data-value="2"></i>
                      <i class="bi bi-star star" data-value="3"></i>
                      <i class="bi bi-star star" data-value="4"></i>
                      <i class="bi bi-star star" data-value="5"></i>
                    </div>

                    <small id="rating-text-<?php echo $row['review_id']; ?>" class="text-muted"></small>
                  </div>

                  <div class="mb-2">
                    <label>Comment</label>
                    <textarea name="comment" class="form-control"><?php echo htmlspecialchars($row['comment']); ?></textarea>
                  </div>

                  <button type="submit" name="update_review" class="btn btn-success btn-sm">
                    Save changes
                  </button>
                  <button type="button"
                          class="btn btn-sm btn-secondary"
                          onclick="cancelEdit(<?php echo $row['review_id']; ?>)">
                    Cancel
                  </button>
                </form>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-end">
              <!-- Toggle edit -->
              <button class="btn btn-sm btn-outline-primary mb-1"
                      onclick="toggleEdit(<?php echo $row['review_id']; ?>)">
                <i class="bi bi-pencil"></i>
              </button>

              <!-- Delete -->
              <form method="POST" action="delete_review.php">
                <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">
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

<?php elseif ($tab === 'favourites'): ?>

  <h4>My favourites</h4>

  <?php if ($favouritesResult->num_rows === 0): ?>
    <p>You don’t have any favourite movies yet.</p>
  <?php else: ?>
    <div class="row mb-4">
      <?php while ($fav = $favouritesResult->fetch_assoc()): ?>
        <div class="col-md-3 mb-3">
          <div class="card h-100">
            <?php if (!empty($fav['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($fav['image_url']); ?>"
                   class="card-img-top"
                   alt="Movie image">
            <?php endif; ?>
            <div class="card-body">
              <h6 class="card-title">
                <a href="movie.php?id=<?php echo (int)$fav['movie_id']; ?>">
                  <?php echo htmlspecialchars($fav['title']); ?>
                </a>
              </h6>
              <?php if (!empty($fav['genre']) || !empty($fav['year'])): ?>
                <p class="card-text small text-muted mb-0">
                  <?php if (!empty($fav['genre'])): ?>
                    <?php echo htmlspecialchars($fav['genre']); ?>
                  <?php endif; ?>
                  <?php if (!empty($fav['year'])): ?>
                    <?php if (!empty($fav['genre'])) echo ' • '; ?>
                    <?php echo (int)$fav['year']; ?>
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

<h4>Permanently delete my account & data</h4>

<form method="POST" action="handlers/deleted_account.php"
      onsubmit="return confirm('Are you sure you want to delete your account and all associated data? This cannot be undone.');">

  <button type="submit" class="btn btn-danger">
    Delete my account
  </button>

</form>

<!-- Toggle script -->
<script>
function toggleEdit(id) {
    const form = document.getElementById("edit-form-" + id);
    if (!form) return;
    form.style.display = (form.style.display === "none" || form.style.display === "")
        ? "block"
        : "none";
}
function cancelEdit(id) {
    const form = document.getElementById("edit-form-" + id);
    if (!form) return;
    form.style.display = "none";
}
</script>

<?php include("includes/footer.php"); ?>