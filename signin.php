<?php
require_once "db.php";

$message = "";
$email = "";

// Handle sign in form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if ($email !== "" && $password !== "") {
        $sql = "SELECT id, username, password FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        // Check password and create session
        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];

            mysqli_stmt_close($stmt);
            header("Location: account.php");
            exit;
        } else {
            $message = "Invalid email or password.";
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
<title>Sign In - OwlTalk</title>
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
<a class="brand" href="signup.php">Sign Up</a>
</div>

</div>
</header>

<div class="container">
<h1>Sign In</h1>

<?php if (!empty($message)) { ?>
<p class="error"><?php echo htmlspecialchars($message); ?></p>
<?php } ?>

<!-- Sign in form -->
<form class="card form" method="POST">

<label>Email
<input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
</label>

<label>Password
<input type="password" name="password" required>
</label>

<button class="btn" type="submit">Login</button>
</form>

<p><a class="brand" href="signup.php">No account? Sign up</a></p>
</div>

</body>
</html>