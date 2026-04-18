<?php
// includes/db.php

// Database config
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "i-rate_movies";

// mysqli connection
$conn = new mysqli($host, $user, $pass, $db);

// Checks for connection errors
if ($conn->connect_error) {
    // Dies early; doesn't continue the page with a broken $conn
    die("Database connection failed: " . $conn->connect_error);
}

// Sets charset explicitly
$conn->set_charset("utf8mb4");