<?php
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

// Handle review update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_review"])) {
    $review_id = (int)$_POST["review_id"];
    $rating = (int)$_POST["rating"];
    $comment = $_POST["comment"];

    $stmt = $conn->prepare("
        UPDATE reviews 
        SET rating = ?, comment = ? 
        WHERE review_id = ? AND user_id = ?
    ");
    $stmt->bind_param("isii", $rating, $comment, $review_id, $_SESSION["user_id"]);
    $stmt->execute();

    header("Location: my_account.php");
    exit;
}

// Require login
if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to view your account.";
    include("includes/footer.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

// Get user info
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

// Get reviews
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

            <!-- ⭐ Stars display -->
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

            <!-- ✏️ EDIT FORM (hidden) -->
            <div id="edit-form-<?php echo $row['review_id']; ?>" style="display:none;" class="mt-3">

  <form method="POST">
    <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">

    <!-- ⭐ Editable stars using Bootstrap Icons -->
    <div class="mb-2">
      <label class="form-label d-block">Rating (1–5)</label>

      <!-- hidden input that PHP will read as $_POST['rating'] -->
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
                  <button type="button"
                    class="btn btn-sm btn-secondary"
                        onclick="cancelEdit(<?php echo $row['review_id']; ?>)">
                    Cancel
                </button>
              </form>

            </div>

          </div>

          <!-- ACTION BUTTONS -->
          <div class="text-end">

            <!-- ✏️ Toggle edit -->
            <button class="btn btn-sm btn-outline-primary mb-1"
                    onclick="toggleEdit(<?php echo $row['review_id']; ?>)">
              <i class="bi bi-pencil"></i>
            </button>

            <!-- 🗑 Delete -->
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

<hr>

<h4>Delete my account and data</h4>

<form method="POST" action="deleted_account.php"
      onsubmit="return confirm('Are you sure you want to delete your account and ALL your reviews?');">
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