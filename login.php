<?php
session_start();
include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT user_id, password, alias FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            // Store session
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["alias"] = $user["alias"];

            header("Location: index.php");
            exit;
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "User not found.";
    }
}
?>

<?php include("includes/header.php"); ?>

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

<p class="mt-3 text-danger"><?php echo $message; ?></p>

<?php include("includes/footer.php"); ?>