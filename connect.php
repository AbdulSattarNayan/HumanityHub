<?php
// Database configuration
$servername = "localhost"; // XAMPP default server
$username = "root"; // XAMPP default username (no password by default)
$password = ""; // XAMPP default password (empty by default)
$dbname = "humanityhub"; // Your database name

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to UTF-8 (recommended for proper encoding)
$conn->set_charset("utf8mb4");

// Optional: Uncomment to confirm connection (useful for debugging)
// echo "Connected successfully to the database!";
?>