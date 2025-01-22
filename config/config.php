<?php
// Database configuration
$host = 'localhost';       // Hostname of the database server
$username = 'root';        // Database username
$password = '';            // Database password
$database = 'autireach';   // Name of your database

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optionally, set character set to avoid encoding issues
$conn->set_charset("utf8");
?>
