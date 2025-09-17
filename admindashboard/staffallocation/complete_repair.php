<?php
require_once dirname(__FILE__) . "/../include/config.php";
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$asset_id = isset($data['asset_id']) ? intval($data['asset_id']) : 0;

if ($asset_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit;
}

try {
    // Update the repair status to Completed and set complete field to 1
    $sql = "UPDATE repair_asset SET status = NULL, completed_date = NOW(), completed = 1 WHERE asset_id = :asset_id AND status = 'Under Repair'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt->execute();

    
    // Also reset status in staff_table if you have a status column
    $sql2 = "UPDATE staff_table SET status = NULL WHERE id = :asset_id";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt2->execute();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
