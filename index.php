<?php
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

// 1. Read filters / sort from query string
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$selectedYear  = isset($_GET['year'])  ? $_GET['year']  : 'all';
$sortOrder     = isset($_GET['sort'])  ? $_GET['sort']  : 'random';

// 2. Get distinct genres from movies
$genresResult = $conn->query("
    SELECT DISTINCT genre
    FROM movies
    WHERE genre IS NOT NULL AND genre <> ''
    ORDER BY genre
");
$genres = [];
while ($genreRow = $genresResult->fetch_assoc()) {
    $genres[] = $genreRow['genre'];
}

// 3. Define year ranges
$yearRanges = [
    '1941-1950' => [1941, 1950],
    '1951-1960' => [1951, 1960],
    '1961-1970' => [1961, 1970],
    '1971-1980' => [1971, 1980],
    '1981-1990' => [1981, 1990],
    '1991-2000' => [1991, 2000],
    '2001-2010' => [2001, 2010],
    '2011-2020' => [2011, 2020],
    '2021-2030' => [2021, 2030],
];

// 4. Build WHERE conditions
$whereClauses = [];
$queryParams  = [];
$paramTypes   = "";

// Genre filter
if ($selectedGenre !== 'all' && $selectedGenre !== '') {
    $whereClauses[] = "genre = ?";
    $paramTypes    .= "s";
    $queryParams[]  = $selectedGenre;
}

// Year range filter
if ($selectedYear !== 'all' && isset($yearRanges[$selectedYear])) {
    [$startYear, $endYear] = $yearRanges[$selectedYear];
    $whereClauses[] = "`year` BETWEEN ? AND ?";
    $paramTypes    .= "ii";
    $queryParams[]  = $startYear;
    $queryParams[]  = $endYear;
}

$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

// 5. Promotions extra filter when sort == promotions
$promoClause = "";
if ($sortOrder === 'promotions') {
    $promoClause = empty($whereClauses) ? "WHERE is_promo = 1" : " AND is_promo = 1";
}

// 6. Sort order
switch ($sortOrder) {
    case 'new':
        $orderBySql = "ORDER BY created_at DESC, title ASC";
        break;

    case 'promotions':
        $orderBySql = "ORDER BY created_at DESC, title ASC";
        break;

    case 'rating_desc':
        // adjust if you don't actually have avg_rating
        $orderBySql = "ORDER BY avg_rating DESC, title ASC";
        break;

    case 'year_asc':
        $orderBySql = "ORDER BY `year` ASC, title ASC";
        break;

    case 'year_desc':
        $orderBySql = "ORDER BY `year` DESC, title ASC";
        break;

    case 'random':
    default:
        $orderBySql = "ORDER BY RAND()";
        break;
}

// 7. Data for horizontal strips (independent of sidebar filters)

// Promotions strip (top left)
$promoStripSql    = "SELECT * FROM movies WHERE is_promo = 1 ORDER BY created_at DESC LIMIT 10";
$promoStripResult = $conn->query($promoStripSql);

// New arrivals strip (top right)
$newStripSql    = "SELECT * FROM movies ORDER BY created_at DESC LIMIT 10";
$newStripResult = $conn->query($newStripSql);

// 8. Final query for main grid
$moviesQuerySql = "SELECT * FROM movies $whereSql $promoClause $orderBySql";
$moviesStmt     = $conn->prepare($moviesQuerySql);

if (!empty($queryParams)) {
    $moviesStmt->bind_param($paramTypes, ...$queryParams);
}

$moviesStmt->execute();
$moviesResult = $moviesStmt->get_result();
?>

<!-- Top row with two horizontal strips -->
<div class="row mb-3">
    <!-- Promotions strip (top left) -->
    <div class="col-md-6 mb-3">
        <h5 class="strip-heading">Promotions</h5>
        <?php if ($promoStripResult && $promoStripResult->num_rows): ?>
            <div class="horizontal-strip">
                <?php while ($movie = $promoStripResult->fetch_assoc()): ?>
                    <div class="strip-item-card card shadow-sm">
                        <a href="movie.php?id=<?php echo (int)$movie['movie_id']; ?>" class="stretched-link"></a>
                        <img src="<?php echo htmlspecialchars($movie['image_url']); ?>"
                             class="card-img-top"
                             alt="Movie Poster">
                        <div class="card-body">
                            <p class="strip-item-title"
                               title="<?php echo htmlspecialchars($movie['title']); ?>">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">No promotions currently.</p>
        <?php endif; ?>
    </div>

    <!-- New Arrivals strip (top right) -->
    <div class="col-md-6 mb-3">
        <h5 class="strip-heading">New Arrivals</h5>
        <?php if ($newStripResult && $newStripResult->num_rows): ?>
            <div class="horizontal-strip">
                <?php while ($movie = $newStripResult->fetch_assoc()): ?>
                    <div class="strip-item-card card shadow-sm">
                        <a href="movie.php?id=<?php echo (int)$movie['movie_id']; ?>" class="stretched-link"></a>
                        <img src="<?php echo htmlspecialchars($movie['image_url']); ?>"
                             class="card-img-top"
                             alt="Movie Poster">
                        <div class="card-body">
                            <p class="strip-item-title"
                               title="<?php echo htmlspecialchars($movie['title']); ?>">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">No new movies yet.</p>
        <?php endif; ?>
    </div>
</div>

<h2 class="mb-2">Movies</h2>

<?php
// Nice, human-readable "currently viewing" text
$genreLabel = ($selectedGenre === 'all') ? 'All genres' : $selectedGenre;
$yearLabel  = ($selectedYear === 'all')  ? 'All years'  : $selectedYear;

switch ($sortOrder) {
    case 'new':
        $sortLabel = 'sorted by New Movies';
        break;
    case 'promotions':
        $sortLabel = 'showing Promotions';
        break;
    case 'rating_desc':
        $sortLabel = 'sorted by Highest Rated';
        break;
    case 'year_asc':
        $sortLabel = 'sorted by Year (Oldest First)';
        break;
    case 'year_desc':
        $sortLabel = 'sorted by Year (Newest First)';
        break;
    case 'random':
    default:
        $sortLabel = 'in Random order';
        break;
}
?>

<p class="text-muted mb-3">
    Currently viewing:
    <strong><?php echo htmlspecialchars($genreLabel); ?></strong>,
    <strong><?php echo htmlspecialchars($yearLabel); ?></strong>
    <span>(<?php echo $sortLabel; ?>)</span>
</p>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Filter</strong>
            </div>
            <div class="card-body">
                <!-- One form to control genre, year, sort together -->
                <form method="get" id="filterForm">
                    <!-- Single source of truth for current filters -->
                    <input type="hidden" name="genre" id="filterGenre" value="<?php echo htmlspecialchars($selectedGenre); ?>">
                    <input type="hidden" name="year"  id="filterYear"  value="<?php echo htmlspecialchars($selectedYear); ?>">
                    <input type="hidden" name="sort"  id="filterSort"  value="<?php echo htmlspecialchars($sortOrder); ?>">

                    <!-- Genre filter -->
                    <div class="mb-3">
                        <h6 class="mb-2">Genre</h6>
                        <div class="list-group">
                            <button
                                type="button"
                                class="list-group-item list-group-item-action genre-btn <?php echo ($selectedGenre === 'all') ? 'active' : ''; ?>"
                                data-genre="all">
                                All
                            </button>
                            <?php foreach ($genres as $genre): ?>
                                <button
                                    type="button"
                                    class="list-group-item list-group-item-action genre-btn <?php echo ($selectedGenre === $genre) ? 'active' : ''; ?>"
                                    data-genre="<?php echo htmlspecialchars($genre); ?>">
                                    <?php echo htmlspecialchars($genre); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Year range filter -->
                    <div class="mb-3">
                        <h6 class="mb-2">Year Made</h6>
                        <div class="list-group">
                            <button
                                type="button"
                                class="list-group-item list-group-item-action year-btn <?php echo ($selectedYear === 'all') ? 'active' : ''; ?>"
                                data-year="all">
                                All Years
                            </button>
                            <?php foreach ($yearRanges as $label => $range): ?>
                                <button
                                    type="button"
                                    class="list-group-item list-group-item-action year-btn <?php echo ($selectedYear === $label) ? 'active' : ''; ?>"
                                    data-year="<?php echo $label; ?>">
                                    <?php echo $label; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sort by -->
                    <!-- Sort by -->
                    <div class="mb-3">
                        <h6 class="mb-2">Sort by</h6>
                        <select id="sortSelect" class="form-select">
                        <option value="random"     <?php echo ($sortOrder === 'random') ? 'selected' : ''; ?>>Random</option>
                        <option value="new"        <?php echo ($sortOrder === 'new') ? 'selected' : ''; ?>>New Movies</option>
                        <option value="promotions" <?php echo ($sortOrder === 'promotions') ? 'selected' : ''; ?>>Promotions</option>
                        <option value="rating_desc" <?php echo ($sortOrder === 'rating_desc') ? 'selected' : ''; ?>>Highest Rated</option>
                        <option value="year_asc"    <?php echo ($sortOrder === 'year_asc') ? 'selected' : ''; ?>>Year Made (Oldest First)</option>
                        <option value="year_desc"   <?php echo ($sortOrder === 'year_desc') ? 'selected' : ''; ?>>Year Made (Newest First)</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Movies grid -->
    <div class="col-md-9">
        <div class="row">
            <?php if (!$moviesResult->num_rows): ?>
                <div class="col-12">
                    <p>No movies found for the selected filters.</p>
                </div>
            <?php else: ?>
                <?php while ($movie = $moviesResult->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow p-3 h-100">
                            <a href="movie.php?id=<?php echo (int)$movie['movie_id']; ?>" class="stretched-link"></a>
                            <img src="<?php echo htmlspecialchars($movie['image_url']); ?>"
                                 class="card-img-top"
                                 alt="Movie Image">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </h5>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($movie['description'], 0, 100)); ?>...
                                </p>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($movie['genre']); ?>
                                    </span>
                                    <?php if (!empty($movie['year'])): ?>
                                        <span class="badge bg-light text-dark ms-1">
                                            <?php echo (int)$movie['year']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <?php
                                        $averageRating = isset($movie['avg_rating']) ? (float)$movie['avg_rating'] : 0;
                                    ?>
                                    <small class="d-block mb-1">
                                        Rating: <?php echo number_format($averageRating, 1); ?>
                                    </small>
                                    <a href="movie.php?id=<?php echo (int)$movie['movie_id']; ?>" class="btn btn-primary btn-sm">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>