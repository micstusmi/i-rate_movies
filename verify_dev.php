// verify_dev.php
require_once __DIR__ . '/includes/db.php';

$userId = $_GET['id'] ?? null;

if ($userId) {
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    echo "User verified.";
}