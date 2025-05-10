<?php
$servername = "localhost";
$username   = "root";              // Default XAMPP username
$password   = "";                  // Default XAMPP password (empty)
$dbname     = "iphs_campus_db";    // Database name

// Create connection using MySQLi (object-oriented)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset to UTF-8 for consistent encoding
$conn->set_charset("utf8");
?>
