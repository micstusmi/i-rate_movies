<?php
$conn = new mysqli("localhost", "root", "", "i-rate_movies");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>