<?php
include("includes/header.php");
include(__DIR__ . "/includes/db.php");

// Fetch movies
$result = $conn->query("SELECT * FROM movies");
?>

<h2 class="mb-4">Movies</h2>

<div class="row">
<?php while ($movie = $result->fetch_assoc()): ?>
  
  <div class="col-md-4 mb-4">
    <div class="card shadow p-3">
      
      <img src="<?php echo $movie['image_url']; ?>" class="card-img-top" alt="Movie Image">
      
      <div class="card-body">
        <h5 class="card-title"><?php echo $movie['title']; ?></h5>
        <p class="card-text">
          <?php echo substr($movie['description'], 0, 100); ?>...
        </p>
      </div>

      <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="badge bg-secondary"><?php echo $movie['genre']; ?></span>
        <a href="movie.php?id=<?php echo $movie['movie_id']; ?>" class="btn btn-primary btn-sm">
          View
        </a>
      </div>

    </div>
  </div>

<?php endwhile; ?>
</div>

<?php include("includes/footer.php"); ?>