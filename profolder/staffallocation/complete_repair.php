<?php
require_once dirname(__FILE__) . "/../../include/config.php";
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
// Accept either repair_asset.id (id) or staff_table.id (asset_id)
$id = isset($data['id']) ? intval($data['id']) : 0;
$asset_id = isset($data['asset_id']) ? intval($data['asset_id']) : 0;
$quantity_completed = isset($data['quantity']) ? intval($data['quantity']) : 1;

if (($id <= 0 && $asset_id <= 0) || $quantity_completed < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid id/asset_id or quantity']);
    exit;
}

try {
    // Cleanup completed rows for given id if provided
    if ($id > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM repair_asset WHERE id = :id AND quantity=0 AND completed=1 AND replaced=0");
        $delete_stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $delete_stmt->execute();
    }

    // Get repair_asset rows under repair. If asset_id provided, fetch by asset_id (may return multiple rows).
    if ($asset_id > 0) {
        $sql = "SELECT id, quantity FROM repair_asset WHERE asset_id = :asset_id AND status = 'Under Repair'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt->execute();
        $repair_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // id provided: fetch that single repair row if it's under repair
        $sql = "SELECT id, quantity, asset_id FROM repair_asset WHERE id = :id AND status = 'Under Repair'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $single = $stmt->fetch(PDO::FETCH_ASSOC);
        $repair_rows = $single ? [ ['id' => $single['id'], 'quantity' => $single['quantity']] ] : [];
        // if we need asset_id later, extract it
        if ($single && empty($asset_id)) {
            $asset_id = (int)$single['asset_id'];
        }
    }

    if (!$repair_rows || count($repair_rows) == 0) {
        echo json_encode(['success' => false, 'message' => 'No asset found under repair']);
        exit;
    }

    // Calculate total quantity under repair
    $total_repair_qty = 0;
    foreach ($repair_rows as $row) {
        $total_repair_qty += (int)$row['quantity'];
    }

    if ($quantity_completed > $total_repair_qty) {
        echo json_encode(['success' => false, 'message' => 'Completed quantity exceeds under repair quantity']);
        exit;
    }

    // Fetch asset details from staff_table using asset_id (staff_table.id)
    $sql_asset = "SELECT reg_no, asset_name, department, requested_by, floor FROM staff_table WHERE id = :asset_id";
    $stmt_asset = $conn->prepare($sql_asset);
    $stmt_asset->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt_asset->execute();
    $asset_details = $stmt_asset->fetch(PDO::FETCH_ASSOC);
    // Ensure 'floor' key exists in asset_details
    if (!isset($asset_details['floor'])) {
        $asset_details['floor'] = '';
    }

    $qty_to_complete = $quantity_completed;
    foreach ($repair_rows as $row) {
        if ($qty_to_complete <= 0) break;
        $row_qty = (int)$row['quantity'];
        if ($row_qty <= $qty_to_complete) {
            // Complete this row
            $sql = "UPDATE repair_asset SET status = NULL, completed_date = NOW(), completed = 1, reg_no = :reg_no, asset_name = :asset_name, department = :department, reported_by = :reported_by, quantity = 0 WHERE id = :repair_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':repair_id', $row['id'], PDO::PARAM_INT);
            $stmt->bindParam(':reg_no', $asset_details['reg_no']);
            $stmt->bindParam(':asset_name', $asset_details['asset_name']);
            $stmt->bindParam(':department', $asset_details['department']);
            $stmt->bindParam(':reported_by', $asset_details['requested_by']);
            $stmt->execute();

            // Insert completed record for this row
            $sql_completed = "INSERT INTO completed_asset (asset_id, quantity, status, floor, completed, completed_date, reg_no, asset_name, department, reported_by) VALUES (?, ?, ?, ?, 1, NOW(), ?, ?, ?, ?)";
            $stmt_completed = $conn->prepare($sql_completed);
            $success_status = 'Repair Completed';
            $stmt_completed->execute([
                $asset_id,
                $row_qty,
                $success_status,
                $asset_details['floor'],
                $asset_details['reg_no'],
                $asset_details['asset_name'],
                $asset_details['department'],
                $asset_details['requested_by']
            ]);
            $qty_to_complete -= $row_qty;
        } else {
            // Partially complete this row
            $remaining_qty = $row_qty - $qty_to_complete;
            $sql = "UPDATE repair_asset SET quantity = :remaining_qty WHERE id = :repair_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':remaining_qty', $remaining_qty, PDO::PARAM_INT);
            $stmt->bindParam(':repair_id', $row['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Insert completed record for repaired units with details
            $sql_completed = "INSERT INTO completed_asset (asset_id, quantity, status, floor, completed, completed_date, reg_no, asset_name, department, reported_by) VALUES (?, ?, ?, ?, 1, NOW(), ?, ?, ?, ?)";
            $stmt_completed = $conn->prepare($sql_completed);
            $success_status = 'Repair Completed';
            $stmt_completed->execute([
                $asset_id,
                $qty_to_complete,
                $success_status,
                $asset_details['floor'],    
                $asset_details['reg_no'],
                $asset_details['asset_name'],
                $asset_details['department'],
                $asset_details['requested_by']
            ]);
            $qty_to_complete = 0;
        }
    }

    // Update staff_table status to NULL if all repairs are completed
    if ($quantity_completed == $total_repair_qty) {
        $sql2 = "UPDATE staff_table SET status = NULL WHERE id = :asset_id";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
        $stmt2->execute();
    }

    // Add repaired quantity back to staff_table
    $sql3 = "UPDATE staff_table SET quantity = quantity + :qty_completed WHERE id = :asset_id";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bindParam(':qty_completed', $quantity_completed, PDO::PARAM_INT);
    $stmt3->bindParam(':asset_id', $asset_id, PDO::PARAM_INT);
    $stmt3->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

