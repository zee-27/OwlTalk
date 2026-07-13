<?php
include "auth.php";

// Validate profile ID
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid user ID.");
}

$profile_id = (int)$_GET["id"];

// Update profile if owner submits form
if ($_SERVER["REQUEST_METHOD"] == "POST" &&
    isset($_SESSION["user_id"]) &&
    $_SESSION["user_id"] == $profile_id) {

    $about_me = trim($_POST["about_me"]);
    $current_classes = trim($_POST["current_classes"]);
    $interests = trim($_POST["interests"]);
    $major = trim($_POST["major"]);

    $update_sql = "UPDATE users
                   SET about_me = ?, current_classes = ?, interests = ?, major = ?
                   WHERE id = ?";

    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param(
        $update_stmt,
        "ssssi",
        $about_me,
        $current_classes,
        $interests,
        $major,
        $profile_id
    );

    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    header("Location: account.php");
    exit;
}

// Get user profile info
$user_sql = "SELECT id, username, email, about_me, current_classes, interests, major, profile_pic 
             FROM users 
             WHERE id = ?";

$user_stmt = mysqli_prepare($conn, $user_sql);
mysqli_stmt_bind_param($user_stmt, "i", $profile_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    die("User not found.");
}

// Get recent posts
$posts_sql = "SELECT id, title, created_at 
              FROM posts 
              WHERE author_id = ? 
              ORDER BY id DESC 
              LIMIT 5";

$posts_stmt = mysqli_prepare($conn, $posts_sql);
mysqli_stmt_bind_param($posts_stmt, "i", $profile_id);
mysqli_stmt_execute($posts_stmt);
$posts_result = mysqli_stmt_get_result($posts_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile - OwlTalk</title>
<link rel="stylesheet" href="style/style.css">
</head>
<body>

<header class="header">
<div class="container nav-wrap">

<div class="left">
<a class="brand brand-1" href="index.php">OwlTalk</a>
</div>

<!-- Navigation -->
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
<div class="card profile-box">

<?php
$profile_image = "uploads/default.png";

if (!empty($user["profile_pic"])) {
    $profile_image = "uploads/" . htmlspecialchars($user["profile_pic"]);
}
?>

<img src="<?php echo $profile_image; ?>" class="avatar profile-avatar" alt="Profile Picture">

<div>

<h1><?php echo htmlspecialchars($user["username"]); ?>'s Profile</h1>

<p><strong>Major:</strong><br>
<?php echo htmlspecialchars($user["major"] ?? ""); ?></p>

<p><strong>About Me:</strong><br>
<?php echo nl2br(htmlspecialchars($user["about_me"] ?? "")); ?></p>

<p><strong>Current Classes:</strong><br>
<?php echo nl2br(htmlspecialchars($user["current_classes"] ?? "")); ?></p>

<p><strong>Interests:</strong><br>
<?php echo nl2br(htmlspecialchars($user["interests"] ?? "")); ?></p>

<?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != $profile_id) { ?>
<p>
<a class="btn" href="messages.php?user_id=<?php echo $profile_id; ?>">
Message
</a>
</p>
<?php } ?>

</div>
</div>

<!-- Edit own profile -->
<?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $profile_id) { ?>

<form class="card form" method="POST">
<h2>Edit Profile</h2>

<label>About Me
<textarea name="about_me" rows="4"><?php echo htmlspecialchars($user["about_me"] ?? ""); ?></textarea>
</label>

<label>Current Classes
<textarea name="current_classes" rows="3"><?php echo htmlspecialchars($user["current_classes"] ?? ""); ?></textarea>
</label>

<label>Major
<input type="text" name="major" value="<?php echo htmlspecialchars($user["major"] ?? ""); ?>">
</label>

<label>Interests
<textarea name="interests" rows="3"><?php echo htmlspecialchars($user["interests"] ?? ""); ?></textarea>
</label>

<button class="btn" type="submit">Save Profile</button>
</form>

<?php } ?>

<!-- Recent posts -->
<div class="card">
<h2>Recent Posts</h2>

<?php if (mysqli_num_rows($posts_result) > 0) { ?>
<?php while ($post = mysqli_fetch_assoc($posts_result)) { ?>

<p>
<a class="brand" href="thread.php?id=<?php echo $post["id"]; ?>">
<?php echo htmlspecialchars($post["title"]); ?>
</a>
</p>

<?php } ?>
<?php } else { ?>

<p>No posts yet.</p>

<?php } ?>

</div>
</div>

</body>
</html>