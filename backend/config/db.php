<?php
$host = "localhost";
$user = "root";
$password = "db@366";
$database = "bookdb";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
