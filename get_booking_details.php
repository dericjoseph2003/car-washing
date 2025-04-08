<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Booking ID not provided']);
    exit;
}

$booking_id = intval($_GET['id']);

$sql = "SELECT * FROM booking WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'booking' => $booking
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found'
    ]);
}

$stmt->close();
$conn->close(); 