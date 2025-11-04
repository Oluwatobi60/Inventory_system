<?php
// Start the PHP session to enable session variables
session_start();

// Start output buffering to prevent header modification issues
ob_start();

// Include database configuration file
include 'include/config.php';

// Define a function to log errors with timestamps
function logError($message) {
    // Set the path to the error log file
    $logFile = 'error_log.txt';
    // Get current timestamp for the log entry
    $timestamp = date("Y-m-d H:i:s");
    // Append the error message with timestamp to the log file
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Check if database connection is successful
if (!$conn) {
    // Log and display database connection failure
    logError("Database connection failed");
    die("Database connection failed");
}

// Initialize array to store department names
$departments = [];

// SQL query to fetch unique department names in lowercase
$deptQuery = "SELECT DISTINCT LOWER(department) AS department FROM user_table WHERE department IS NOT NULL";

try {
    // Execute the department query
    $deptResult = $conn->query($deptQuery);
    
    // Fetch each department and add to departments array
    while ($row = $deptResult->fetch(PDO::FETCH_ASSOC)) {
        $departments[] = $row['department'];
    }
} catch (PDOException $e) {
    // Log any database errors that occur while fetching departments
    logError("Failed to fetch departments: " . $e->getMessage());
}

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove whitespace from username and password inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate that both username and password are provided
    if (!empty($username) && !empty($password)) {
        // SQL query to find user by email or username
        $query = "SELECT * FROM user_table WHERE email = ? OR username = ?";
        
        try {
            // Prepare and execute the query with parameters
            $stmt = $conn->prepare($query);
            $stmt->execute([$username, $username]);
            
            // Fetch the user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);            // Check if user exists
            if ($user !== false) {
                $db_password = $user['password'];
                // Try password_verify first, fallback to plain text if not hashed
                $is_bcrypt = (strlen($db_password) === 60 && (substr($db_password, 0, 4) === '$2y$' || substr($db_password, 0, 4) === '$2a$' || substr($db_password, 0, 4) === '$2b$'));
                if (($is_bcrypt && password_verify($password, $db_password)) || (!$is_bcrypt && $password === $db_password)) {
                    // Log successful login attempt without password info
                    logError("Login successful for user '$username'");

                    // Convert role to lowercase for consistent comparison
                    $role = strtolower($user['role']);
                    // Get department and ensure it exists
                    $department = isset($user['department']) ? strtolower($user['department']) : '';

                    // Validate user role and department
                    if (in_array($role, ['audit', 'procurement', 'admin', 'facility', 'account']) &&
                        in_array($department, $departments)) {
                        
                        // Set session variables for the authenticated user
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['department'] = $user['department'];

                        // Display success message
                        echo "<div class='alert alert-success text-center'>Login successful! Redirecting...</div>";

                        // Redirect user based on their role
                        switch ($role) {
                            case 'audit':
                                header("Location: audit/auditdashboard.php");
                                break;
                            case 'procurement':
                                header("Location: procurement/procurementdashboard.php");
                                break;
                            case 'facility':
                                header("Location: facility/facilitydashboard.php");
                                break;
                            case 'admin':
                                header("Location: admindashboard/index.php");
                                break;
                            case 'account':
                                header("Location: account/accountdashboard.php");
                                break;
                        }
                        // Stop execution after redirect
                        exit();
                    } else {
                        // Set error for invalid role or department
                        $error = "Invalid role or category.";
                        logError("Login failed: Invalid role or category for user '$username'.");
                    }
                } else {
                    // Set error for password mismatch
                    $error = "Invalid username or password.";
                    logError("Login failed: Password mismatch for user '$username'.");
                }
            } else {
                // Set error for non-existent user
                $error = "Invalid username or password.";
                logError("Login failed: No matching user found for '$username'.");
            }
        } catch (PDOException $e) {
            // Set error for database query failure
            $error = "Failed to prepare the SQL statement.";
            logError("SQL error: " . $e->getMessage());
        }
    } else {
        // Set error for empty fields
        $error = "Please fill in all fields.";
        logError("Login failed: Missing username or password.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="16x16" href="admindashboard/assets/images/isalu-logo.png">
    <title>Login | Asset Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts for modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 0;
            animation: fadeInBg 1.5s;
        }
        @keyframes fadeInBg {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .logo-animate {
            animation: logoDrop 1.2s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes logoDrop {
            0% { opacity: 0; transform: translateY(-60px) scale(0.8) rotate(-10deg); }
            60% { opacity: 1; transform: translateY(10px) scale(1.05) rotate(2deg); }
            100% { opacity: 1; transform: translateY(0) scale(1) rotate(0); }
        }
        .card-animate {
            animation: cardFadeIn 1.2s 0.5s both;
        }
        @keyframes cardFadeIn {
            0% { opacity: 0; transform: translateY(40px) scale(0.95); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .btn-primary {
            background: linear-gradient(90deg, #1e90ff 0%, #00c6ff 100%);
            border: none;
            transition: box-shadow 0.3s, transform 0.2s;
        }
        .btn-primary:hover, .btn-primary:focus {
            box-shadow: 0 4px 16px rgba(30,144,255,0.25);
            transform: translateY(-2px) scale(1.03);
        }
        .card {
            border-radius: 1.2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        }
        .form-control:focus {
            border-color: #1e90ff;
            box-shadow: 0 0 0 0.2rem rgba(30,144,255,0.15);
        }
        .forgot-link {
            color: #1e90ff;
            transition: color 0.2s;
        }
        .forgot-link:hover {
            color: #005fa3;
            text-decoration: underline;
        }
    </style>
</head>
<!-- Set body background and styling -->
<body class="bg-light" style="background-image: url('admindashboard/assets/images/isalu1.jpg'); background-size: cover; background-position: center; min-height: 100vh; position: relative;">
    <div class="bg-overlay"></div>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100" style="position: relative; z-index: 1;">
        <!-- Logo above the form -->
        <div class="mb-4 text-center">
            <img src="admindashboard/assets/images/isalu-logo.png" alt="Isalu Logo" class="img-fluid rounded shadow-lg logo-animate" style="max-height: 180px; background: rgba(255,255,255,0.8); padding: 10px;">
        </div>
        <!-- Login card -->
        <div class="card shadow-lg p-4 card-animate" style="min-width: 320px; max-width: 400px; width: 100%; background: rgba(255,255,255,0.95);">
            <h3 class="text-center mb-4">Asset Management</h3>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Email/Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="#" class="text-decoration-none forgot-link">Forgot Password?</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>