<?php
date_default_timezone_set("America/New_York");

// Database connection info
$servername = "elvisdb";
$username = "hughes56";
$password = "16CAPSt0ne!!";
$databasename = "hughes56";

// Connect to database
$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $databasename,
    3306
);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set database timezone
mysqli_query($conn, "SET time_zone = '-04:00'");

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>