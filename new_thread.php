<?php
include "auth.php";
requireLogin();

$message = "";
$current_user_id = $_SESSION["user_id"];

// Get dropdown options
$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$majors_result = mysqli_query($conn, "SELECT * FROM majors ORDER BY name ASC");

// Handle new thread form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category_id = (int)$_POST["category_id"];
    $major_id = !empty($_POST["major_id"]) ? (int)$_POST["major_id"] : null;
    $major_topic = $_POST["major_topic"] ?? null;

    if (!empty($title) && !empty($content) && $category_id > 0) {
        $sql = "INSERT INTO posts (author_id, category_id, major_id, major_topic, title, content) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiisss", $current_user_id, $category_id, $major_id, $major_topic, $title, $content);

        if (mysqli_stmt_execute($stmt)) {
            $new_post_id = mysqli_insert_id($conn);
            header("Location: thread.php?id=" . $new_post_id);
            exit;
        } else {
            $message = "Error posting thread.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Thread - OwlTalk</title>
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

<!-- Navigation -->
<div class="right">
<a class="brand" href="index.php">Home</a>
<a class="brand" href="profile.php?id=<?php echo $_SESSION["user_id"]; ?>">Profile</a>
<a class="brand" href="logout.php">Logout</a>
</div>

</div>
</header>

<div class="container">
<h1>Create New Thread</h1>

<?php if (!empty($message)) { ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<!-- New thread form -->
<form class="card form" method="POST">

<label>Title
<input name="title" maxlength="255" required />
</label>

<label>Category
<select name="category_id" id="categorySelect" required>
<option value="">Select Category</option>

<?php while ($cat = mysqli_fetch_assoc($categories_result)) { ?>
<option value="<?php echo $cat['id']; ?>">
<?php echo htmlspecialchars($cat['name']); ?>
</option>
<?php } ?>

</select>
</label>

<label id="majorLabel" style="display:none;">Major / Program
<select name="major_id" id="majorSelect">
<option value="">Select Major / Program</option>

<?php while ($major = mysqli_fetch_assoc($majors_result)) { ?>
<option value="<?php echo $major['id']; ?>">
<?php echo htmlspecialchars($major['name']); ?>
</option>
<?php } ?>

</select>
</label>

<label id="majorTopicLabel" style="display:none;">Topic
<select name="major_topic" id="majorTopicSelect">
<option value="">Select Topic</option>
<option value="Classes">Classes</option>
<option value="Professors">Professors</option>
<option value="Study Groups">Study Groups</option>
<option value="General Discussion">General Discussion</option>
</select>
</label>

<label>Content
<textarea name="content" rows="7" required></textarea>
</label>

<button class="btn" type="submit">Post Thread</button>
</form>
</div>

<script>
const categorySelect = document.getElementById("categorySelect");

const majorLabel = document.getElementById("majorLabel");
const majorSelect = document.getElementById("majorSelect");

const majorTopicLabel = document.getElementById("majorTopicLabel");
const majorTopicSelect = document.getElementById("majorTopicSelect");

// Show major options only when Majors category is selected
function toggleMajorDropdowns() {
    const selectedText =
        categorySelect.options[
            categorySelect.selectedIndex
        ].text.trim();

    if (selectedText === "Majors") {
        majorLabel.style.display = "block";
        majorTopicLabel.style.display = "block";
    } else {
        majorLabel.style.display = "none";
        majorTopicLabel.style.display = "none";

        majorSelect.value = "";
        majorTopicSelect.value = "";
    }
}

categorySelect.addEventListener("change", toggleMajorDropdowns);
toggleMajorDropdowns();
</script>

</body>
</html>