<?php
$host = 'mysql.railway.internal'; // Change from "localhost" to "mysql" (Docker service name)
$username = "root"; // Default MySQL username
$password = 'xuUhTMsdFrvdHpfjvrzSSLbrhUyXhRHb'; // Match the MySQL password from Docker Compose
$database = "railway"; // Database name
$port = 3306;

// Create a connection
$conn = new mysqli($host, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>