<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$message = "";

// Throttles the configuration to mitigate brute-force Login attempts.
$MAX_ATTEMPTS   = 5;    // failed logins allowed
$WINDOW_SECONDS = 600;  // 10 minutes window

// In‑session throttle storage
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

// Cleans up old attempts
$_SESSION['login_attempts'] = array_filter(
    $_SESSION['login_attempts'],
    function ($t) use ($WINDOW_SECONDS) {
        return $t > time() - $WINDOW_SECONDS;
    }
);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim($_POST["identifier"] ?? ""); // email or alias
    $password   = $_POST["password"] ?? "";

    if (count($_SESSION['login_attempts']) >= $MAX_ATTEMPTS) {
        $message = "Too many failed login attempts. Please wait 10 minutes before trying again.";
    } else {
        $errorText = "Invalid login credentials."; // generic on purpose

        if ($identifier === "" || $password === "") {
            $message = $errorText;
        } else {
            // Query by email OR alias
            $stmt = $conn->prepare("
                SELECT user_id, alias, password, email_verified
                FROM users
                WHERE email = ? OR alias = ?
                LIMIT 1
            ");
            if (!$stmt) {
                die("Database error: " . $conn->error);
            }

            $stmt->bind_param("ss", $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {

    if (!password_verify($password, $user["password"])) {
        $_SESSION['login_attempts'][] = time();
        $message = $errorText;

    } else {

        if (EMAIL_VERIFICATION_REQUIRED && (int)$user['email_verified'] !== 1) {
            $message = "Please verify your email address before logging in.";

        } else {
            $_SESSION['login_attempts'] = [];

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["alias"]   = $user["alias"];

            header("Location: index.php");
            exit;
        }
    }

} else {
    $_SESSION['login_attempts'][] = time();
    $message = $errorText;
}
            $stmt->close();
        }
    }
}

include __DIR__ . "/includes/header.php";
?>

<h2>Login</h2>

<form method="POST" class="w-50">
  <div class="mb-3">
    <label class="form-label">Email or Alias</label>
    <input type="text" name="identifier" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Password</label>
    <div class="input-group">
        <input type="password" name="password" class="form-control" id="login-password" required>
            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('login-password', this)">
<i class="bi bi-eye"></i>
            </button>
        </div>
  </div>

<button type="submit" class="btn btn-primary">Login</button>
<div class="mt-3">
    <a href="#" class="text-decoration-underline" data-bs-toggle="modal" data-bs-target="#devForgotModal">
        Forgot password?
    </a>
</div>
</form>

<p class="mt-3 text-danger"><?php echo htmlspecialchars($message); ?></p>

<!-- Temporary 'forgot password' workaround (manual override) solution while emails are not setup with SMTP configurations -->
<div class="modal fade" id="devForgotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">System Message: Dev Sandbox</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>The system is currently in <strong>dev sandbox mode</strong> and is unable to send emails.</p>
        <p>Would you like to bypass the email step and update your password now via our manual override?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <!-- Redirects to your reset page directly -->
        <a href="reset_password_dev_temp.php" class="btn btn-primary">Yes, Bypass Email</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>