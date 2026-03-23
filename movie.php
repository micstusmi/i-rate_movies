<?php
session_start();

include("includes/header.php");
include(__DIR__ . "/includes/db.php");

// 1) Check if ID exists in URL
if (!isset($_GET["id"])) {
    echo "Movie not found.";
    exit;
}

$id = (int)$_GET["id"];  // this will be used as movie_id

// 2) Fetch movie data first
$stmt = $conn->prepare("SELECT * FROM movies WHERE movie_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Movie not found.";
    exit;
}

$movie = $result->fetch_assoc();
$stmt->close();

// 3) Handle review submission
if (isset($_POST["submit_review"])) {

    if (!isset($_SESSION["user_id"])) {
        echo "You must be logged in to leave a review.";
        exit;
    }

    $user_id  = (int)$_SESSION["user_id"];
    $movie_id = $id; // same as in URL / used above
    $rating   = (int)$_POST["rating"];
    $comment  = $_POST["comment"];

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

    // Avoid resubmit on refresh
    header("Location: movie.php?id=" . $id);
    exit;
}
?>

<div class="row">

  <!-- Movie Image -->
  <div class="col-md-4">
    <img src="<?php echo htmlspecialchars($movie['image_url']); ?>" class="img-fluid rounded shadow" alt="Movie Image">
  </div>

  <!-- Movie Details -->
  <div class="col-md-8">
    <h2><?php echo htmlspecialchars($movie['title']); ?></h2>

    <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
    <p><strong>Year:</strong> <?php echo htmlspecialchars($movie['year']); ?></p>
    <p><strong>Actors:</strong> <?php echo htmlspecialchars($movie['actors']); ?></p>

    <hr>

    <p><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
    <hr>

    <h4>Leave a Review</h4>

    <?php if (isset($_SESSION["user_id"])): ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Rating (1–5)</label>
          <select name="rating" class="form-control" required>
            <option value="">Select rating</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
          </select>
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
      <p>Please <a href="login.php">log in</a> to leave a review.</p>
    <?php endif; ?>
  </div>
</div>

<?php include("includes/footer.php"); ?>