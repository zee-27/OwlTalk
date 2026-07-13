<?php
require_once "db.php";

// Get selected major and search text
$selected_major = isset($_GET["major_id"]) ? (int)$_GET["major_id"] : 0;
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";

// Get all majors for links
$majors_list = mysqli_query($conn, "
    SELECT * 
    FROM majors
    ORDER BY
        CASE WHEN name = 'Other' THEN 1 ELSE 0 END,
        name ASC
");

/* Get posts from Majors category */
$sql = "SELECT posts.id, posts.title, posts.content, posts.created_at,
               posts.major_topic,
               users.username, users.id AS user_id,
               majors.name AS major_name,
               COALESCE(SUM(post_votes.vote), 0) AS score
        FROM posts
        JOIN users ON posts.author_id = users.id
        JOIN categories ON posts.category_id = categories.id
        LEFT JOIN majors ON posts.major_id = majors.id
        LEFT JOIN post_votes ON posts.id = post_votes.post_id
        WHERE categories.name = 'Majors'";

// Filter by selected major
if ($selected_major > 0) {
    $sql .= " AND posts.major_id = " . $selected_major;
}

// Search filter
if ($search !== "") {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (posts.title LIKE '%$safe_search%' 
               OR posts.content LIKE '%$safe_search%')";
}

// Group and sort results
$sql .= " GROUP BY posts.id
          ORDER BY posts.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Majors - OwlTalk</title>
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

<!-- Navigation -->
<div class="right">
<a class="brand" href="index.php">Home</a>
<a class="brand" href="majors.php">Majors</a>
<a class="brand" href="new_thread.php">New Thread</a>

<?php if (isset($_SESSION["user_id"])) { ?>
<a class="brand" href="account.php">Account</a>
<a class="brand" href="messages.php">Messages</a>
<a class="brand" href="logout.php">Logout</a>
<?php } else { ?>
<a class="brand" href="signin.php">Sign In</a>
<a class="brand" href="signup.php">Sign Up</a>
<?php } ?>

</div>
</div>
</header>

<div class="container">

<h1>Major Discussions</h1>

<!-- Search and major links -->
<div class="card">
<form method="GET" class="form">

<?php if ($selected_major > 0) { ?>
<input type="hidden" name="major_id"
value="<?php echo $selected_major; ?>">
<?php } ?>

<label>Search Threads
<input type="text"
name="search"
value="<?php echo htmlspecialchars($search); ?>"
placeholder="Search title or content">
</label>

<button class="btn" type="submit">Search</button>
</form>

<h2>Browse by Major</h2>

<div class="major-links">
<a class="brand" href="majors.php">All Majors</a>

<?php while ($major = mysqli_fetch_assoc($majors_list)) { ?>
<a class="brand"
href="majors.php?major_id=<?php echo $major["id"]; ?>">
<?php echo htmlspecialchars($major["name"]); ?>
</a>
<?php } ?>

</div>
</div>

<!-- Show posts -->
<?php if ($result && mysqli_num_rows($result) > 0) { ?>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>

<div class="card">

<h2>
<a class="brand" href="thread.php?id=<?php echo $row["id"]; ?>">
<?php echo htmlspecialchars($row["title"]); ?>
</a>
</h2>

<p>
<strong>Posted by:</strong>
<a class="brand" href="profile.php?id=<?php echo $row["user_id"]; ?>">
<?php echo htmlspecialchars($row["username"]); ?>
</a>
</p>

<p>
<strong>Major:</strong>
<?php echo htmlspecialchars($row["major_name"] ?? "Not specified"); ?>
</p>

<p>
<strong>Topic:</strong>
<?php echo htmlspecialchars($row["major_topic"] ?? "Not specified"); ?>
</p>

<p>
<?php echo nl2br(htmlspecialchars($row["content"])); ?>
</p>

<p>
<small>
<?php echo date("M j, Y g:i A", strtotime($row["created_at"])); ?>
</small>
</p>

<p>
<strong>Likes:</strong>
<?php echo $row["score"]; ?>
</p>

<p>
<a class="brand" href="thread.php?id=<?php echo $row["id"]; ?>">
Open Thread
</a>
</p>

</div>

<?php } ?>

<?php } else { ?>

<div class="card">
<p>No major threads yet.</p>
</div>

<?php } ?>

</div>

</body>
</html>