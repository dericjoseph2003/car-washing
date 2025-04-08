<?php
require_once 'conn.php';

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$staffId = $data['id'];
$active = $data['active'];

// Update the staff status
$sql = "UPDATE staff SET active = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $active, $staffId);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
}

header('Content-Type: application/json');
echo json_encode($response); 