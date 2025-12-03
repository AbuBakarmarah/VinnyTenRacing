<?php
// Database connection settings for XAMPP MySQL
$host = "localhost";
$user = "root";
$pass = "";                 // default XAMPP MySQL has empty password
$db   = "vinnyten_racing";

// Throw exceptions for DB errors (useful in development)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Database connection failed");
}
mysqli_set_charset($conn, "utf8mb4");

// Start session for the whole app
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
