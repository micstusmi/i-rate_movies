<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$reviewCount = 0;

// db.php is in the same includes folder as this file
if (isset($_SESSION["user_id"])) {
    include __DIR__ . "/db.php";

    $reviewCountStmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    $reviewCountStmt->bind_param("i", $_SESSION["user_id"]);
    $reviewCountStmt->execute();
    $reviewCountResult = $reviewCountStmt->get_result();
    $reviewCountRow = $reviewCountResult->fetch_assoc();
    $reviewCountStmt->close();

    $reviewCount = (int)$reviewCountRow["total"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .horizontal-strip .strip-item-card .card-body {
            padding: 0.15rem 0.25rem !important;
        }

        .horizontal-strip .strip-item-card .card-body > *:last-child {
            margin-bottom: 0 !important;
        }

        .strip-heading {
            font-size: 0.85rem !important;
            margin-bottom: 0.3rem !important;
        }

        /* GLOBAL SEARCH IN NAVBAR */
        .navbar {
            position: relative;
        }

        .global-search-form {
            flex: 1;
            display: flex;
            justify-content: center;
            position: relative;
            margin: 0 1rem;
        }

        .global-search-form input[type="text"] {
            width: 60%;
            max-width: 400px;
            padding: 4px 8px;
            border-radius: 4px 0 0 4px;
            border: 1px solid #ccc;
            font-size: 0.9rem;
        }

        .global-search-form button {
            padding: 4px 10px;
            border-radius: 0 4px 4px 0;
            border: 1px solid #ccc;
            border-left: none;
            font-size: 0.9rem;
            background-color: #f8f9fa;
        }

        .global-search-results {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            max-width: 400px;
            background: #fff;
            color: #000;
            border: 1px solid #ccc;
            border-top: none;
            display: none;
            z-index: 1000;
        }

        .global-search-results .result-item {
            display: block;
            padding: 6px 10px;
            text-decoration: none;
            color: inherit;
            font-size: 0.9rem;
        }

        .global-search-results .result-item:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body class="bg-gray">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container d-flex align-items-center">

    <!-- Brand -->
    <a class="navbar-brand me-3" href="index.php">i-rate Movies</a>

    <!-- GLOBAL SEARCH: centered in navbar -->
    <form id="global-search-form" class="global-search-form" action="search.php" method="get">
        <input
            type="text"
            id="global-search-input"
            name="q"
            placeholder="Search by title, actor, or year"
            autocomplete="off"
            value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
        >
        <button type="submit">Search</button>
        <div id="global-search-results" class="global-search-results"></div>
    </form>

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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- FILTER JS (your existing code) -->
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

<!-- GLOBAL SEARCH AJAX -->
<script>
$(function () {
    let timeout = null;

    $('#global-search-input').on('keyup', function () {
        clearTimeout(timeout);
        const q = $(this).val().trim();

        if (q.length < 2) {
            $('#global-search-results').empty().hide();
            return;
        }

        timeout = setTimeout(function () {
            $.ajax({
                url: 'search_ajax.php',
                data: { q: q },
                dataType: 'json',
                success: function (data) {
                    let html = '';
                    if (data.length === 0) {
                        html = '<div class="result-item">No results</div>';
                    } else {
                        data.forEach(function (movie) {
                            html += '<a class="result-item" href="movie.php?id='
                                  + movie.movie_id + '">'
                                  + movie.title + ' (' + movie.year + ')</a>';
                        });
                    }
                    $('#global-search-results').html(html).show();
                }
            });
        }, 300);
    });

    // Hide dropdown when clicking elsewhere
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#global-search-form').length) {
            $('#global-search-results').hide();
        }
    });
});
</script>