<?php
session_start();
require_once 'conn.php';

if (isset($_SESSION['payment_details'])) {
    $payment_details = $_SESSION['payment_details'];
    $booking_id = $payment_details['booking_id'];
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE booking SET status = 'Confirmed' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Payment completed successfully! Your booking is confirmed.";
    } else {
        $_SESSION['error'] = "Error updating booking status.";
    }
    
    // Clear payment details from session
    unset($_SESSION['payment_details']);
    
    header("Location: view-my-booking.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid payment information";
    header("Location: view-my-booking.php");
    exit();
}
?> 