<?php
$host = "localhost";
$user = "root";
$pass = ""; // Update with your MySQL password
$dbname = "blog";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>