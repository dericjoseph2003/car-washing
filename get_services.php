<?php
require_once "conn.php";

if (isset($_GET['category'])) {
    $category = $_GET['category'];
    
    $sql = "SELECT service_name FROM services WHERE category = ? ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $service_name = $row['service_name'];
        $service_value = strtolower(str_replace(' ', '_', $service_name));
        $services[] = [
            'value' => $service_value,
            'label' => $service_name
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'services' => $services]);
    
    $stmt->close();
    $conn->close();
}
?> 