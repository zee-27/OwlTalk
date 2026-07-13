<?php
require_once "db.php";

// Get search text and selected category
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$category_id = isset($_GET["category_id"]) ? (int)$_GET["category_id"] : 0;

// Get all categories
$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

// Query posts with usernames, category names, and vote score
$sql = "SELECT posts.id, posts.title, posts.content, posts.created_at,
               users.username, users.id AS user_id,
               categories.name AS category_name,
               COALESCE(SUM(post_votes.vote), 0) AS score
        FROM posts
        JOIN users ON posts.author_id = users.id
        LEFT JOIN categories ON posts.category_id = categories.id
        LEFT JOIN post_votes ON posts.id = post_votes.post_id
        WHERE 1=1";

$params = [];
$types = "";

// Search filter
if ($search !== "") {
    $sql .= " AND (posts.title LIKE ? OR posts.content LIKE ?)";
    $searchLike = "%" . $search . "%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= "ss";
}

// Category filter
if ($category_id > 0) {
    $sql .= " AND posts.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

// Sort by likes first
$sql .= " GROUP BY posts.id
          ORDER BY score DESC, posts.id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home - OwlTalk</title>
<link rel="stylesheet" href="style/style.css"/>
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

<!-- Navigation links -->
<div class="right">
<a class="brand" href="index.php">Home</a>
<a class="brand" href="new_thread.php">New Thread</a>
<a class="brand" href="majors.php">Majors</a>

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

<h1>All Threads</h1>

<!-- Search form -->
<form class="card form" method="GET">

<label>Search Threads
<input type="text"
name="search"
value="<?php echo htmlspecialchars($search); ?>"
placeholder="Search title or content">
</label>

<label>Category
<select name="category_id">

<option value="0">All Categories</option>

<?php while ($cat = mysqli_fetch_assoc($categories_result)) { ?>
<option value="<?php echo $cat['id']; ?>"
<?php if ($category_id == $cat['id']) echo "selected"; ?>>
<?php echo htmlspecialchars($cat['name']); ?>
</option>
<?php } ?>

</select>
</label>

<button class="btn" type="submit">Search</button>

</form>

<!-- Show posts -->
<?php if ($result && mysqli_num_rows($result) > 0) { ?>

<?php while ($row = mysqli_fetch_assoc($result)) { ?>

<div class="card">

<h2>
<a href="thread.php?id=<?php echo $row['id']; ?>" class="brand">
<?php echo htmlspecialchars($row['title']); ?>
</a>
</h2>

<p>
<strong>Posted by:</strong>

<a class="brand"
href="profile.php?id=<?php echo $row['user_id']; ?>">
<?php echo htmlspecialchars($row['username']); ?>
</a>
</p>

<p>
<strong>Category:</strong>
<?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
</p>

<p>
<strong>Likes:</strong>
<?php echo $row['score']; ?>
</p>

<p>
<?php echo nl2br(htmlspecialchars($row['content'])); ?>
</p>

<p>
<strong>Posted:</strong>
<?php echo date("M j, Y g:i A", strtotime($row["created_at"])); ?>
</p>

<p>
<a class="brand"
href="thread.php?id=<?php echo $row['id']; ?>">
Open Thread
</a>
</p>

</div>

<?php } ?>

<?php } else { ?>

<p>No threads found.</p>

<?php } ?>

</div>

</body>
</html>