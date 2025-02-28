<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $user_email = $_SESSION['user']; // Get logged-in user's email

    $target_dir = "uploads/profiles/";
    $file_name = basename($_FILES["profile_picture"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name; // Prevent overwriting

    // Check file type
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        die("Only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Move the uploaded file
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        // Update user profile picture in the database
        $update_stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $target_file, $user_email);
        $update_stmt->execute();

        echo "<script>alert('Profile picture updated!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error uploading file. Try again!');</script>";
    }
}
?>
