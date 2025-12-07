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

// ---- CSRF HELPERS ----
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): void
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }
        if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}

// Require login for pages that need it
if (!function_exists('require_login')) {
    function require_login(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
    }
}