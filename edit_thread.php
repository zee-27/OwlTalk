<?php
include "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];

// Validate thread ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid thread ID.");
}

$post_id = (int)$_GET["id"];

// Get post information
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$post) {
    die("Thread not found.");
}

// Make sure user owns the post
if ($post["author_id"] != $user_id) {
    die("You are not allowed to edit this thread.");
}

// Update thread after form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (!empty($title) && !empty($content)) {

        $update_sql = "UPDATE posts SET title = ?, content = ? WHERE id = ? AND author_id = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssii", $title, $content, $post_id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: thread.php?id=" . $post_id);
            exit;
        } else {
            die("Error updating thread.");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Thread - OwlTalk</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>

<div class="container">
    <div class="card">
        <h2>Edit Thread</h2>

        <form method="POST" class="form">
            <label>Title
                <input type="text" name="title" value="<?php echo htmlspecialchars($post["title"]); ?>" required>
            </label>

            <label>Content
                <textarea name="content" rows="8" required><?php echo htmlspecialchars($post["content"]); ?></textarea>
            </label>

            <button class="btn" type="submit">Update Thread</button>
        </form>
    </div>
</div>

</body>
</html>