<?php
// search.php - full search results page

// DB and header
include __DIR__ . '/includes/db.php';
include __DIR__ . '/includes/header.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$movies = [];
if ($q !== '') {

    // Split the query into separate words/tokens
    $tokens = preg_split('/\s+/', $q);
    $conditions = [];
    $params     = [];
    $types      = '';

    foreach ($tokens as $token) {
        $token = trim($token);
        if ($token === '') {
            continue;
        }

        // If this token is a 4‑digit number, treat it as a year filter
        if (preg_match('/^\d{4}$/', $token)) {
            $conditions[] = '(year = ?)';
            $types       .= 'i';
            $params[]     = (int)$token;
        } else {
            // Otherwise, token must appear in title OR actors
            $conditions[] = '(title LIKE ? OR actors LIKE ?)';
            $types       .= 'ss';
            $like         = '%' . $token . '%';
            $params[]     = $like; // for title
            $params[]     = $like; // for actors
        }
    }

    if (!empty($conditions)) {
        // All tokens must match somewhere (AND between each token’s condition)
        $whereSql = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT movie_id, title, year, actors, image_url, description
            FROM movies
            $whereSql
            ORDER BY title
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $movies = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<h1>Search results</h1>

<?php if ($q === ''): ?>
    <p>Please enter a search term.</p>
<?php elseif (empty($movies)): ?>
    <p>No movies found for "<?php echo htmlspecialchars($q); ?>".</p>
<?php else: ?>
    <p>Found <?php echo count($movies); ?> result(s) for
       "<strong><?php echo htmlspecialchars($q); ?></strong>":</p>

    <div class="row">
        <?php foreach ($movies as $movie): ?>
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <?php if (!empty($movie['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($movie['image_url']); ?>"
                             class="card-img-top"
                             alt="<?php echo htmlspecialchars($movie['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="movie.php?id=<?php echo (int)$movie['movie_id']; ?>">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </a>
                        </h5>
                        <p class="card-text mb-1">
                            <small><?php echo htmlspecialchars($movie['year']); ?></small>
                        </p>
                        <p class="card-text">
                            <small><?php echo htmlspecialchars($movie['actors']); ?></small>
                        </p>
                        <p class="card-text">
                            <?php echo htmlspecialchars(substr($movie['description'], 0, 100)); ?>...
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>