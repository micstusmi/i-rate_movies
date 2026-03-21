<?php

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

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>

<h2>Sign Up to start reviewing movies</h2>

<form method="POST">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Create a username as an alias:</label><br>
    <input type="text" name="alias" required><br><br>

    <label>Choose a Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Register</button>
</form>

<p><?php echo $message; ?></p>

</body>
</html>