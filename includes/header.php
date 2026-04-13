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

    <style>
        /* NEW & PROMOTIONS STRIPS */
        .horizontal-strip {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            gap: 0.4rem !important;
            padding-bottom: 0.25rem !important;
        }

        .horizontal-strip::-webkit-scrollbar {
            height: 4px;
        }

        .horizontal-strip::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 2px;
        }

        .strip-item-card {
            flex: 0 0 auto !important;
            width: 90px !important;
        }

        .strip-item-card img {
            height: 120px !important;
            object-fit: cover;
        }

        .strip-item-title {
            font-size: 0.65rem !important;
            margin: 0 !important;
            line-height: 1.1;
            display: -webkit-box;
            -webkit-line-clamp: 2;      /* show max 2 lines */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Tighten card body inside strips */
        .horizontal-strip .strip-item-card .card-body {
            padding: 0.15rem 0.25rem !important;
        }

        /* Remove default bottom spacing inside card body */
        .horizontal-strip .strip-item-card .card-body > *:last-child {
            margin-bottom: 0 !important;
        }

        .strip-heading {
            font-size: 0.85rem !important;
            margin-bottom: 0.3rem !important;
        }
    </style>
</head>
<body class="bg-gray">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container d-flex justify-content-between">

    <!-- Brand -->
    <a class="navbar-brand" href="index.php">i-rate Movies</a>

    <!-- Right-hand side navigation -->
    <div class="d-flex align-items-center">

      <!-- Shared links: always visible -->
      <ul class="navbar-nav flex-row me-3">
        <li class="nav-item me-3">
          <a class="nav-link text-dark p-0" href="index.php">Home</a>
        </li>
        <li class="nav-item me-3">
          <a class="nav-link text-dark p-0" href="about_us.php">About</a>
        </li>

        <?php if (isset($_SESSION["user_id"])): ?>
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="my_account.php?tab=favourites">Favourites</a>
          </li>
          <li class="nav-item me-3">
            <a class="nav-link text-dark p-0" href="my_account.php?tab=reviews">Account</a>
          </li>
        <?php endif; ?>
      </ul>

      <?php if (isset($_SESSION["user_id"])): ?>

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

        <!-- Login / Register buttons when not logged in -->
        <a href="login.php" class="btn btn-success btn-sm me-2">Login</a>
        <a href="register.php" class="btn btn-primary btn-sm">Register</a>

      <?php endif; ?>

    </div>
  </div>
</nav>

<div class="container mt-4">

<!-- jQuery and filter JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function () {
    const $form       = $('#filterForm');
    const $genreInput = $('#filterGenre');
    const $yearInput  = $('#filterYear');
    const $sortInput  = $('#filterSort');

    // Genre buttons
    $(document).on('click', '.genre-btn', function () {
        $genreInput.val($(this).data('genre'));
        $('.genre-btn').removeClass('active');
        $(this).addClass('active');
        $form.submit();
    });

    // Year buttons
    $(document).on('click', '.year-btn', function () {
        $yearInput.val($(this).data('year'));
        $('.year-btn').removeClass('active');
        $(this).addClass('active');
        $form.submit();
    });

    // Sort select
    $('#sortSelect').on('change', function () {
        $sortInput.val($(this).val());
        $form.submit();
    });
});
</script>