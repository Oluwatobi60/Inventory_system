<?php
require_once '../../include/config.php'; // Add this line to define $conn
// Get the first name and last name of the logged-in admin
try {
    $pro_username = $_SESSION['username'];
    $pro_query = "SELECT firstname, lastname FROM user_table WHERE username = :username";
    $stmt = $conn->prepare($pro_query);
    $stmt->bindParam(':username', $pro_username, PDO::PARAM_STR);
    $stmt->execute();
    $pro_row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pro_first_name = $pro_row['firstname'] ?? '';
    $pro_last_name = $pro_row['lastname'] ?? '';

    } catch (PDOException $e) {
    // Log error and set default values
    error_log("Database error in prodashboard.php: " . $e->getMessage());
    $pro_first_name = 'User';
    $pro_last_name = '';
}