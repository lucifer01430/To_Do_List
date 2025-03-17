<?php
$host = "localhost"; // XAMPP server
$user = "root"; // Default MySQL username
$password = ""; // Default MySQL password (XAMPP me empty hota hai)
$database = "todo_list_db"; // Tumhara database name

// Database Connection
$conn = new mysqli($host, $user, $password, $database);

// Connection Check
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
   