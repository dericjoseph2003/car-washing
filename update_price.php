<?php
require_once "conn.php";

header('Content-Type: application/json');

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['service_id'], $data['prices'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$service_id = $data['service_id'];
$prices = $data['prices'];

// Validate prices
foreach ($prices as $price) {
    if (!is_numeric($price) || $price < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid price value']);
        exit;
    }
}

try {
    $conn->begin_transaction();

    // Update prices in the services table
    $sql = "UPDATE services SET 
            price_sedan = ?,
            price_suv = ?,
            price_truck = ?,
            price_van = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ddddi",
        $prices['sedan'],
        $prices['suv'],
        $prices['truck'],
        $prices['van'],
        $service_id
    );
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } else {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close(); 