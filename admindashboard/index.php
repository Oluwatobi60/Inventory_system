<?php  
session_start(); // Start the session to manage user sessions

// Set session timeout to 20 minutes
$timeout_duration = 1200; // 20 minutes in seconds

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // If the session has been inactive for too long, destroy it
    session_unset();
    session_destroy();
    header("Location: ../index.php"); // Redirect to login page
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity timestamp

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php"); // Redirect to login page if not logged in
    exit();
}

// Database connection
require_once "../include/config.php";
require_once "../include/utils.php";

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
    $admin_last_name = "User";
}

// Section: Fetch Asset Quantities
try {
    // Prepare query to get total quantity for each asset category
    $query = "SELECT SUM(quantity) AS total_quantity FROM asset_table WHERE category = ?";
    // Create a prepared statement for reuse with different categories
    $stmt = $conn->prepare($query);

    // Get total quantity of printers
    // Execute the query specifically for 'Printers' category
    $stmt->execute(['Printers']);
    // Fetch the result row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Store the quantity, use 0 if NULL using null coalescing operator
    $total_printer_quantity = $row['total_quantity'] ?? 0;

    // Get furniture quantity
    $stmt->execute(['Furniture']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_furniture_quantity = $row['total_quantity'] ?? 0;

    // Get laptops quantity
    $stmt->execute(['Laptops']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_laptop_quantity = $row['total_quantity'] ?? 0;

    // Get accessories quantity
    $stmt->execute(['Accessories']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_accessories_quantity = $row['total_quantity'] ?? 0;

    // Get Desktop quantity
    $stmt->execute(['Desktops']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_desktop_quantity = $row['total_quantity'] ?? 0;
} catch (PDOException $e) {
    logError("Failed to fetch asset quantities: " . $e->getMessage());
    // Set default values if query fails
    $total_printer_quantity = 0;
    $total_furniture_quantity = 0;
    $total_laptop_quantity = 0;
    $total_accessories_quantity = 0;
    $total_desktop_quantity = 0;
}

// Section: Fetch Request Statistics
try {
    // Fetch total number of assets
    $query = "SELECT COUNT(*) AS total_asset FROM asset_table";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_asset = $row ? $row['total_asset'] : 0;

    // Fetch number of assets added today
    $query = "SELECT COUNT(*) AS new_added_asset FROM asset_table WHERE DATE(dateofpurchase) = CURDATE()";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $new_added_asset = $row ? $row['new_added_asset'] : 0;

    // Fetch total number of users
    $query = "SELECT COUNT(*) AS total_users FROM user_table";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_users = $row ? $row['total_users'] : 0;

    // Fetch total staff allocated asset (from staff_allocation table, counting unique staff_id)
    $query = "SELECT COUNT(DISTINCT id) AS staff_allocated FROM staff_table";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $staff_allocated = $row ? $row['staff_allocated'] : 0;

    // Fetch total staff allocated asset (from maintenance_table, counting unique staff_id)
    $query = "SELECT COUNT(DISTINCT id) AS maintenance_report FROM maintenance_table";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $maintenance_report = $row ? $row['maintenance_report'] : 0;
} catch (PDOException $e) {
    // TEMPORARY DEBUGGING: Show the error on the screen
    die("Database Error: " . $e->getMessage());
    
    // Keep your original logging for later
    // logError("Failed to fetch request statistics: " . $e->getMessage());
    $total_asset = 0;
    $new_added_asset = 0;
    $total_users = 0;
    $staff_allocated = 0;
    $maintenance_report = 0;
}

// Section: Fetch Repair Status Statistics
try {
    $status_counts = [
        'Under Repair' => 0,
        'Completed' => 0,
        'Withdrawn' => 0,
        'Replaced' => 0
    ];
    $query = "SELECT status, COUNT(*) AS count FROM repair_asset WHERE status IN ('Under Repair', 'Completed', 'Withdrawn', 'Replaced') GROUP BY status";
    $stmt = $conn->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['status'];
        $count = (int)$row['count'];
        if (isset($status_counts[$status])) {
            $status_counts[$status] = $count;
        }
    }
} catch (PDOException $e) {
    $status_counts = [
        'Under Repair' => 0,
        'Completed' => 0,
        'Withdrawn' => 0,
        'Replaced' => 0
    ];
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/isalu-logo.png">
    <title>Admin||Dashboard</title>
    <!-- Custom CSS -->
    <link href="assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
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
        .page-title {
            font-family: 'Montserrat', Arial, sans-serif;
            font-weight: 900;
            font-size: 2.2rem;
            color: #1e90ff;
            letter-spacing: 1px;
            text-shadow: 0 2px 8px rgba(30,144,255,0.08), 0 1px 0 #fff;
            background: rgba(255,255,255,0.7);
            border-radius: 8px;
            padding: 0.3em 1em;
            display: inline-block;
            margin-top: 0.5em;
        }
        @media (max-width: 600px) {
            .page-title {
                font-size: 1.3rem;
                padding: 0.2em 0.7em;
            }
            .logo-icon img.light-logo {
                width: 48px !important;
                max-height: 48px;
            }
        }
        .request-dist-card.glass-effect {
    background: rgba(255,255,255,0.25);
    box-shadow: 0 8px 32px rgba(30,144,255,0.13), 0 1.5px 6px rgba(0,0,0,0.09);
    border-radius: 18px;
    backdrop-filter: blur(7px);
    border: 1.5px solid rgba(30,144,255,0.13);
    transition: box-shadow 0.3s, transform 0.3s;
    opacity: 0;
    transform: translateY(40px) scale(0.98);
    animation: fadeInUp 1.2s 0.2s forwards;
}
.request-dist-card.glass-effect:hover {
    box-shadow: 0 16px 48px rgba(30,144,255,0.18), 0 3px 12px rgba(0,0,0,0.13);
    transform: translateY(-8px) scale(1.04) rotate(-1deg);
    z-index: 2;
}
.glass-header {
    background: rgba(255,255,255,0.55)!important;
    border-radius: 18px 18px 0 0;
    border-bottom: 1px solid rgba(30,144,255,0.08);
}
.animated-fade-in {
    opacity: 0;
    animation: fadeInUp 1.2s 0.2s forwards;
}
.animated-chart {
    opacity: 0;
    animation: fadeInUp 1.4s 0.4s forwards;
}
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
    </style>
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
   <!--  <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div> -->
    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
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
        <!-- ============================================================== -->
        <!-- End Topbar header -->
      

        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php require "asidebar.php";  ?>
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
             <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center">
                        <h4 class="page-title">Admin Dashboard</h4>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- Main Stats Overview -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-gradient-primary text-white shadow-lg rounded-lg">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h2 class="mb-3">Welcome back, <?php echo htmlspecialchars($admin_first_name . ' ' . $admin_last_name); ?>!</h2>
                                        <p class="mb-0">Your asset management dashboard overview</p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <i class="mdi mdi-view-dashboard" style="font-size: 4rem;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asset Categories Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h4 class="text-muted mb-4">Asset Categories</h4>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="card shadow-lg rounded-lg h-100 border-left-primary">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Printers</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_printer_quantity; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-printer fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <div class="card shadow-lg rounded-lg h-100 border-left-success">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Furniture</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_furniture_quantity; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-sofa fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <div class="card shadow-lg rounded-lg h-100 border-left-warning">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Laptops</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_laptop_quantity; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-laptop fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <div class="card shadow-lg rounded-lg h-100 border-left-danger">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Accessories</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_accessories_quantity; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-headphones fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <div class="card shadow-lg rounded-lg h-100 border-left-info">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Desktops</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_desktop_quantity; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-air-conditioner fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="row mb-4 dashboard-stats-row">
                    <div class="col-12">
                        <h4 class="text-muted mb-4">Statistics</h4>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card shadow-lg rounded-lg h-100 bg-gradient-primary text-white animated-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Users</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $total_users; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-account-multiple fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card shadow-lg rounded-lg h-100 bg-gradient-success text-white animated-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Staff Allocated Asset</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $staff_allocated; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-account-tie fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card shadow-lg rounded-lg h-100 bg-gradient-warning text-white animated-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">Maintenance Report</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $maintenance_report; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-wrench fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stat-card shadow-lg rounded-lg h-100 bg-gradient-secondary text-white animated-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1">New Added Assets</div>
                                        <div class="h5 mb-0 font-weight-bold"><?php echo $new_added_asset; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="mdi mdi-plus-box fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Charts Section -->
                <div class="row">
                    <div class="col-xl-4 col-lg-4">
                        <div class="card shadow-lg rounded-lg mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Asset Overview</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="assetBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4">
                        <div class="card shadow-lg rounded-lg mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Statistics Overview</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-pie pt-4 pb-2" style="height:350px;">
                                    <canvas id="statisticsPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

 <!-- Repair Status Chart Section -->
                      <div class="col-xl-4 col-lg-4">
                        <div class="card shadow-lg rounded-lg mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-light">
                                <h6 class="m-0 font-weight-bold text-primary">Repair Status Report</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="repairStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

             
            </div>

            <!-- footer -->
            <!-- ============================================================== -->
           <?php require "include/footer.php" ?>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Get the canvas context for the asset bar chart
    var ctx = document.getElementById('assetBarChart').getContext('2d');
    
    // Initialize the bar chart with configuration
    var assetBarChart = new Chart(ctx, {
        // Set chart type to bar
        type: 'bar',
        // Define the data structure
        data: {
            // Define labels for each asset category
            labels: ['Printers', 'Furniture', 'Laptops', 'Accessories', 'Desktops'],
            datasets: [{
                label: 'Asset Quantities',
                data: [
                    <?php echo $total_printer_quantity; ?>, 
                    <?php echo $total_furniture_quantity; ?>, 
                    <?php echo $total_laptop_quantity; ?>, 
                    <?php echo $total_accessories_quantity; ?>, 
                    <?php echo $total_desktop_quantity; ?>
                ],
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(54, 185, 204, 0.8)'
                ],
                borderColor: [
                    'rgb(78, 115, 223)',
                    'rgb(28, 200, 138)',
                    'rgb(246, 194, 62)',
                    'rgb(231, 74, 59)',
                    'rgb(54, 185, 204)'
                ],
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Pie Chart for Statistics
var pieCtx = document.getElementById('statisticsPieChart').getContext('2d');
var statisticsPieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: ['Total Users', 'Staff Allocated Asset', 'Maintenance Report', 'New Added Assets'],
        datasets: [{
            data: [
                <?php echo $total_users; ?>,
                <?php echo $staff_allocated; ?>,
                <?php echo $maintenance_report; ?>,
                <?php echo $new_added_asset; ?>
            ],
            backgroundColor: [
                'rgba(78, 115, 223, 0.8)',
                'rgba(28, 200, 138, 0.8)',
                'rgba(246, 194, 62, 0.8)',
                'rgba(231, 74, 59, 0.8)'
            ],
            borderColor: [
                'rgb(78, 115, 223)',
                'rgb(28, 200, 138)',
                'rgb(246, 194, 62)',
                'rgb(231, 74, 59)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Repair Status Bar Chart
var repairStatusCtx = document.getElementById('repairStatusChart').getContext('2d');
var repairStatusChart = new Chart(repairStatusCtx, {
    type: 'bar',
    data: {
        labels: ['Under Repair', 'Completed', 'Withdrawn', 'Replaced'],
        datasets: [{
            label: 'Asset Count',
            data: [
                <?php echo $status_counts['Under Repair']; ?>,
                <?php echo $status_counts['Completed']; ?>,
                <?php echo $status_counts['Withdrawn']; ?>,
                <?php echo $status_counts['Replaced']; ?>
            ],
            backgroundColor: [
                'rgba(78, 115, 223, 0.8)',
                'rgba(28, 200, 138, 0.8)',
                'rgba(231, 74, 59, 0.8)',
                'rgba(54, 185, 204, 0.8)'
            ],
            borderColor: [
                'rgb(78, 115, 223)',
                'rgb(28, 200, 138)',
                'rgb(231, 74, 59)',
                'rgb(54, 185, 204)'
            ],
            borderWidth: 2,
            borderRadius: 5
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false,
                    color: 'rgba(0, 0, 0, 0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
    </script>

<style>
.bg-gradient-primary {
    background: linear-gradient(45deg, #4e73df 10%, #224abe 100%);
}

.bg-gradient-success {
    background: linear-gradient(45deg, #1cc88a 10%, #13855c 100%);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #f6c23e 10%, #dda20a 100%);
}

.bg-gradient-danger {
    background: linear-gradient(45deg, #e74a3b 10%, #be2617 100%);
}

.bg-gradient-secondary {
    background: linear-gradient(45deg, #858796 10%, #60616f 100%);
}

.border-left-primary {
    border-left: 4px solid #4e73df;
}

.border-left-success {
    border-left: 4px solid #1cc88a;
}

.border-left-warning {
    border-left: 4px solid #f6c23e;
}

.border-left-danger {
    border-left: 4px solid #e74a3b;
}

.border-left-info {
    border-left: 4px solid #36b9cc;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.chart-area {
    position: relative;
    height: 350px;
    width: 100%;
}

.chart-pie {
    position: relative;
    height: 350px;
    width: 100%;
}
</style>
</body>

</html>