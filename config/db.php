<?php
$host = 'switchback.proxy.rlwy.net'; // Change from "localhost" to "mysql" (Docker service name)
$username = "root"; // Default MySQL username
$password = 'XiSbWkhhWLDOHQCAadENepHFRXXYXZQQ'; // Match the MySQL password from Docker Compose
$database = "railway"; // Database name

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>