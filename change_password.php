<?php
session_start();
require_once "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $username = $_SESSION['username'];
    
    // Verify passwords match
    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = "New passwords do not match!";
        header("Location: profile.php");
        exit();
    }
    
    // Get current password from database
    $sql = "SELECT PasswordHash FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare failed
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "User not found!";
        header("Location: profile.php");
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $user['PasswordHash'])) {
        $_SESSION['error'] = "Current password is incorrect!";
        header("Location: profile.php");
        exit();
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $sql = "UPDATE users SET PasswordHash = ? WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare failed
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
    
    $stmt->bind_param("ss", $hashedPassword, $username);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password successfully changed!";
    } else {
        $_SESSION['error'] = "Error changing password: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: profile.php");
    exit();
}
?> 