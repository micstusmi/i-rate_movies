<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
include(__DIR__ . "/includes/db.php");

$message = "";
$email = "";
$alias = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? '');
    $alias = trim($_POST["alias"] ?? '');
    $password = $_POST["password"] ?? '';
    $commonPasswords = ['password', '123456', 'qwerty'];

    if ($email === '' || $alias === '' || $password === '') {
        $message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $message = "Password must contain at least one letter and one number.";
    } elseif (in_array(strtolower($password), $commonPasswords)) {
        $message = "Please choose a stronger password.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Checks if the email or alias already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR alias = ?");
        $check->bind_param("ss", $email, $alias);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email or alias already exists.";
        } else {
            if (EMAIL_VERIFICATION_REQUIRED) {
    $token = bin2hex(random_bytes(32));
    $emailVerified = 0;
} else {
    $token = null;
    $emailVerified = 1; // auto-verify in dev
}
 
$stmt = $conn->prepare("
    INSERT INTO users (email, password, alias, email_verified, verification_token)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("sssis", $email, $hashedPassword, $alias, $emailVerified, $token);

            if ($stmt->execute()) {
                $newUserId = $stmt->insert_id;

                // Verification link
                $verificationLink = "http://localhost/i-rate_movies/verify.php?token=" . urlencode($token);

                // Send verification email
                $subject = "Verify your email address";
                $headers = "From: no-reply@i-rate_movies.com\r\n";
                $headers .= "Reply-To: no-reply@i-rate_movies.com\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                $body = "
                    <html>
                    <head><title>Verify your email</title></head>
                    <body>
                        <p>Hi " . htmlspecialchars($alias) . ",</p>
                        <p>Thank you for registering. Please verify your email address by clicking the button below:</p>
                        <p>
                            <a href='" . htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') . "'
                               style='display:inline-block;padding:10px 20px;background:#0d6efd;color:#fff;
                                      text-decoration:none;border-radius:4px;'>
                                Verify Email
                            </a>
                        </p>
                        <p>If the button doesn't work, copy and paste this link into your browser:</p>
                        <p>" . htmlspecialchars($verificationLink) . "</p>
                        <p>If you did not create an account, you can ignore this email.</p>
                    </body>
                    </html>
                ";

                // Using PHP's mail() (for production, consider PHPMailer/SMTP)
                if (EMAIL_VERIFICATION_REQUIRED) {
    if (mail($email, $subject, $body, $headers)) {
        $message = "Registration successful. Please check your email to verify your account.";
    } else {
        $message = "Account created, but we could not send a verification email.";
    }
} else {
    // Saving this for future use: $message = "Your email address has now been verified and your account is already activated. You can now log in.";
    // Auto-Login after successful registration (for the interim) whilst in dev mode, effectively bypasses the email verification.
    $_SESSION["user_id"] = $newUserId;
    $_SESSION["alias"] = $alias;
    header("Location: index.php");
    exit;
}

            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
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
    <h2>Sign up to start reviewing</h2>

    <?php if ($message): ?>
        <div class="alert alert-warning mt-3">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="w-50 mt-3">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input
                type="email"
                name="email"
                class="form-control"
                required
                value="<?php echo htmlspecialchars($email); ?>"
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Alias</label>
            <input
                type="text"
                name="alias"
                class="form-control"
                required
                value="<?php echo htmlspecialchars($alias); ?>"
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
  <input type="password" name="password" class="form-control" id="register-password" required>
  <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('register-password', this)">
<i class="bi bi-eye"></i>
  </button>
</div>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>