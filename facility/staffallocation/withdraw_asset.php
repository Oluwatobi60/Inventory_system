<?php
// Include the database configuration file for DB connection
require_once dirname(__FILE__) . "/../../include/config.php";

// Set the response content type to JSON
header('Content-Type: application/json');

// Get the POST data sent to this endpoint and decode it from JSON
$data = json_decode(file_get_contents('php://input'), true);

// Accept either repair_asset.id (id) or staff_table.id (asset_id)
$repair_id = isset($data['id']) ? intval($data['id']) : 0;
$asset_id = isset($data['asset_id']) ? intval($data['asset_id']) : 0;
$withdraw_qty = isset($data['quantity']) ? intval($data['quantity']) : 1;
$withdrawn_by = isset($data['withdrawn_by']) ? $data['withdrawn_by'] : '';
$reason = isset($data['reason']) ? $data['reason'] : '';

// At least one identifier must be provided and quantity must be valid
if (($repair_id <= 0 && $asset_id <= 0) || $withdraw_qty < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid id/asset_id or quantity']);
    exit;
}

// If only repair_id was provided, look up the corresponding staff asset id
if ($asset_id <= 0 && $repair_id > 0) {
    $tmp = $conn->prepare("SELECT asset_id FROM repair_asset WHERE id = :repair_id LIMIT 1");
    $tmp->bindParam(':repair_id', $repair_id, PDO::PARAM_INT);
    $tmp->execute();
    $rowtmp = $tmp->fetch(PDO::FETCH_ASSOC);
    if (!$rowtmp) {
        echo json_encode(['success' => false, 'message' => 'Repair record not found']);
        exit;
    }
    $asset_id = (int)$rowtmp['asset_id'];
}

try {
    // Get current quantity from staff_table using asset_id
    $sql = "SELECT * FROM staff_table WHERE id = :asset_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt->execute();
    $asset = $stmt->fetch(PDO::FETCH_ASSOC);

    // If staff asset not found, try treating provided asset_id as a repair_asset.id
    if (!$asset) {
        $maybe_repair_id = $asset_id;
        $tmp = $conn->prepare("SELECT asset_id FROM repair_asset WHERE id = :rid AND status = 'Under Repair' LIMIT 1");
        $tmp->bindParam(':rid', $maybe_repair_id, PDO::PARAM_INT);
        $tmp->execute();
        $tmpRow = $tmp->fetch(PDO::FETCH_ASSOC);
        if ($tmpRow) {
            $repair_id = $maybe_repair_id;
            $asset_id = (int)$tmpRow['asset_id'];
            // re-fetch staff asset
            $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
            $stmt->execute();
            $asset = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$asset) {
        $debugMsg = 'Asset not found for asset_id=' . $asset_id;
        if ($repair_id > 0) $debugMsg .= ' (lookup via repair_id=' . $repair_id . ')';
        echo json_encode(['success' => false, 'message' => $debugMsg]);
        exit;
    }

    // Check total quantity under repair for this asset (only consider rows not yet withdrawn)
    $sql_repair_check = "SELECT SUM(quantity) as total_repair_qty FROM repair_asset WHERE asset_id = :asset_id AND status = 'Under Repair' AND withdrawn = 0";
    $stmt_repair_check = $conn->prepare($sql_repair_check);
    $stmt_repair_check->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt_repair_check->execute();
    $repair_check = $stmt_repair_check->fetch(PDO::FETCH_ASSOC);
    $total_repair_qty = $repair_check && $repair_check['total_repair_qty'] ? (int)$repair_check['total_repair_qty'] : 0;

    if ($total_repair_qty == 0) {
        // Not under repair (or nothing pending) — deduct from staff_table.quantity
        $new_staff_qty = max(0, (int)$asset['quantity'] - $withdraw_qty);
        $sql_update_staff = "UPDATE staff_table SET quantity = :new_qty WHERE id = :asset_id";
        $stmt_update_staff = $conn->prepare($sql_update_staff);
        $stmt_update_staff->bindParam(':new_qty', $new_staff_qty, PDO::PARAM_INT);
        $stmt_update_staff->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt_update_staff->execute();

        // Insert withdrawn asset record for the total quantity withdrawn (single record)
    $sql3 = "INSERT INTO withdrawn_asset (asset_id, reg_no, asset_name, department, floor, withdrawn_date, withdrawn_by, withdrawn_reason, qty, status) VALUES (:asset_id, :reg_no, :asset_name, :department, :floor, NOW(), :withdrawn_by, :reason, :qty, 1)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt3->bindParam(':reg_no', $asset['reg_no']);
    $stmt3->bindParam(':asset_name', $asset['asset_name']);
    $stmt3->bindParam(':department', $asset['department']);
    $stmt3->bindParam(':floor', $asset['floor']);
    $stmt3->bindParam(':withdrawn_by', $withdrawn_by);
    $stmt3->bindParam(':reason', $reason);
    $stmt3->bindParam(':qty', $withdraw_qty, PDO::PARAM_INT);
    $stmt3->execute();
    } else {
        // Deduct from repair_asset rows under repair for this asset (not yet withdrawn)
        $sql4 = "SELECT * FROM repair_asset WHERE asset_id = :asset_id AND status = 'Under Repair' AND withdrawn = 0 ORDER BY id ASC";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt4->execute();
        $repairs = $stmt4->fetchAll(PDO::FETCH_ASSOC);

        $qty_to_withdraw = $withdraw_qty;
        $total_withdrawn = 0;
        foreach ($repairs as $repair) {
            if ($qty_to_withdraw <= 0) break;
            $row_qty = (int)$repair['quantity'];

            if ($row_qty <= $qty_to_withdraw) {
                // fully withdraw this repair row
                $sql5 = "UPDATE repair_asset SET quantity = 0, withdrawn = 1, status = NULL, withdrawn_reason = :reason, withdrawn_date = NOW() WHERE id = :id";
                $stmt5 = $conn->prepare($sql5);
                $stmt5->bindParam(':reason', $reason);
                $stmt5->bindParam(':id', $repair['id'], PDO::PARAM_INT);
                $stmt5->execute();
                $qty_to_withdraw -= $row_qty;
                $total_withdrawn += $row_qty;
            } else {
                // partially withdraw this repair row
                $remaining_qty = $row_qty - $qty_to_withdraw;
                $sql5 = "UPDATE repair_asset SET quantity = :remaining_qty, withdrawn = 1, withdrawn_reason = :reason, withdrawn_date = NOW() WHERE id = :id";
                $stmt5 = $conn->prepare($sql5);
                $stmt5->bindParam(':remaining_qty', $remaining_qty, PDO::PARAM_INT);
                $stmt5->bindParam(':reason', $reason);
                $stmt5->bindParam(':id', $repair['id'], PDO::PARAM_INT);
                $stmt5->execute();
                $total_withdrawn += $qty_to_withdraw;
                $qty_to_withdraw = 0;
            }
        }

        // If we withdrew anything from repair rows, record a single withdrawn_asset entry
        if ($total_withdrawn > 0) {
            $sql3 = "INSERT INTO withdrawn_asset (asset_id, reg_no, asset_name, department, floor, withdrawn_date, withdrawn_by, withdrawn_reason, qty, status) VALUES (:asset_id, :reg_no, :asset_name, :department, :floor, NOW(), :withdrawn_by, :reason, :qty, 1)";
            $stmt3 = $conn->prepare($sql3);
            $stmt3->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
            $stmt3->bindParam(':reg_no', $asset['reg_no']);
            $stmt3->bindParam(':asset_name', $asset['asset_name']);
            $stmt3->bindParam(':department', $asset['department']);
            $stmt3->bindParam(':floor', $asset['floor']);
            $stmt3->bindParam(':withdrawn_by', $withdrawn_by);
            $stmt3->bindParam(':reason', $reason);
            $stmt3->bindParam(':qty', $total_withdrawn, PDO::PARAM_INT);
            $stmt3->execute();
        }
    }

    // Return success response as JSON
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // If a database error occurs, return error response as JSON
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}




