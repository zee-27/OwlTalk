<?php
include "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];

// Validate comment ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid comment ID.");
}

$comment_id = (int)$_GET["id"];

// Get comment information
$sql = "SELECT * FROM comments WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $comment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$comment = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$comment) {
    die("Comment not found.");
}

// Make sure user owns the comment
if ($comment["user_id"] != $user_id) {
    die("You are not allowed to edit this comment.");
}

// Update comment after form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = trim($_POST["content"]);

    if (!empty($content)) {
        $update = "UPDATE comments SET content = ? WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sii", $content, $comment_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: thread.php?id=" . $comment["post_id"]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Comment</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Edit Comment</h2>

        <form method="POST" class="form">
            <label>Comment
                <textarea name="content" rows="5" required><?php
                    echo htmlspecialchars($comment["content"]);
                ?></textarea>
            </label>

            <button class="btn" type="submit">Update Comment</button>
        </form>
    </div>
</div>

</body>
</html>