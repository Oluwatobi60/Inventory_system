<?php
require_once dirname(__FILE__) . "/../include/config.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$asset_id = isset($data['asset_id']) ? intval($data['asset_id']) : 0;

if ($asset_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE repair_asset SET withdrawn = 1, replaced = 0 WHERE asset_id = :asset_id");
    $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Asset updated for replacement']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
