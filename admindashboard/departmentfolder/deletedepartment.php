

<?php
require "../include/config.php";

$id = isset($_GET['id']) ? $_GET['id'] : null;
if ($id) {
    try {
        $stmt = $conn->prepare("DELETE FROM department_table WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<script>alert('Record deleted successfully'); window.location.href='../department.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error deleting record'); window.location.href='../department.php';</script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.location.href='../department.php';</script>";
        exit;
    }
} else {
    header('Location: ../department.php');
    exit;
}
?>

