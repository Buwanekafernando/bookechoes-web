<?php
$host = "localhost";
$user = "root";
$password = "db@366";
$database = "bookdb";
$port = 3307; 

$conn = mysqli_connect($host, $user, $password, $database, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Database connected successfully";
?>
