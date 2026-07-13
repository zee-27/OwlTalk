<?php
require_once "db.php";

$message = "";
$username = "";
$email = "";

// Handle sign up form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($username !== "" && $email !== "" && $password !== "") {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";

        } elseif (strlen($password) < 6) {
            $message = "Password must be at least 6 characters.";

        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Create new user
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPassword);

                if (mysqli_stmt_execute($stmt)) {
                    $new_user_id = mysqli_insert_id($conn);

                    // Log user in after signup
                    session_regenerate_id(true);
                    $_SESSION["user_id"] = $new_user_id;
                    $_SESSION["username"] = $username;

                    mysqli_stmt_close($stmt);

                    header("Location: account.php");
                    exit;

                } else {
                    if (mysqli_errno($conn) == 1062) {
                        $message = "That email or username is already registered.";
                    } else {
                        $message = "Registration failed. Please try again.";
                    }
                }

                mysqli_stmt_close($stmt);

            } else {
                $message = "Something went wrong. Please try again.";
            }
        }

    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - OwlTalk</title>
<link rel="stylesheet" href="style/style.css">
</head>

<body>

<header class="header">
<div class="container nav-wrap">

<div class="left">
<a class="brand brand-1" href="index.php">
<img src="owl.jpg" alt="OwlTalk Logo" class="logo-img">
<span>OwlTalk</span>
</a>
</div>

<!-- Navigation -->
<div class="right">
<a class="brand" href="index.php">Home</a>
<a class="brand" href="signin.php">Sign In</a>
</div>

</div>
</header>

<div class="container">

<h1>Create Your Account</h1>

<?php if (!empty($message)) { ?>
<p class="error">
<?php echo htmlspecialchars($message); ?>
</p>
<?php } ?>

<!-- Sign up form -->
<form class="card form" method="POST">

<label>Username
<input
type="text"
name="username"
placeholder="Enter a username"
required
value="<?php echo htmlspecialchars($username); ?>">
</label>

<label>Email
<input
type="email"
name="email"
placeholder="test@rowan.edu"
required
value="<?php echo htmlspecialchars($email); ?>">
</label>

<label>Password
<input
type="password"
name="password"
placeholder="Create a password"
required>
</label>

<button class="btn" type="submit">
Join OwlTalk
</button>

</form>

<p>
<a class="brand" href="signin.php">
Already have an account? Sign in here
</a>
</p>

</div>

</body>
</html>