<?php
require_once "auth.php";
requireLogin();

$user_id = $_SESSION["user_id"];

// Check upload
if (!isset($_FILES["profile_pic"]) || $_FILES["profile_pic"]["error"] !== 0) {
    echo "<script>
        window.parent.document.getElementById('picStatus').innerText = 'No file selected or upload error.';
        window.parent.document.getElementById('picStatus').style.color = 'red';
    </script>";
    exit;
}

// Validate file type
$allowed_types = ["jpg", "jpeg", "png", "gif"];
$file_ext = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_types)) {
    echo "<script>
        window.parent.document.getElementById('picStatus').innerText = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        window.parent.document.getElementById('picStatus').style.color = 'red';
    </script>";
    exit;
}

// Create uploads folder if needed
$target_dir = "uploads/";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Save uploaded file
$file_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
$target_file = $target_dir . $file_name;

if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {

    // Save filename in database
    $sql = "UPDATE users SET profile_pic = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $file_name, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);

        echo "<script>
            window.parent.document.getElementById('profileDisplay').src = 'uploads/$file_name';
            window.parent.document.getElementById('picStatus').innerText = 'Photo updated!';
            window.parent.document.getElementById('picStatus').style.color = 'green';
        </script>";
    } else {
        mysqli_stmt_close($stmt);

        echo "<script>
            window.parent.document.getElementById('picStatus').innerText = 'Database update failed.';
            window.parent.document.getElementById('picStatus').style.color = 'red';
        </script>";
    }
} else {
    echo "<script>
        window.parent.document.getElementById('picStatus').innerText = 'Upload failed. Check uploads folder.';
        window.parent.document.getElementById('picStatus').style.color = 'red';
    </script>";
}
?>