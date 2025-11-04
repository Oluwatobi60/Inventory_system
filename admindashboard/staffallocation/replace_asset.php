<?php
require_once dirname(__FILE__) . "/../include/config.php";
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);
// Accept repair_id as primary
$repair_id = isset($data['id']) ? intval($data['id']) : 0;
$replace_qty = isset($data['quantity']) ? intval($data['quantity']) : 0;
if ($repair_id <= 0 || $replace_qty < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid repair_id or quantity']);
    exit;
}
try {
    // Check asset state in repair_asset before allowing replacement
    $check_stmt = $conn->prepare("SELECT asset_id, withdrawn, quantity, status, replaced FROM repair_asset WHERE id = :id LIMIT 1");
    $check_stmt->bindParam(':id', $repair_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $row = $check_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || $row['withdrawn'] != 1 || (int)$row['quantity'] !== 0 || !($row['status'] === null || $row['status'] === 'Under Repair') || $row['replaced'] != 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Asset not eligible for replacement']);
        exit;
    }
    $asset_id = (int)$row['asset_id'];
    // Only allow replacement if the latest withdrawn_asset row for this asset_id is from today
    $today = date('Y-m-d');
    $latest_withdrawn_stmt = $conn->prepare("SELECT id, status FROM withdrawn_asset WHERE asset_id = :asset_id AND status = 1 ORDER BY withdrawn_date DESC LIMIT 1");
    $latest_withdrawn_stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $latest_withdrawn_stmt->execute();
    $latest_withdrawn = $latest_withdrawn_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$latest_withdrawn) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No eligible withdrawn_asset row with status=1 to replace']);
        exit;
    }

    // Withdrawn quantity for this asset (only status = 1, eligible for replacement)
    $stmt_withdrawn = $conn->prepare("SELECT SUM(qty) FROM withdrawn_asset WHERE asset_id = :asset_id AND status = 1");
    $stmt_withdrawn->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt_withdrawn->execute();
    $max_withdrawn_qty = (int)($stmt_withdrawn->fetchColumn() ?: 0);


    // Fetch asset details from staff_table
    $asset_stmt = $conn->prepare("SELECT quantity, reg_no, asset_name, department, floor FROM staff_table WHERE id = :asset_id LIMIT 1");
    $asset_stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $asset_stmt->execute();
    $asset_row = $asset_stmt->fetch(PDO::FETCH_ASSOC);
    if (!$asset_row) {
        throw new Exception('Asset details not found in staff_table');
    }
    $current_qty = (int)($asset_row['quantity'] ?? 0);
    $reg_no = $asset_row['reg_no'] ?? '';
    $asset_name = $asset_row['asset_name'] ?? '';
    $department = $asset_row['department'] ?? '';
    $floor = $asset_row['floor'] ?? '';

    $new_qty = $current_qty + $max_withdrawn_qty;
    $stmt2 = $conn->prepare("UPDATE staff_table SET quantity = :new_qty, withdrawn = 0, status = NULL WHERE id = :asset_id");
    $stmt2->bindParam(':new_qty', $new_qty, PDO::PARAM_INT);
    $stmt2->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt2->execute();

    $stmt3 = $conn->prepare("UPDATE repair_asset SET replaced = 1, replaced_date = NOW(), status = NULL WHERE id = :id");
    $stmt3->bindParam(':id', $repair_id, PDO::PARAM_INT);
    $stmt3->execute();

    $stmt4 = $conn->prepare("UPDATE repair_asset SET replaced = 1, replaced_date = NOW(), quantity = :replace_qty, status = NULL WHERE id = :id");
    $stmt4->bindParam(':replace_qty', $replace_qty, PDO::PARAM_INT);
    $stmt4->bindParam(':id', $repair_id, PDO::PARAM_INT);
    $stmt4->execute();

    $conn->exec("CREATE TABLE IF NOT EXISTS asset_replacement_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        asset_id INT NOT NULL,
        replaced_quantity INT NOT NULL,
        replaced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        replaced INT DEFAULT 1,
        reg_no VARCHAR(100),
        asset_name VARCHAR(255),
        department VARCHAR(100),
        floor VARCHAR(100)
    )");
    $stmt5 = $conn->prepare("INSERT INTO asset_replacement_log (asset_id, replaced_quantity, replaced, reg_no, asset_name, department, floor) VALUES (:asset_id, :replace_qty, 1, :reg_no, :asset_name, :department, :floor)");
    $stmt5->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt5->bindParam(':replace_qty', $replace_qty, PDO::PARAM_INT);
    $stmt5->bindParam(':reg_no', $reg_no, PDO::PARAM_STR);
    $stmt5->bindParam(':asset_name', $asset_name, PDO::PARAM_STR);
    $stmt5->bindParam(':department', $department, PDO::PARAM_STR);
    $stmt5->bindParam(':floor', $floor, PDO::PARAM_STR);
    $stmt5->execute();

    // Set status=0 in withdrawn_asset for the specific row with status=1 for this asset_id
    $update_withdrawn = $conn->prepare("UPDATE withdrawn_asset SET status = 0 WHERE asset_id = :asset_id AND status = 1");
    $update_withdrawn->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $update_withdrawn->execute();

    // Clean up any completed/replaced repair_asset records for this asset
    $cleanup_stmt = $conn->prepare("DELETE FROM repair_asset WHERE asset_id = :asset_id AND quantity=0 AND (completed=1 OR replaced=1)");
    $cleanup_stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $cleanup_stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}


 

