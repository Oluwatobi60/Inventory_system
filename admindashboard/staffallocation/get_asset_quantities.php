<?php
require_once dirname(__FILE__) . "/../include/config.php";
header('Content-Type: application/json');

$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;
if ($asset_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit;
}
try {
    $stmt1 = $conn->prepare("SELECT quantity FROM repair_asset WHERE asset_id = :asset_id AND status IS NULL LIMIT 1");
    $stmt1->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt1->execute();
    $repair_qty = (int)($stmt1->fetchColumn() ?: 0);

    // Only count withdrawn_asset rows with status=1 (eligible for replacement)
    $stmt2 = $conn->prepare("SELECT SUM(qty) FROM withdrawn_asset WHERE asset_id = :asset_id AND status = 1");
    $stmt2->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt2->execute();
    $withdrawn_qty = (int)($stmt2->fetchColumn() ?: 0);

    echo json_encode(['success' => true, 'repair_qty' => $repair_qty, 'withdrawn_qty' => $withdrawn_qty]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
