<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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