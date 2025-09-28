<?php
require_once dirname(__FILE__) . "/../include/config.php";
require_once dirname(__FILE__) . "/../../include/utils.php";

try {
    // Validate input
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Invalid asset ID");
    }
    
    $id = (int)$_GET['id'];

    // If not confirmed, show confirmation form
    if (!isset($_GET['confirm'])) {
        echo '<!DOCTYPE html><html><head><title>Confirm Delete</title></head><body>';
        echo '<div style="display:flex;justify-content:center;align-items:center;height:100vh;">';
        echo '<div style="background:#fff;padding:2rem 2.5rem;border-radius:1rem;box-shadow:0 4px 24px rgba(0,0,0,0.12);text-align:center;">';
        echo '<h3>Are you sure you want to delete this asset?</h3>';
        echo '<form method="get" action="">';
        echo '<input type="hidden" name="id" value="'.htmlspecialchars($id).'">';
        echo '<button type="submit" name="confirm" value="yes" style="background:#d9534f;color:#fff;padding:0.5rem 1.5rem;border:none;border-radius:0.5rem;margin-right:1rem;">Yes, Delete</button>';
        echo '<a href="../assets.php" style="background:#5bc0de;color:#fff;padding:0.5rem 1.5rem;border-radius:0.5rem;text-decoration:none;">Cancel</a>';
        echo '</form>';
        echo '</div></div></body></html>';
        exit();

        // If confirmed, delete the record
    } elseif ($_GET['confirm'] === 'yes') {
        // Delete the asset using prepared statement
        $sql = "DELETE FROM asset_table WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            logError("Asset deleted successfully (ID: $id)");
            echo "<script>alert('Record deleted successfully'); window.location.href='../assets.php';</script>";
            exit();
        } else {
            throw new PDOException("Failed to delete asset");
        }
    } else {
        header("Location: ../assets.php");
        exit();
    }
} catch (Exception $e) {
    logError("Error in deleteasset.php: " . $e->getMessage());
    echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='../assets.php';</script>";
    exit();
}
?>

