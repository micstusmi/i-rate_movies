<?php
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

// Reads filter/sort values from query string

$genre = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$year  = isset($_GET['year'])  ? $_GET['year']  : 'all';
$sort  = isset($_GET['sort'])  ? $_GET['sort']  : 'random';

// Gets distinct genres from movies

$genresResult = $conn->query("
    SELECT DISTINCT genre
    FROM movies
    WHERE genre IS NOT NULL AND genre <> ''
    ORDER BY genre
");
$genres = [];
while ($row = $genresResult->fetch_assoc()) {
    $genres[] = $row['genre'];
}

// Defines static year ranges

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

// Builds WHERE conditions

$where  = [];
$params = [];
$types  = "";

// Genre filter
if ($genre !== 'all' && $genre !== '') {
    $where[]   = "genre = ?";
    $types    .= "s";
    $params[]  = $genre;
}

// Year range filter
if ($year !== 'all' && isset($yearRanges[$year])) {
    [$startYear, $endYear] = $yearRanges[$year];
    $where[]   = "`year` BETWEEN ? AND ?";
    $types    .= "ii";
    $params[]  = $startYear;
    $params[]  = $endYear;
}

$whereSql = "";
if (!empty($where)) {
    $whereSql = "WHERE " . implode(" AND ", $where);
}

// Sort order
switch ($sort) {
    case 'rating_desc':
        $orderBy = "ORDER BY avg_rating DESC, title ASC";
        break;
    case 'year_asc':
        $orderBy = "ORDER BY `year` ASC, title ASC";
        break;
    case 'year_desc':
        $orderBy = "ORDER BY `year` DESC, title ASC";
        break;
    case 'random':
    default:
        $orderBy = "ORDER BY RAND()";
        break;
}

// Final query
$sql = "SELECT * FROM movies $whereSql $orderBy";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<h2 class="mb-4">Movies</h2>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Filter</strong>
            </div>
            <div class="card-body">
                <!-- One form to control genre, year, sort together -->
                <form method="get">
                    <!-- Genre filter -->
                    <div class="mb-3">
                        <h6 class="mb-2">Genre</h6>
                        <div class="list-group">
                            <button
                                type="submit"
                                name="genre"
                                value="all"
                                class="list-group-item list-group-item-action <?php echo ($genre === 'all') ? 'active' : ''; ?>">
                                All
                            </button>
                            <?php foreach ($genres as $g): ?>
                                <button
                                    type="submit"
                                    name="genre"
                                    value="<?php echo htmlspecialchars($g); ?>"
                                    class="list-group-item list-group-item-action <?php echo ($genre === $g) ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($g); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Year range filter -->
                    <div class="mb-3">
                        <h6 class="mb-2">Year Made</h6>
                        <div class="list-group">
                            <button
                                type="submit"
                                name="year"
                                value="all"
                                class="list-group-item list-group-item-action <?php echo ($year === 'all') ? 'active' : ''; ?>">
                                All Years
                            </button>
                            <?php foreach ($yearRanges as $label => $range): ?>
                                <button
                                    type="submit"
                                    name="year"
                                    value="<?php echo $label; ?>"
                                    class="list-group-item list-group-item-action <?php echo ($year === $label) ? 'active' : ''; ?>">
                                    <?php echo $label; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sort by -->
                    <div class="mb-3">
                        <h6 class="mb-2">Sort by</h6>

                        <!-- Preserve current genre/year when changing sort -->
                        <input type="hidden" name="genre" value="<?php echo htmlspecialchars($genre); ?>">
                        <input type="hidden" name="year"  value="<?php echo htmlspecialchars($year); ?>">

                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="random" <?php echo ($sort === 'random') ? 'selected' : ''; ?>>
                                Random
                                </option>
                            <option value="rating_desc" <?php echo ($sort === 'rating_desc') ? 'selected' : ''; ?>>
                                Highest Rated
                            </option>
                            <option value="year_asc" <?php echo ($sort === 'year_asc') ? 'selected' : ''; ?>>
                                Year Made (Oldest First)
                            </option>
                        <option value="year_desc" <?php echo ($sort === 'year_desc') ? 'selected' : ''; ?>>
        Year Made (Newest First)
    </option>
</select>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Movies grid -->
    <div class="col-md-9">
        <div class="row">
            <?php if ($result->num_rows === 0): ?>
                <div class="col-12">
                    <p>No movies found for the selected filters.</p>
                </div>
            <?php else: ?>
                <?php while ($movie = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow p-3 h-100">
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
                                        $avg = isset($movie['avg_rating']) ? (float)$movie['avg_rating'] : 0;
                                    ?>
                                    <small class="d-block mb-1">
                                        Rating: <?php echo number_format($avg, 1); ?>
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