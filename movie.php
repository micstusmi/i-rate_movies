<?php
include("includes/header.php");
include(__DIR__ . "/includes/db.php");

if (isset($_POST["submit_review"])) {

    $user_id = $_SESSION["user_id"];
    $movie_id = $id; // already from URL
    $rating = $_POST["rating"];
    $comment = $_POST["comment"];

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, movie_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $movie_id, $rating, $comment);
    $stmt->execute();
}

// Check if ID exists
if (!isset($_GET["id"])) {
    echo "Movie not found.";
    exit;
}

$id = $_GET["id"];

// Fetch movie
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Movie not found.";
    exit;
}

$movie = $result->fetch_assoc();
?>

<div class="row">
  
  <!-- Movie Image -->
  <div class="col-md-4">
    <img src="<?php echo $movie['image_url']; ?>" class="img-fluid rounded shadow">
  </div>

  <!-- Movie Details -->
  <div class="col-md-8">
    <h2><?php echo $movie['title']; ?></h2>
    
    <p><strong>Genre:</strong> <?php echo $movie['genre']; ?></p>
    <p><strong>Year:</strong> <?php echo $movie['year']; ?></p>
    <p><strong>Actors:</strong> <?php echo $movie['actors']; ?></p>

    <hr>

    <p><?php echo $movie['description']; ?></p>
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