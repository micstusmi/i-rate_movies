<?php
// search_ajax.php - live search endpoint
include __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

// Split into tokens
$tokens = preg_split('/\s+/', $q);
$conditions = [];
$params     = [];
$types      = '';

foreach ($tokens as $token) {
    $token = trim($token);
    if ($token === '') {
        continue;
    }

    if (preg_match('/^\d{4}$/', $token)) {
        $conditions[] = '(year = ?)';
        $types       .= 'i';
        $params[]     = (int)$token;
    } else {
        $conditions[] = '(title LIKE ? OR actors LIKE ?)';
        $types       .= 'ss';
        $like         = '%' . $token . '%';
        $params[]     = $like;
        $params[]     = $like;
    }
}

if (empty($conditions)) {
    echo json_encode([]);
    exit;
}

$whereSql = 'WHERE ' . implode(' AND ', $conditions);

$sql = "
    SELECT movie_id, title, year
    FROM movies
    $whereSql
    ORDER BY title
    LIMIT 10
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$movies = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($movies);