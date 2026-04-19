<?php
include(__DIR__ . "/includes/db.php");

$message = "";

if (!empty($_GET['token'])) {
    $token = $_GET['token'];

    // Looks up the user by token
    $stmt = $conn->prepare("
        SELECT user_id, alias, email_verified 
        FROM users 
        WHERE verification_token = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        if ((int)$row['email_verified'] === 1) {
            $message = "Your email is already verified. You can log in.";
        } else {
            // Marks as verified and clears token
            $update = $conn->prepare("
                UPDATE users 
                SET email_verified = 1, verification_token = NULL 
                WHERE user_id = ?
            ");
            $update->bind_param("i", $row['user_id']);

            if ($update->execute()) {
                // Logs the user in immediately
                session_start();
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['alias'] = $row['alias'];

                header("Location: index.php?verified=1");
                exit;

            } else {
                $message = "Unable to verify your email. Please try again later.";
            }
        }

    } else {
        $message = "Invalid or expired verification link.";
    }

} else {
    $message = "Missing verification token.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include(__DIR__ . "/includes/header.php"); ?>

<div class="container mt-4">
    <?php if ($message): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>