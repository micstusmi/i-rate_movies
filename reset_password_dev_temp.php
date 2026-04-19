// WARNING: THIS FILE IS FOR DEVELOPMENT PURPOSES ONLY. IT BYPASSES ALL SECURITY MEASURES AND SHOULD NEVER NORMALLY BE USED. DELETE THIS FILE BEFORE USING IN ANOTHER PROJECT.

<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    
    $commonPasswords = ['password', '123456', 'qwerty', '12345678', 'password123'];

    // PasswordSecurity Validation
    if ($email === "" || $password === "" || $confirm_password === "") {
        $message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $message = "Password must contain at least one letter and one number.";
    } elseif (in_array(strtolower($password), $commonPasswords)) {
        $message = "Please choose a stronger password.";
    } else {
        // Checks if the user exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows === 1) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashedPassword, $email);

            if ($update->execute()) {
                $_SESSION['flash_message'] = "Success! Your password has now been updated!";
                header("Location: login.php");
                exit;
            } else {
                $message = "Unknown database error.";
            }
        } else {
            $message = "That email is not associated with any account.";
        }
    }
}

include __DIR__ . "/includes/header.php";
?>

<!-- Red DEV ONLY Banner
<div class="bg-danger text-white text-center py-2 fw-bold w-100 shadow-sm" style="position: sticky; top: 0; z-index: 1050;">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    TEMPORARY DEV PAGE FOR THIS ASSIGNMENT ONLY! DO NOT RE-USE THIS FILE IN ANY OTHER PROJECTS!
    <i class="bi bi-exclamation-triangle-fill ms-2"></i>
</div>

<div class="container mt-5 mb-5"> <!-- Added mb-5 for bottom breathing room -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- The "card" wrapper adds the white background and border -->
            <div class="card shadow-sm"> 
                
                <div class="card-header bg-light py-3"> <!-- py-3 adds vertical padding -->
                    <div class="text-center">
                        <i class="bi bi-shield-lock-fill fs-2 text-danger mb-2"></i>
                        <h4 class="mb-0 text-secondary">Manual Password Reset</h4>
                        <small class="text-muted">(Bypass Mode)</small>
                    </div>
                </div>

                <div class="card-body p-4"> <!-- p-4 adds generous internal white space -->
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">User Email</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" class="form-control" id="res-pass" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('res-pass', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Min. 8 chars, must include a letter and a number.</div>
                        </div>

                        <div class="mb-4"> <!-- Increased margin bottom -->
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger">Override Password</button>
                            <a href="login.php" class="btn btn-link text-muted small">Back to Login</a>
                        </div>
                    </form>
                </div> <!-- End Card Body -->
            </div> <!-- End Card -->
        </div>
    </div>
</div>

<?php include __DIR__ . "/includes/footer.php"; ?>
