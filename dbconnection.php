<?php
// Database connection settings
$host = 'localhost';     // Database host (usually localhost)
$dbname = 'healthcare_portal';  // Your database name
$username = 'root';      // Your MySQL username
$password = '';          // Your MySQL password (empty by default)

// Create a new PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection failure
    die("Connection failed: " . $e->getMessage());
}
?>
