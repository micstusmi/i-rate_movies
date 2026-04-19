<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/includes/db.php";

$userId = (int)$_SESSION["user_id"];

$activeTab = $_GET['tab'] ?? 'reviews';
if (!in_array($activeTab, ['reviews', 'settings', 'favourites'], true)) {
    $activeTab = 'reviews';
}

$userQuery = $conn->prepare("
    SELECT alias, email, created_at
    FROM users
    WHERE user_id = ?
");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userQuery->close();

if ($userResult->num_rows == 0) {
    echo "User not found.";
    exit;
}

$userData = $userResult->fetch_assoc();


// Gets user's reviews

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


// Gets user's favourites

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_password"])) {

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword     = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $commonPasswords = ['password', '123456', 'qwerty'];


    if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
        $_SESSION['flash_message'] = "All fields are required.";

    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['flash_message'] = "New passwords do not match.";

    } elseif (strlen($newPassword) < 8) {
        $_SESSION['flash_message'] = "Password must be at least 8 characters.";

    } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $_SESSION['flash_message'] = "Password must contain at least one letter and one number.";

    } elseif (in_array(strtolower($newPassword), $commonPasswords)) {
    $_SESSION['flash_message'] = "Please choose a stronger password.";

    } else {
        // DB logic

        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($currentPassword, $user["password"])) {
            $_SESSION['flash_message'] = "Current password is incorrect.";

        } elseif (password_verify($newPassword, $user["password"])) {
            $_SESSION['flash_message'] = "New password must be different from current password.";

                } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update->bind_param("si", $newHash, $userId);

            if ($update->execute()) {
                $_SESSION['flash_message'] = "Password updated successfully!";
                header("Location: my_account.php?tab=settings#password-anchor"); 
                exit;
            } else {
                $_SESSION['flash_message'] = "Error updating password.";
                header("Location: my_account.php?tab=settings#password-anchor");
                exit;
            }
            $update->close();
        }
    }
    // Redirects to the Change Password area on the my_account.php page if there is an error so that the user can see the flash message.
    header("Location: my_account.php?tab=settings#password-anchor");
    exit;
}

include(__DIR__ . "/includes/header.php");
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

<!-- Tabs (My reviews / My favourites / Account Settings) -->
<ul class="nav nav-tabs mb-3">
  <li class="nav-item">
    <a class="nav-link <?php echo ($activeTab === 'settings') ? 'active' : ''; ?>" href="my_account.php?tab=settings#password-anchor">Account Settings</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($activeTab === 'reviews') ? 'active' : ''; ?>" href="my_account.php?tab=reviews">My reviews</a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?php echo ($activeTab === 'favourites') ? 'active' : ''; ?>" href="my_account.php?tab=favourites">My favourites</a>
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

<hr>

<?php if ($activeTab === 'settings'): ?>
  <div id="password-anchor" class="card shadow-sm mt-3">
    <div class="card-header bg-light py-3">
      <h4 class="mb-0 text-secondary">Account Settings</h4>
    </div>
    <div class="card-body p-4">

      <!-- Accessible flash message for any form submission results -->
      <?php if (isset($_SESSION['flash_message'])): 
          $isSuccess = (strpos($_SESSION['flash_message'], 'successfully') !== false);
          $alertClass = $isSuccess ? 'alert-success' : 'alert-danger';
          $icon = $isSuccess ? 'bi-check-circle-fill' : 'bi-exclamation-octagon-fill';
      ?>
          <div class="alert <?php echo $alertClass; ?> d-flex align-items-center alert-dismissible fade show" role="alert">
              <i class="bi <?php echo $icon; ?> me-2 fs-5"></i>
              <div>
                <strong><?php echo $isSuccess ? 'Success: ' : 'Error: '; ?></strong>
                <?php echo htmlspecialchars($_SESSION['flash_message']); unset($_SESSION['flash_message']); ?>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      <?php endif; ?>

      <!-- Change Password Form -->
      <section class="mb-5">
        <h5 class="mb-3">Change Password</h5>
        <form method="POST">
            <!-- Current Password -->
            <div class="mb-3">
                <label class="form-label fw-bold">Current Password</label>
                <div class="input-group">
                    <input type="password" name="current_password" id="cur-pass" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('cur-pass', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <!-- New Password -->
            <div class="mb-3">
                <label class="form-label fw-bold">New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="new-pass" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('new-pass', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text">Must be 8+ characters with a letter and a number.</div>
            </div>

            <!-- Confirm New Password -->
            <div class="mb-4">
                <label class="form-label fw-bold">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm-pass" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm-pass', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
        </form>
      </section>

      <hr class="my-5">

      <!-- DELETE ACCOUNT SECTION -->
      <section class="border border-danger p-4 rounded bg-white">
          <h5 class="text-danger fw-bold mb-4">
              DELETE ACCOUNT
          </h5>

          <p class="text-muted small">
              <strong>BEWARE!</strong> Deleting your account will permanently remove all data including reviews / favourites, etc. This action cannot be undone.
          </p>
          
          <button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
             <i class="bi bi-trash3 me-2"></i>Delete My Account
          </button>
      </section>

    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Confirm Account Deletion</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Are you absolutely sure? All your movie reviews and favourites will be permanently removed.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <form action="handlers/delete_account.php" method="POST">
              <button type="submit" class="btn btn-danger">Yes, Delete Everything</button>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

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

<?php include __DIR__ . "/includes/footer.php"; ?>