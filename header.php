<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reviewCount = 0;

if (isset($_SESSION["user_id"])) {
    include(__DIR__ . "/db.php");

    $reviewCountStmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $reviewCountStmt->bind_param("i", $_SESSION["user_id"]);
    $reviewCountStmt->execute();
    $reviewCountResult = $reviewCountStmt->get_result();
    $reviewCountRow = $reviewCountResult->fetch_assoc();
    $reviewCountStmt->close();

    $reviewCount = $reviewCountRow["total"];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>i-rate Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
</head>
<body class="bg-gray">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container d-flex justify-content-between">

    <!-- Brand -->
    <a class="navbar-brand" href="index.php">i-rate Movies</a>

    <!-- Right-hand side navigation -->
    <div class="d-flex align-items-center">

      <?php if (isset($_SESSION["user_id"])): ?>
        <!-- Main links -->
        <ul class="navbar-nav flex-row me-3">
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="index.php">Home</a>
          </li>
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="my_account.php?tab=favourites">Favourites</a>
          </li>
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="my_account.php?tab=reviews">Account</a>
          </li>
        </ul>

        <!-- Greeting and badge -->
        <span class="text-dark me-3">
          Hello,
          <a href="my_account.php" class="text-dark text-decoration-underline">
            <?php echo htmlspecialchars($_SESSION["alias"]); ?>
          </a>
          <?php if ($reviewCount >= 11): ?>
            <span class="badge bg-warning text-dark ms-2">⭐ Super Reviewer</span>
          <?php endif; ?>
        </span>

        <!-- Logout button -->
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>

      <?php else: ?>

        <!-- When not logged in: Home, plus Login/Register Buttons -->
        <ul class="navbar-nav flex-row me-3">
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="index.php">Home</a>
          </li>
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="about.php">About</a>
          </li>
        </ul>

        <a href="login.php" class="btn btn-success btn-sm me-2">Login</a>
        <a href="register.php" class="btn btn-primary btn-sm">Register</a>

      <?php endif; ?>

    </div>
  </div>
</nav>

<div class="container mt-4">