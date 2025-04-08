<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    
    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        // Successful deletion
        header("Location: bookings.php?message=sdeleted");
        exit();
    } else {
        // Error in deletion
        header("Location: bookings.php?error=delete_failed");
        exit();
    }
    
    $stmt->close();
} else {
    // Invalid or missing ID
    header("Location: bookings.php?error=invalid_id");
    exit();
}

$conn->close();
?> 