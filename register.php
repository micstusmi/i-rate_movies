<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<body>

<?php

include("includes/header.php");
include(__DIR__ . "/includes/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $alias = $_POST["alias"];
    $password = $_POST["password"];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email or alias already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR alias = ?");
    $check->bind_param("ss", $email, $alias);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Email or alias already exists.";
    } else {
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (email, password, alias) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hashedPassword, $alias);

        if ($stmt->execute()) {
            $message = "Registration successful!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<h2>Register</h2>

<form method="POST" class="w-50">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Alias</label>
    <input type="text" name="alias" class="form-control" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>

  <button type="submit" class="btn btn-primary">Register</button>
</form>

<p class="mt-3"><?php echo $message; ?></p>

<p><?php echo $message; 

include("includes/footer.php");

?></p>

</body>
</html>