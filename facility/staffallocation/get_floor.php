<?php
require_once "../include/config.php";

header('Content-Type: application/json');

if (!isset($_GET['department-select'])) {
    echo json_encode(['error' => 'Department not specified']);
    exit;
}

try {
    $department = $_GET['department-select'];
    error_log("Fetching floor for department: " . $department);

    // Prepare SQL to get floor for the selected department
    $sql = "SELECT floor FROM department_table WHERE department = :department";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':department', $department, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode(['floor' => $result['floor']]);
    } else {
        echo json_encode(['error' => 'Floor not found for department']);
    }
    
} catch (PDOException $e) {
    error_log("Error in get_floor.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch floor: ' . $e->getMessage()]);
}
?>