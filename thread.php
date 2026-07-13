<?php
require_once "db.php";

$message = "";

// Validate thread ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid thread ID.");
}

$post_id = (int)$_GET["id"];

/* Get thread and vote score */
$post_sql = "SELECT posts.*,
                    users.username,
                    users.id AS user_id,
                    categories.name AS category_name,
                    COALESCE(SUM(post_votes.vote),0) AS score
             FROM posts
             JOIN users ON posts.author_id = users.id
             LEFT JOIN categories ON posts.category_id = categories.id
             LEFT JOIN post_votes ON posts.id = post_votes.post_id
             WHERE posts.id = ?
             GROUP BY posts.id";

$post_stmt = mysqli_prepare($conn, $post_sql);
mysqli_stmt_bind_param($post_stmt, "i", $post_id);
mysqli_stmt_execute($post_stmt);
$post_result = mysqli_stmt_get_result($post_stmt);
$post = mysqli_fetch_assoc($post_result);
mysqli_stmt_close($post_stmt);

if (!$post) {
    die("Thread not found.");
}

/* Add comment */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_SESSION["user_id"])) {

    $content = trim($_POST["content"]);

    if ($content !== "") {
        $user_id = $_SESSION["user_id"];

        $insert_sql = "INSERT INTO comments (post_id, user_id, content)
                       VALUES (?, ?, ?)";

        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "iis", $post_id, $user_id, $content);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: thread.php?id=" . $post_id);
        exit;
    } else {
        $message = "Comment cannot be empty.";
    }
}

/* Get comments with scores */
$comments_sql = "SELECT comments.*,
                        users.username,
                        COALESCE(SUM(comment_votes.vote),0) AS score
                 FROM comments
                 JOIN users ON comments.user_id = users.id
                 LEFT JOIN comment_votes
                    ON comments.id = comment_votes.comment_id
                 WHERE comments.post_id = ?
                 GROUP BY comments.id
                 ORDER BY score DESC, comments.id DESC";

$comments_stmt = mysqli_prepare($conn, $comments_sql);
mysqli_stmt_bind_param($comments_stmt, "i", $post_id);
mysqli_stmt_execute($comments_stmt);
$comments_result = mysqli_stmt_get_result($comments_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($post["title"]); ?> - OwlTalk</title>
<link rel="stylesheet" href="style/style.css">
</head>
<body>

<header class="header">
<div class="container nav-wrap">

<div class="left">
<a class="brand brand-1" href="index.php">
<img src="owl.jpg" alt="Logo" class="logo-img">
<span>OwlTalk</span>
</a>
</div>

<div class="right">
<a class="brand" href="index.php">Home</a>
<a class="brand" href="new_thread.php">New Thread</a>

<?php if (isset($_SESSION["user_id"])) { ?>
<a class="brand" href="account.php">Account</a>
<a class="brand" href="logout.php">Logout</a>
<?php } else { ?>
<a class="brand" href="signin.php">Sign In</a>
<a class="brand" href="signup.php">Sign Up</a>
<?php } ?>

</div>
</div>
</header>

<div class="container">

<!-- Thread -->
<div class="card">

<h1 style="margin-top:0;">
<?php echo htmlspecialchars($post["title"]); ?>
</h1>

<p>
<strong>Posted by:</strong>
<a class="brand" href="profile.php?id=<?php echo $post["user_id"]; ?>">
<?php echo htmlspecialchars($post["username"]); ?>
</a>
</p>

<p>
<strong>Category:</strong>
<?php echo htmlspecialchars($post["category_name"] ?? "Uncategorized"); ?>
</p>

<p>
<?php echo nl2br(htmlspecialchars($post["content"])); ?>
</p>

<p>
<strong>Posted:</strong>
<?php echo date("M j, Y g:i A", strtotime($post["created_at"])); ?>
</p>

<p>
<a class="brand"
href="vote_post.php?post_id=<?php echo $post_id; ?>&vote=1">⬆️</a>

<strong><?php echo $post["score"]; ?></strong>

<a class="brand"
href="vote_post.php?post_id=<?php echo $post_id; ?>&vote=-1">⬇️</a>
</p>

<?php if (isset($_SESSION["user_id"]) &&
$_SESSION["user_id"] == $post["author_id"]) { ?>

<p>
<a class="brand"
href="edit_thread.php?id=<?php echo $post["id"]; ?>">
Edit Thread
</a>

|

<a class="brand"
href="delete_thread.php?id=<?php echo $post["id"]; ?>"
onclick="return confirm('Delete this thread?');">
Delete Thread
</a>
</p>

<?php } ?>

</div>

<!-- Comments -->
<h2>Comments</h2>

<?php if (mysqli_num_rows($comments_result) > 0) { ?>
<?php while ($comment = mysqli_fetch_assoc($comments_result)) { ?>

<div class="card">

<p>
<strong>
<a class="brand"
href="profile.php?id=<?php echo $comment["user_id"]; ?>">
<?php echo htmlspecialchars($comment["username"]); ?>
</a>:
</strong>
</p>

<p>
<?php echo nl2br(htmlspecialchars($comment["content"])); ?>
</p>

<p>
<small>
<?php echo date("M j, Y g:i A", strtotime($comment["created_at"])); ?>
</small>
</p>

<p>
<a class="brand"
href="vote_comment.php?comment_id=<?php echo $comment["id"]; ?>&vote=1">⬆️</a>

<strong><?php echo $comment["score"]; ?></strong>

<a class="brand"
href="vote_comment.php?comment_id=<?php echo $comment["id"]; ?>&vote=-1">⬇️</a>
</p>

<?php if (isset($_SESSION["user_id"]) &&
$_SESSION["user_id"] == $comment["user_id"]) { ?>

<p>
<a class="brand"
href="edit_comment.php?id=<?php echo $comment["id"]; ?>">
Edit Comment
</a>

|

<a class="delete-link"
href="delete_comment.php?id=<?php echo $comment["id"]; ?>&post_id=<?php echo $post_id; ?>"
onclick="return confirm('Delete this comment?');">
Delete Comment
</a>
</p>

<?php } ?>

</div>

<?php } ?>
<?php } else { ?>

<p>No comments yet. Be the first!</p>

<?php } ?>

<!-- Comment form -->
<?php if (isset($_SESSION["user_id"])) { ?>

<?php if (!empty($message)) { ?>
<p class="error"><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<form class="card form" method="POST">

<label>Add to the Conversation
<textarea name="content" rows="4" required></textarea>
</label>

<button class="btn" type="submit">
Post Comment
</button>

</form>

<?php } else { ?>

<div class="card">
<p>
<a class="brand" href="signin.php">
Sign in to join the conversation
</a>
</p>
</div>

<?php } ?>

</div>

</body>
</html>