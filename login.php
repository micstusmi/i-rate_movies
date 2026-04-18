<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$message = "";

// Password throttle (if too many failed attempts).
$MAX_ATTEMPTS   = 5;   // No. of failed logins before locking the user out
$WINDOW_SECONDS = 600; // 10 minutes locked out before resetting the time window

// In‑session throttle (per browser)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

// Clean up old attempts
$_SESSION['login_attempts'] = array_filter(
    $_SESSION['login_attempts'],
    function ($t) use ($WINDOW_SECONDS) {
        return $t > time() - $WINDOW_SECONDS;
    }
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // if they already failed too many times in the last 10 minutes
    if (count($_SESSION['login_attempts']) >= $MAX_ATTEMPTS) {
        $message = "Too many failed login attempts. Please wait 10 minutes before trying again.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, password, alias FROM users WHERE email = ?");
        if (!$stmt) {
            die("Database error: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Error message
        $errorText = "Invalid email or password.";

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {
                // Reset attempts on success
                $_SESSION['login_attempts'] = [];

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["alias"]   = $user["alias"];

                header("Location: index.php");
                exit;
            } else {
                // Wrong password
                $_SESSION['login_attempts'][] = time();
                $message = $errorText;
            }
        } else {
            // Email not found, but responds in the same way
            $_SESSION['login_attempts'][] = time();
            $message = $errorText;
        }

        $stmt->close();
    }
}

// Only now output HTML:
include __DIR__ . "/includes/header.php";
?>

<h2>Login</h2>

<form method="POST" class="w-50">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>

  <button type="submit" class="btn btn-success">Login</button>
</form>

<p class="mt-3 text-danger"><?php echo htmlspecialchars($message); ?></p>

<?php include __DIR__ . "/includes/footer.php"; ?>