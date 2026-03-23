<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reviewCount = 0;

if (isset($_SESSION["user_id"])) {
    include(__DIR__ . "/db.php");

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $reviewCount = $row["total"];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>i-rate Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container d-flex justify-content-between">
    
    <a class="navbar-brand" href="index.php">i-rate Movies</a>

    <div>
      <?php if (isset($_SESSION["user_id"])): ?>
        <span class="text-white me-3">
            Hello, <?php echo $_SESSION["alias"]; ?>
            <?php if ($reviewCount >= 11): ?>
    <span class="badge bg-warning text-dark ms-2">⭐ Super Reviewer</span>
  <?php endif; ?>
</span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-success btn-sm me-2">Login</a>
        <a href="register.php" class="btn btn-primary btn-sm">Register</a>
      <?php endif; ?>
    </div>

  </div>
</nav>

<div class="container mt-4">