<?php
// Only allow logged-in users (optional, adjust as needed)
session_start();

require_once "include/config.php"; // Adjust path if needed
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Database credentials (adjust if needed)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'inventory_sys';

// Full path to mysqldump for XAMPP on Windows (adjust if your XAMPP is installed elsewhere)
$mysqldump_path = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

// When user clicks the export button
if (isset($_POST['export_db'])) {
    $backup_file = "backup_" . $db_name . "_" . date("Y-m-d_H-i-s") . ".sql";
    // Handle empty password for Windows shell
    $pass_part = $db_pass !== '' ? "--password=\"{$db_pass}\"" : "--password=";
    $command = "\"{$mysqldump_path}\" --user=\"{$db_user}\" {$pass_part} --host=\"{$db_host}\" \"{$db_name}\" 2>&1";

    // Execute the command and capture output
    $output = null;
    $result = null;
    exec($command, $output, $result);

    if ($result === 0) {
        $sql_content = implode("\n", $output);
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $backup_file . '"');
        echo $sql_content;
        exit();
    } else {
        $error = "Database backup failed. Please check server permissions and mysqldump availability.<br><pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
    }
}

// Section: Fetch Admin User Details
try {
    // Get the username from the session variable
    $admin_username = $_SESSION['username'];
    
    // SQL query to get the admin's first and last name
    $admin_query = "SELECT firstname, lastname FROM user_table WHERE username = ?";
    
    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare($admin_query);
    
    // Execute the query with the username parameter
    $stmt->execute([$admin_username]);
    
    // Fetch the result as an associative array
    $admin_row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Extract the first name and last name from the result
    $admin_first_name = $admin_row['firstname'];
    $admin_last_name = $admin_row['lastname'];
} catch (PDOException $e) {
    // Log any database errors that occur
    logError("Failed to fetch admin details: " . $e->getMessage());
    // Set default values if query fails
    $admin_first_name = "Admin";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/isalu-logo.png">
    <!-- Google Fonts for modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;900&display=swap" rel="stylesheet">
     <style>
        .logo-icon img.light-logo {
            width: 60px !important;
            max-height: 60px;
            object-fit: contain;
            background: linear-gradient(135deg, #e0e7ff 60%, #fff 100%);
            border-radius: 50%;
            box-shadow: 0 4px 18px rgba(30,144,255,0.10), 0 1.5px 6px rgba(0,0,0,0.07);
            padding: 7px;
            margin: 4px 0 4px 0;
            border: 2.5px solid #1e90ff22;
            transition: box-shadow 0.3s, transform 0.2s, border 0.2s;
        }
          .logo-icon img.light-logo:hover {
            box-shadow: 0 8px 32px rgba(30,144,255,0.18);
            border: 2.5px solid #1e90ff;
            transform: scale(1.08) rotate(-2deg);
        }
        @media (max-width: 600px) {
            .logo-icon img.light-logo {
                width: 48px !important;
                max-height: 48px;
            }
        }
    </style>
    <title>Database Backup</title>
    <link rel="stylesheet" href="assets/libs/bootstrap/dist/css/bootstrap.min.css">
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
</head>
<body>
<div id="main-wrapper">
  <!-- Topbar header - style you can find in pages.scss -->
 <header class="topbar" data-navbarbg="skin5">
            <nav class="navbar top-navbar navbar-expand-md navbar-dark">
                <div class="navbar-header" data-logobg="skin5">
                    <!-- This is for the sidebar toggle which is visible on mobile only -->
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    <!-- ============================================================== -->
                    <!-- Logo -->
                    <!-- ============================================================== -->
                    <a class="navbar-brand" href="index.php">
                        <!-- Logo icon -->
                        <b class="logo-icon p-l-10">
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="assets/images/isalu-logo.png" alt="homepage" class="light-logo" />

                        </b>
                        <!--End Logo icon -->
                         <!-- Logo text -->
                        <span class="logo-text">
                        
                            
                     
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <!-- ============================================================== -->
                    
                        <!-- Search -->
                        <!-- ============================================================== -->
                        <li class="nav-item search-box"> <a class="nav-link waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
                            <form class="app-search position-absolute">
                                <input type="text" class="form-control" placeholder="Search &amp; enter"> <a class="srh-btn"><i class="ti-close"></i></a>
                            </form>
                        </li>
                    </ul>
                    <!-- ============================================================== -->
                    <!-- Right side toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-right">
                        <!-- ============================================================== -->
                        <!-- Comment -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="mdi mdi-bell font-24"></i>
                            </a>
                             <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </li>
                     
                        <!-- ============================================================== -->
                     
                   

                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31">
                                <span class="online-indicator" style="color: green; font-size: 12px;">●</span>
                                <span class="username" style="margin-left: 5px;"><?php echo htmlspecialchars($admin_first_name . ' ' . $admin_last_name); ?></span>
                            </a>
                             <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <a class="dropdown-item" href="profile.php"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>

                               
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="change_password.php"><i class="ti-settings m-r-5 m-l-5"></i> Change Password</a>
                                <div class="dropdown-divider"></div>
                    

                                <a href="../admindashboard/logout.php" class="dropdown-item">
                                <i class="fa fa-power-off"></i><span class="hide-menu"> Logout </span>
                                </a>

                                
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                    </ul>
                </div>
            </nav>
        </header>
          <!-- End Topbar header -->

            <?php require "asidebar.php";  ?>

    <!-- ============================================================== -->
        <div class="page-wrapper">

        <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
    <h2>Export Database Backup</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <button type="submit" name="export_db" class="btn btn-primary">
            <i class="mdi mdi-database-plus"></i> Download Database Backup
        </button>
    </form>
    <p class="mt-3 text-muted">Click the button above to export and download the current database as an SQL file.</p>
        </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
    
 <!-- footer -->
            <!-- ============================================================== -->
            <?php require "include/footer.php" ?>
            <!-- ============================================================== -->
            <!-- End footer -->

     </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
</div>
<!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="assets/extra-libs/sparkline/sparkline.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <!-- <script src="dist/js/pages/dashboards/dashboard1.js"></script> -->
    <!-- Charts js Files -->
    <script src="assets/libs/flot/excanvas.js"></script>
    <script src="assets/libs/flot/jquery.flot.js"></script>
    <script src="assets/libs/flot/jquery.flot.pie.js"></script>
    <script src="assets/libs/flot/jquery.flot.time.js"></script>
    <script src="assets/libs/flot/jquery.flot.stack.js"></script>
    <script src="assets/libs/flot/jquery.flot.crosshair.js"></script>
    <script src="assets/libs/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
    <script src="dist/js/pages/chart/chart-page-init.js"></script>
</body>
</html>