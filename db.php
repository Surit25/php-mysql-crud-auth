
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "blog";

// Create database connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    http_response_code(500);
    exit("Unable to connect to the database. Please try again later.");
}

// Set UTF-8 character encoding
if (!$conn->set_charset("utf8mb4")) {
    error_log("Database charset setup failed: " . $conn->error);
    http_response_code(500);
    exit("A database configuration error occurred.");
}
?>
