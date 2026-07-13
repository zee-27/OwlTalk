<?php
require_once "auth.php";
requireLogin();

$current_user_id = $_SESSION["user_id"];
$conversation_mode = false;
$other_user = null;
$messages_result = null;

/* Load conversations list */
$conversations_sql = "
    SELECT DISTINCT
        users.id,
        users.username
    FROM messages
    JOIN users
      ON users.id = CASE
            WHEN messages.sender_id = ? THEN messages.receiver_id
            ELSE messages.sender_id
        END
    WHERE messages.sender_id = ? OR messages.receiver_id = ?
    ORDER BY users.username ASC
";

$conversations_stmt = mysqli_prepare($conn, $conversations_sql);
mysqli_stmt_bind_param($conversations_stmt, "iii", $current_user_id, $current_user_id, $current_user_id);
mysqli_stmt_execute($conversations_stmt);
$conversations_result = mysqli_stmt_get_result($conversations_stmt);

/* Open selected conversation */
if (isset($_GET["user_id"]) && is_numeric($_GET["user_id"])) {
    $other_user_id = (int)$_GET["user_id"];

    if ($other_user_id === $current_user_id) {
        die("You cannot message yourself.");
    }

    $conversation_mode = true;

    // Get other user's info
    $user_sql = "SELECT id, username FROM users WHERE id = ?";
    $user_stmt = mysqli_prepare($conn, $user_sql);
    mysqli_stmt_bind_param($user_stmt, "i", $other_user_id);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    $other_user = mysqli_fetch_assoc($user_result);
    mysqli_stmt_close($user_stmt);

    if (!$other_user) {
        die("User not found.");
    }

    // Send new message
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $message_text = trim($_POST["message_text"]);

        if ($message_text !== "") {
            $insert_sql = "INSERT INTO messages (sender_id, receiver_id, message_text)
                           VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "iis", $current_user_id, $other_user_id, $message_text);
            mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);

            header("Location: messages.php?user_id=" . $other_user_id);
            exit;
        }
    }

    // Load messages
    $messages_sql = "SELECT messages.*, users.username AS sender_name
                     FROM messages
                     JOIN users ON messages.sender_id = users.id
                     WHERE (sender_id = ? AND receiver_id = ?)
                        OR (sender_id = ? AND receiver_id = ?)
                     ORDER BY created_at ASC";

    $messages_stmt = mysqli_prepare($conn, $messages_sql);
    mysqli_stmt_bind_param(
        $messages_stmt,
        "iiii",
        $current_user_id,
        $other_user_id,
        $other_user_id,
        $current_user_id
    );
    mysqli_stmt_execute($messages_stmt);
    $messages_result = mysqli_stmt_get_result($messages_stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages - OwlTalk</title>
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
<a class="brand" href="account.php">Account</a>
<a class="brand" href="messages.php">Messages</a>
<a class="brand" href="logout.php">Logout</a>
</div>

</div>
</header>

<div class="container">

<!-- Conversations list -->
<div class="card">
<h2>Messages</h2>

<?php if (mysqli_num_rows($conversations_result) > 0) { ?>
<?php while ($conversation = mysqli_fetch_assoc($conversations_result)) { ?>

<p>
<a class="brand" href="messages.php?user_id=<?php echo $conversation["id"]; ?>">
<?php echo htmlspecialchars($conversation["username"]); ?>
</a>
</p>

<?php } ?>
<?php } else { ?>

<p>No conversations yet.</p>

<?php } ?>

</div>

<!-- Open conversation -->
<?php if ($conversation_mode && $other_user) { ?>

<div class="card">
<h2>Conversation with <?php echo htmlspecialchars($other_user["username"]); ?></h2>
</div>

<div class="card">

<?php if (mysqli_num_rows($messages_result) > 0) { ?>
<?php while ($message = mysqli_fetch_assoc($messages_result)) { ?>

<div style="margin-bottom: 15px;">

<p>
<strong><?php echo htmlspecialchars($message["sender_name"]); ?>:</strong>
</p>

<p><?php echo nl2br(htmlspecialchars($message["message_text"])); ?></p>

<p>
<small>
<?php echo date("M j, Y g:i A", strtotime($message["created_at"])); ?>
</small>
</p>

</div>
<hr>

<?php } ?>
<?php } else { ?>

<p>No messages yet.</p>

<?php } ?>

</div>

<!-- Send message form -->
<form class="card form" method="POST">

<label>Send a message
<textarea name="message_text" rows="4" required></textarea>
</label>

<button class="btn" type="submit">Send</button>

</form>

<?php } ?>

</div>

</body>
</html>