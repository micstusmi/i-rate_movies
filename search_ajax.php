<?php
require 'config.php';
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
        title LIKE :q
        OR actors LIKE :q
        " . ($isYear ? " OR year = :year " : "") . "
    ORDER BY title
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$like = '%' . $q . '%';
$stmt->bindParam(':q', $like, PDO::PARAM_STR);
if ($isYear) {
    $stmt->bindParam(':year', $q, PDO::PARAM_INT);
}
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($movies);