<?php
// Connect to the database
include "db.php";

// Check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION["user_id"]);
}

// Redirect users who are not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: signin.php");
        exit;
    }
}
?>