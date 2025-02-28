<?php
include 'db.php';
$password = password_hash("pixil123", PASSWORD_BCRYPT);
$conn->query("INSERT INTO users (email, password) VALUES ('PIXIL0001', '$password')");
echo "User added successfully!";
?>
