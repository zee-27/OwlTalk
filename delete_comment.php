<?php
include "auth.php";
requireLogin();

// Validate comment ID and post ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"]) || !isset($_GET["post_id"]) || !is_numeric($_GET["post_id"])) {
    die("Invalid request.");
}

$comment_id = (int)$_GET["id"];
$post_id = (int)$_GET["post_id"];
$user_id = $_SESSION["user_id"];

// Delete only the logged in user's comment
$sql = "DELETE FROM comments WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

// Return to thread page
header("Location: thread.php?id=" . $post_id);
exit;
?>