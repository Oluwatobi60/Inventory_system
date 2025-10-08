<?php  
session_start(); // Start the session to manage user sessions

require_once "../admindashboard/include/config.php"; // <-- Add this line to include DB connection

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


 // Fetch total "Total withdrawals" based on quantity
    $query = "SELECT SUM(quantity) AS total_withdrawals FROM repair_asset WHERE withdrawn = 1";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_withdrawals = $row['total_withdrawals'] ?? 0; // Default to 0 if no recordpproved = $row['total_hod_not_approved'] ?? 0; // Default to 0 if no record

    // Fetch total "Total Replaced Assets" based on quantity
    $query = "SELECT SUM(quantity) AS total_replaced_assets FROM repair_asset WHERE replaced = 1 ";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_replaced_assets = $row['total_replaced_assets'] ?? 0; // Default to 0 if no record

 // Fetch total "Total completed repair assets"
    $query = "SELECT SUM(quantity) AS total_completed_repair_assets FROM repair_asset WHERE completed = 1";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_completed_repair_assets = $row['total_completed_repair_assets'] ?? 0; // Default to 0 if no record

      // Fetch total "Assets Under Repair" requests
    $query = "SELECT SUM(quantity) AS total_assets_under_repair FROM repair_asset WHERE status = 'under repair'";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_assets_under_repair = $row['total_assets_under_repair'] ?? 0; // Default to 0 if no record

    
    // Fetch total number of all assets
    $query = "SELECT COUNT(*) AS total_assets FROM asset_table";
    $stmt = $conn->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_assets = $row['total_assets'] ?? 0; // Default to 0 if no record

// Get the department and name of the logged-in HOD
$hod_username = $_SESSION['username'];
$dept_query = "SELECT department, firstname, lastname FROM user_table WHERE username = :username";
$stmt = $conn->prepare($dept_query);
$stmt->bindParam(':username', $hod_username, PDO::PARAM_STR);
$stmt->execute();
$dept_row = $stmt->fetch(PDO::FETCH_ASSOC);
$hod_department = $dept_row['department'];
$hod_first_name = $dept_row['firstname'];
$hod_last_name = $dept_row['lastname']; 

    
    } catch (PDOException $e) {
     // Log error and set default values
    error_log("Database error in prodashboard.php: " . $e->getMessage());
    $total_withdrawals = 0;
    $total_assets_under_repair = 0;
    $total_assets = 0;
    $total_replaced_assets = 0;
    $total_completed_repair_assets = 0;
    $pro_first_name = 'User';
    $pro_last_name = '';
    $hod_first_name ='';
    $hod_last_name = '';
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
    <title>Audit || Dashboard</title>
    <!-- Custom CSS -->
    <link href="../admindashboard/assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../admindashboard/dist/css/style.min.css" rel="stylesheet">

    <style>
        .stat-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-content {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .stat-icon {
            font-size: 40px;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        .stat-details {
            flex-grow: 1;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-top: 30px;
            height: 400px; /* Added fixed height */
            position: relative; /* Added for proper chart sizing */
        }

        .chart-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4e73df;
            display: inline-block;
        }

        .dashboard-header {
            margin-bottom: 40px;
        }

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
 
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
  <!--   <div class="preloader">
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
                    <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                    <a class="navbar-brand" href="index.php">
                        <b class="logo-icon p-l-10">
                            <img src="../admindashboard/assets/images/isalu-logo.png" alt="homepage" class="light-logo" width="100px"/>
                        </b>
                    </a>
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <ul class="navbar-nav float-left mr-auto">
                        <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
                        <li class="nav-item search-box"> <a class="nav-link waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
                            <form class="app-search position-absolute">
                                <input type="text" class="form-control" placeholder="Search &amp; enter"> <a class="srh-btn"><i class="ti-close"></i></a>
                            </form>
                        </li>
                    </ul>
                    <ul class="navbar-nav float-right">
                      <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="../admindashboard/assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31"> 
                             <span class="online-indicator" style="color: green; font-size: 12px;">●</span>
                            <?php echo htmlspecialchars($pro_first_name . ' ' . $pro_last_name); ?></a> 
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
           
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <div class="dashboard-header">
                    <h1 class="page-title">HOD Dashboard</h1>
                    <div class="welcome-message" style="color: #666; font-size: 1.1em; margin-top: -15px; margin-bottom: 25px;">
                        Welcome, <span style="color: #4e73df; font-weight: 600;"><?php echo htmlspecialchars($hod_first_name . ' ' . $hod_last_name); ?></span>!
                    </div>
                </div>

                 <div class="row">
                    <div class="col-md-6 col-lg-4">
                        <div class="stat-card">
                            <div class="stat-content bg-danger">
                                <div class="stat-icon bg-danger-light">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value text-white"><?php echo $total_assets_under_repair; ?></div>
                                    <div class="stat-label">Total Assets Under Repair</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="stat-card">
                            <div class="stat-content bg-success">
                                <div class="stat-icon bg-success-light">
                                    <i class="fas fa-calendar-day text-white"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value text-white"><?php echo $total_completed_repair_assets; ?></div>
                                    <div class="stat-label">Total Completed Repairs</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="stat-card">
                            <div class="stat-content bg-info">
                                <div class="stat-icon bg-info-light">
                                    <i class="fas fa-calendar-alt text-white"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value text-white"><?php echo $total_replaced_assets; ?></div>
                                    <div class="stat-label">Total Replaced Assets</div>
                                </div>
                            </div>
                        </div>
                    </div>

               

                    <div class="col-md-6 col-lg-6">
                        <div class="stat-card">
                            <div class="stat-content bg-warning">
                                <div class="stat-icon bg-warning-light">
                                    <i class="fas fa-exclamation-circle text-white"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value text-white"><?php echo $total_withdrawals; ?></div>
                                    <div class="stat-label">Total Withdrawals</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-6">
                        <div class="stat-card">
                            <div class="stat-content bg-purple" style="background-color: #6f42c1;">
                                <div class="stat-icon bg-purple-light">
                                    <i class="fas fa-boxes text-white"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value text-white"><?php echo $total_assets; ?></div>
                                    <div class="stat-label">Total Assets</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->

                <!-- ============================================================== -->
                <!-- Bar Chart Section -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-12">
                        <div class="chart-container">
                            <h2 class="chart-title">Asset Report Overview</h2>
                            <canvas id="assetBarChart"></canvas>
                        </div>
                    </div>
                </div>
            
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
           <?php require "../admindashboard/include/footer.php" ?>
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
    <script src="../admindashboard/assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../admindashboard/assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../admindashboard/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../admindashboard/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../admindashboard/assets/extra-libs/sparkline/sparkline.js"></script>
    <!--Wave Effects -->
    <script src="../admindashboard/dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="../admindashboard/dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="../admindashboard/dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <!-- <script src="dist/js/pages/dashboards/dashboard1.js"></script> -->
    <!-- Charts js Files -->
    <script src="../admindashboard/assets/libs/flot/excanvas.js"></script>
    <script src="../admindashboard/assets/libs/flot/jquery.flot.js"></script>
    <script src="../admindashboard/assets/libs/flot/jquery.flot.pie.js"></script>
    <script src="../admindashboard/assets/libs/flot/jquery.flot.time.js"></script>
    <script src="../admindashboard/assets/libs/flot/jquery.flot.stack.js"></script>
    <script src="../admindashboard/assets/libs/flot/jquery.flot.crosshair.js"></script>
    <script src="../admindashboard/assets/libs/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
    <script src="../admindashboard/dist/js/pages/chart/chart-page-init.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('assetBarChart').getContext('2d');
        var assetBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total Assets Under Repair', 'Total Completed Repairs', 'Total Replaced Assets', 'Total Withdrawals'],
                datasets: [{
                    label: 'Requests',
                    data: [
                        <?php echo $total_assets_under_repair; ?>,
                        <?php echo $total_completed_repair_assets; ?>,
                        <?php echo $total_replaced_assets; ?>,
                        <?php echo $total_withdrawals; ?>,
                       
                    ],
                    backgroundColor: [
                        '#36b9cc',  // Info color
                        '#1cc88a',  // Success color
                        '#4e73df',  // Primary color
                        '#f6c23e',  // Warning color
                        '#e74a3b'   // Danger color
                    ],
                    borderColor: [
                        '#2c9faf',
                        '#169b6b',
                        '#2e59d9',
                        '#dda20a',
                        '#be2617'
                    ],
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 50
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 20,
                        right: 20,
                        bottom: 20,
                        left: 20
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return context.raw + ' Requests';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4],
                            color: '#e0e0e0'
                        },
                        ticks: {
                            beginAtZero: true,
                            precision: 0,
                            stepSize: Math.ceil(Math.max(
                                <?php echo $total_assets_under_repair; ?>,
                                <?php echo $total_completed_repair_assets; ?>,
                                <?php echo $total_replaced_assets; ?>,
                                <?php echo $total_withdrawals; ?>,
                            ) / 5),
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    </script>
</body>

</html>