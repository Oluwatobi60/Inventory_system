
<?php
require "../include/config.php";

// Get the id from the URL and validate it
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    // Invalid or missing id
    header("Location: ../maintenance.php");
    exit();
}

// If not confirmed, show confirmation form
if (!isset($_GET['confirm'])) {
    echo '<!DOCTYPE html><html><head><title>Confirm Delete</title></head><body>';
    echo '<div style="display:flex;justify-content:center;align-items:center;height:100vh;">';
    echo '<div style="background:#fff;padding:2rem 2.5rem;border-radius:1rem;box-shadow:0 4px 24px rgba(0,0,0,0.12);text-align:center;">';
    echo '<h3>Are you sure you want to delete this maintenance record?</h3>';
    echo '<form method="get" action="">';
    echo '<input type="hidden" name="id" value="'.htmlspecialchars($id).'">';
    echo '<button type="submit" name="confirm" value="yes" style="background:#d9534f;color:#fff;padding:0.5rem 1.5rem;border:none;border-radius:0.5rem;margin-right:1rem;">Yes, Delete</button>';
    echo '<a href="../maintenance.php" style="background:#5bc0de;color:#fff;padding:0.5rem 1.5rem;border-radius:0.5rem;text-decoration:none;">Cancel</a>';
    echo '</form>';
    echo '</div></div></body></html>';
    exit();
}

// If confirmed, delete the record
if ($_GET['confirm'] === 'yes') {
    $stmt = $conn->prepare("DELETE FROM maintenance_table WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        echo "<script>alert('Record deleted successfully');window.location.href='../maintenance.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting record');window.location.href='../maintenance.php';</script>";
        exit();
    }
}
?>

