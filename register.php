<!DOCTYPE html>
<html>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<?php

include(__DIR__ . "/includes/db.php");
include(__DIR__ . "/includes/header.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $alias = $_POST["alias"];
    $password = $_POST["password"];

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email or alias already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR alias = ?");
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
    
            // Get the new user's ID
            $newUserId = $stmt->insert_id;
        
            // Start session (if not already started)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        
            // Log user in automatically
            $_SESSION["user_id"] = $newUserId;
            $_SESSION["alias"] = $alias;
        
            // Redirect to homepage
            header("Location: index.php");
            exit;
        
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<h2>Sign up to start reviewing</h2>

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

<?php include("includes/footer.php"); ?>

</body>
</html>