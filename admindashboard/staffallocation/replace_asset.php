<?php
require_once dirname(__FILE__) . "/../include/config.php";
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$asset_id = isset($data['asset_id']) ? intval($data['asset_id']) : 0;

if ($asset_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit;
}

try {
    // Mark the asset as replaced in repair_asset
    $sql = "UPDATE repair_asset SET status = NULL, replaced_date = NOW() WHERE asset_id = :asset_id AND status = 'Withdrawn'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt->execute();

    // Reset withdrawn and status in staff_table
    $sql2 = "UPDATE staff_table SET withdrawn = 0, status = NULL WHERE id = :asset_id";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt2->execute();

    // Also reset status in repair_asset
    $sql3 = "UPDATE repair_asset SET status = NULL WHERE asset_id = :asset_id";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt3->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
