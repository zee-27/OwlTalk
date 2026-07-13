<?php
include "db.php";

// Clear all session data
session_unset();

// End the session
session_destroy();

// Return user to sign in page
header("Location: signin.php");
exit;
?>