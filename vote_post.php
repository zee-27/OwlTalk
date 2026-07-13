<?php
include "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];

$post_id = (int)$_GET["post_id"];
$vote = (int)$_GET["vote"];

// Validate vote
if (!in_array($vote, [1, -1])) {
    die("Invalid vote.");
}

// Check if user already voted
$sql = "SELECT vote FROM post_votes WHERE user_id = ? AND post_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Update existing vote or insert new vote
if ($existing) {
    $update = "UPDATE post_votes SET vote = ? WHERE user_id = ? AND post_id = ?";
    $stmt = mysqli_prepare($conn, $update);
    mysqli_stmt_bind_param($stmt, "iii", $vote, $user_id, $post_id);
} else {
    $insert = "INSERT INTO post_votes (user_id, post_id, vote) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert);
    mysqli_stmt_bind_param($stmt, "iii", $user_id, $post_id, $vote);
}

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Return to thread
header("Location: thread.php?id=" . $post_id);
exit;
?>