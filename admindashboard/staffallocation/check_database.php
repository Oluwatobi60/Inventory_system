<?php
require_once "../include/config.php";

try {
    echo "Testing database connection...<br>";
    echo "Database name: " . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . "<br>";
    
    // Test if tables exist
    $tables = ['asset_table', 'department_table', 'user_table', 'staff_table']; // Your actual table names
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn();
        echo "Table '$table' exists: " . ($exists ? 'Yes' : 'No') . "<br>";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>