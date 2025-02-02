<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must be logged in to update your profile.";
    header("Location: profile.php");
    exit();
}

// Get user input
$newUsername = trim($_POST['newUsername']);
$newEmail = trim($_POST['newEmail']);
$newPhone = trim($_POST['newPhone']);
$currentUsername = $_SESSION['username']; // Get current username from session

// Validate input
if (empty($newUsername) || empty($newEmail)) {
    $_SESSION['error'] = "Username and email cannot be empty!";
    header("Location: profile.php");
    exit();
}

// Check if email already exists for another user
$sqlCheck = "SELECT UserID FROM users WHERE Email = ? AND Username != ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ss", $newEmail, $currentUsername);
$stmtCheck->execute();
$stmtCheck->store_result();
if ($stmtCheck->num_rows > 0) {
    $_SESSION['error'] = "This email is already in use!";
    header("Location: profile.php");
    exit();
}
$stmtCheck->close();

// Update database
$sql = "UPDATE users SET Username=?, Email=?, Phone=? WHERE Username=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $newUsername, $newEmail, $newPhone, $currentUsername);

if ($stmt->execute()) {
    $_SESSION['success'] = "Profile updated successfully!";
    $_SESSION['username'] = $newUsername; // Update session with new username
    $_SESSION['email'] = $newEmail;
    $_SESSION['phone'] = $newPhone;
} else {
    $_SESSION['error'] = "Error updating profile!";
}

$stmt->close();
$conn->close();
header("Location:profile.php");
exit();
?>
