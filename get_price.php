<?php
require_once "conn.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['category']) && isset($data['service']) && isset($data['vehicle'])) {
    $category = $data['category'];
    $service = ucwords(str_replace('_', ' ', $data['service']));
    $vehicle = strtolower($data['vehicle']);
    
    $sql = "SELECT price_" . $vehicle . " as price FROM services WHERE category = ? AND service_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $category, $service);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'price' => floatval($row['price'])]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Service not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
}

$conn->close(); 