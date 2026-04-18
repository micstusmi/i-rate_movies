<?php
include(__DIR__ . "/includes/db.php");

if (!isset($_GET["token"])||empty($_GET["token"])) {
    die("Invalid token.");
}
$token = $_GET["token"];
$stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? 
                        AND reset_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

// it will show word when token is invalid.
if ($result->num_rows !== 1) {
    die("Invalid token.");
}
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $newPassword = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password = ?, 
    reset_token = NULL, reset_expiry = NULL WHERE user_id = ?");

    $update->bind_param("si", $newPassword, $user["user_id"]);
    $update->execute();

    echo "Password reset successful. You can <a href='login.php'>login</a> now.";
    exit;
}
?>
<h3>Reset Password</h3>
<form method="POST">
    <label for="password">New Password:</label>
    <input type="password" name="password" placeholder="Enter new password" required>
    <button>Reset Password</button>
</form>