<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
//check user 
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $token = bin2hex(random_bytes(32));
        $expiry=date("Y-m-d H:i:s", time() + 3600); // 1 hour expiry

        //save into database
        $update=$conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE user_id = ?");

        // set up a reset link
        $resetLink = "http://localhost/i-rate_movies/reset_password.php?token=" . $token;

        $message="Reset link: <a href='$resetLink'>$resetLink</a>";
        }else{
            $message="If email exists, a reset-link will be sent.";
        }
}
?>

<h3>Forgot Password</h3>
<form method="POST">
    <label for="email">Enter your email address:</label>
    <input type="email" name="email" class="form-control mb-3" required>
    <button class="btn btn-primary">Send Reset Link</button>
</form>
<p><?php echo $message; ?></p>