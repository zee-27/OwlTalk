<?php
include "auth.php";
requireLogin();

// Validate post ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid thread ID.");
}

$post_id = (int)$_GET["id"];
$user_id = $_SESSION["user_id"];

// Delete only the user's own post
$sql = "DELETE FROM posts WHERE id = ? AND author_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

// Return to homepage
header("Location: index.php");
exit;
?>