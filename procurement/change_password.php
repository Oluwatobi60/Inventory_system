<?php
session_start();
require_once "../admindashboard/include/config.php";

$message = "";

if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } else {
        // Check old password
        $stmt = $conn->prepare("SELECT password FROM user_table WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($old_password, $row['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE user_table SET password = :password WHERE username = :username");
            $update->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $update->bindParam(':username', $username, PDO::PARAM_STR);
            if ($update->execute()) {
                $message = "Password changed successfully.";
            } else {
                $message = "Failed to update password.";
            }
        } else {
            $message = "Old password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link href="../admindashboard/dist/css/style.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 4px 18px rgba(78,115,223,0.10);}
        h2 { text-align: center; margin-bottom: 24px; color: #4e73df;}
        .form-group { margin-bottom: 18px; }
        .btn-primary { width: 100%; }
        .message { text-align: center; margin-bottom: 18px; color: #e74a3b; }
        .success { color: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Change Password</h2>
        <?php if ($message): ?>
            <div class="message <?php echo ($message === "Password changed successfully.") ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="old_password">Old Password:</label>
                <input type="password" name="old_password" id="old_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Change Password</button>
            <a href="prodashboard.php"><button type="button" class="btn btn-info">Back</button></a>
        </form>
    </div>
</body>
</html>
