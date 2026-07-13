<?php
require_once "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];

/* Get logged in user info */
$user_sql = "SELECT id, username, email, about_me, current_classes, interests, profile_pic
             FROM users
             WHERE id = ?";

$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);
mysqli_stmt_close($user_stmt);

if (!$user) {
    die("User not found.");
}

/* Get latest 10 posts by user */
$posts_sql = "SELECT id, title, content, created_at
              FROM posts
              WHERE author_id = ?
              ORDER BY created_at DESC
              LIMIT 10";

$posts_stmt = mysqli_prepare($conn, $posts_sql);
mysqli_stmt_bind_param($posts_stmt, "i", $user_id);
mysqli_stmt_execute($posts_stmt);
$posts_result = mysqli_stmt_get_result($posts_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account - OwlTalk</title>
<link rel="stylesheet" href="style/style.css">

<style>
/* Profile layout */
.profile-box {
    display:flex;
    gap:20px;
    align-items:flex-start;
}

/* Profile picture */
.avatar {
    width:100px;
    height:100px;
    border-radius:15px;
    border:3px solid #4b2e1e;
    object-fit:cover;
    background:#ddd;
}

/* Post cards */
.post-card {
    background:white;
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
}
</style>
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

<!-- Navigation -->
<div class="right">
<a class="brand" href="index.php">Home</a>
<a class="brand" href="new_thread.php">New Thread</a>
<a class="brand" href="account.php">Account</a>
<a class="brand" href="messages.php">Messages</a>
<a class="brand" href="logout.php">Logout</a>
</div>

</div>
</header>

<div class="container">

<div class="card profile-box">

<?php
/* Default or custom profile image */
$profile_image = "uploads/default.png";

if (!empty($user["profile_pic"])) {
    $profile_image = "uploads/" . htmlspecialchars($user["profile_pic"]);
}
?>

<img
src="<?php echo $profile_image; ?>"
id="profileDisplay"
class="avatar"
alt="Profile Picture">

<div>

<h2>
Welcome,
<?php echo htmlspecialchars($user["username"]); ?>!
</h2>

<p><?php echo htmlspecialchars($user["email"]); ?></p>

<!-- Upload new profile picture -->
<form
action="upload_handler.php"
method="POST"
enctype="multipart/form-data"
target="upload_target">

<label class="btn">
Change Photo

<input
type="file"
name="profile_pic"
style="display:none;"
onchange="this.form.submit();">

</label>

</form>

<iframe name="upload_target" style="display:none;"></iframe>

<p id="picStatus"></p>

<p><strong>About Me:</strong><br>
<?php echo nl2br(htmlspecialchars($user["about_me"] ?? "")); ?></p>

<p><strong>Current Classes:</strong><br>
<?php echo nl2br(htmlspecialchars($user["current_classes"] ?? "")); ?></p>

<p><strong>Interests:</strong><br>
<?php echo nl2br(htmlspecialchars($user["interests"] ?? "")); ?></p>

</div>
</div>

<!-- Quick links -->
<div class="card">
<h3>Quick Actions</h3>

<p>
<a class="brand" href="new_thread.php">Create New Thread</a> |
<a class="brand" href="profile.php?id=<?php echo $user_id; ?>">Edit Profile</a>
</p>
</div>

<!-- User posts -->
<div class="card">
<h3>My Recent Posts</h3>

<?php if (mysqli_num_rows($posts_result) > 0) { ?>
<?php while ($post = mysqli_fetch_assoc($posts_result)) { ?>

<div class="post-card">
<h4>
<a class="brand" href="thread.php?id=<?php echo $post["id"]; ?>">
<?php echo htmlspecialchars($post["title"]); ?>
</a>
</h4>

<p><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>

<small>
<?php echo htmlspecialchars($post["created_at"]); ?>
</small>
</div>

<?php } ?>
<?php } else { ?>

<p class="card">No posts yet.</p>

<?php } ?>

</div>

</div>

</body>
</html>