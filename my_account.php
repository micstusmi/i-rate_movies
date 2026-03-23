<?php
session_start();

include("includes/header.php");
include(__DIR__ . "/includes/db.php");

// Require login
if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to view your account.";
    include("includes/footer.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];

// Fetch basic user info (adjust column names to your users table)
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE user_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
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

// Fetch this user's reviews with movie titles
$sql = "
    SELECT r.review_id, r.movie_id, r.rating, r.comment, r.created_at,
           m.title
    FROM reviews r
    JOIN movies m ON r.movie_id = m.movie_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reviewsResult = $stmt->get_result();
$stmt->close();
?>

<h2>My Account</h2>

<div class="mb-4">
  <h4>Account details</h4>
  <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
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
            <h5 class="mb-1">
              <a href="movie.php?id=<?php echo (int)$row['movie_id']; ?>">
                <?php echo htmlspecialchars($row['title']); ?>
              </a>
            </h5>
            <p class="mb-1">
              <strong>Rating:</strong> <?php echo (int)$row['rating']; ?>/5
            </p>
            <?php if (!empty($row['comment'])): ?>
              <p class="mb-1">
                <?php echo nl2br(htmlspecialchars($row['comment'])); ?>
              </p>
            <?php endif; ?>
            <?php if (!empty($row['created_at'])): ?>
              <small class="text-muted">
                Posted: <?php echo htmlspecialchars($row['created_at']); ?>
              </small>
            <?php endif; ?>
          </div>
          <div class="ms-3 text-end">
            <a href="edit_review.php?id=<?php echo (int)$row['review_id']; ?>" class="btn btn-sm btn-outline-primary mb-1">
              Edit
            </a>
            <form method="POST" action="delete_review.php" class="d-inline">
              <input type="hidden" name="review_id" value="<?php echo (int)$row['review_id']; ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"
                      onclick="return confirm('Delete this review? This cannot be undone.');">
                Delete
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
<p class="text-muted">
  Deleting your account will permanently remove your login and all your reviews.
  This cannot be undone and may change the overall ratings of movies you reviewed.
</p>

<form method="POST" action="delete_account.php" onsubmit="return confirm('Are you sure you want to delete your account and ALL your reviews? This cannot be undone.');">
  <button type="submit" class="btn btn-danger">
    Delete my account
  </button>
</form>

<?php include("includes/footer.php"); ?>
