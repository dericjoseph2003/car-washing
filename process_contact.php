<?php
require_once 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            header("Location: contact.php?status=success");
        } else {
            throw new Exception("Error executing query");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        header("Location: contact.php?status=error");
    }
    
    $conn->close();
} else {
    header("Location: contact.php");
}
?> 