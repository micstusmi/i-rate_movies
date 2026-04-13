<?php
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

$isYear = preg_match('/^\d{4}$/', $q);

$sql = "
    SELECT movie_id, title, year
    FROM movies
    WHERE
        title LIKE ?
        OR actors LIKE ?
        " . ($isYear ? " OR year = ? " : "") . "
    ORDER BY title
    LIMIT 10
";

$stmt = $conn->prepare($sql);
$like = '%' . $q . '%';

if ($isYear) {
    $stmt->bind_param('ssi', $like, $like, $q);
} else {
    $stmt->bind_param('ss', $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($movies);