<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is received
if (isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'];
    
    // Validate status
    $allowed_statuses = ['pending', 'in_progress', 'completed'];
    if (!in_array($status, $allowed_statuses)) {
        header("Location: booking_details.php?id=$booking_id&error=invalid_status");
        exit();
    }
    
    // Update the booking status
    $sql = "UPDATE booking SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        header("Location: booking_details.php?id=$booking_id&success=1");
    } else {
        header("Location: booking_details.php?id=$booking_id&error=update_failed");
    }
} else {
    header("Location: service_history.php");
}

$conn->close();
?> 