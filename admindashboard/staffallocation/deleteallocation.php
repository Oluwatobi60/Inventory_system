<?php
require_once "../include/config.php";
require_once "../../include/utils.php";

// Start output buffering to prevent header issues
ob_start();

try {
    // Check if ID is provided and is numeric
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid request ID');
    }

    $id = intval($_GET['id']); // Sanitize the input

    // If confirmation is not yet received, show confirmation form
    if (!isset($_GET['confirm'])) {
        echo '<!DOCTYPE html><html><head><title>Confirm Delete</title></head><body>';
        echo '<div style="display:flex;justify-content:center;align-items:center;height:100vh;">';
        echo '<div style="background:#fff;padding:2rem 2.5rem;border-radius:1rem;box-shadow:0 4px 24px rgba(0,0,0,0.12);text-align:center;">';
        echo '<h3>Are you sure you want to delete this staff allocation?</h3>';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="id" value="'.htmlspecialchars($id).'">';
        echo '<button type="submit" name="confirm" value="yes" style="background:#d9534f;color:#fff;padding:0.5rem 1.5rem;border:none;border-radius:0.5rem;margin-right:1rem;">Yes, Delete</button>';
        echo '<a href="../staffallocation.php" style="background:#5bc0de;color:#fff;padding:0.5rem 1.5rem;border-radius:0.5rem;text-decoration:none;">Cancel</a>';
        echo '</form>';
        echo '</div></div></body></html>';
        exit();

        // If confirmed, proceed to delete the record
    } elseif ($_GET['confirm'] === 'yes') {
        // Begin transaction
        $conn->beginTransaction();

        try {
            // First, get the request details to update asset quantity
            $selectSql = "SELECT asset_name, quantity FROM staff_table WHERE id = :id";
            $selectStmt = $conn->prepare($selectSql);
            $selectStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $selectStmt->execute();
            
            $request = $selectStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$request) {
                throw new Exception('Allocation not found');
            }

            // Update asset quantity - add back the requested quantity
            $updateAssetSql = "UPDATE asset_table SET quantity = quantity + :quantity 
                             WHERE asset_name = :asset_name";
            $updateAssetStmt = $conn->prepare($updateAssetSql);
            $updateAssetStmt->bindParam(':quantity', $request['quantity'], PDO::PARAM_INT);
            $updateAssetStmt->bindParam(':asset_name', $request['asset_name'], PDO::PARAM_STR);
            $updateAssetStmt->execute();

            // Now delete the allocation
            $deleteSql = "DELETE FROM staff_table WHERE id = :id";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Commit the transaction
            $conn->commit();
            
            echo "<script>alert('Staff Allocation deleted successfully and asset quantity updated');</script>";
            echo "<script>window.location.href = '../staffallocation.php';</script>";
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            logError("Error in delete allocation: " . $e->getMessage());
            echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
            echo "<script>window.location.href = '../staffallocation.php';</script>";
        }
    }
} catch (Exception $e) {
    logError("Error in delete request: " . $e->getMessage());
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    echo "<script>window.location.href = '../staffallocation.php';</script>";
}

ob_end_flush();
?>

