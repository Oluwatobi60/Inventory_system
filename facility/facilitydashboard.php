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
require_once "../admindashboard/include/config.php";

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
    $total_withdrawals = $row['total_withdrawals'] ?? 0; // Default to 0 if no record


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
}
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../admindashboard/assets/images/isalu-logo.png">
    <title>Facility||Dashboard</title>
    <link href="../admindashboard/assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <link href="../admindashboard/dist/css/style.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
    <div id="main-wrapper">
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
                            <img src="../admindashboard/assets/images/isalu-logo.png" alt="homepage" class="light-logo" />

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
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="../admindashboard/assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31"> <?php echo htmlspecialchars($pro_first_name . ' ' . $pro_last_name); ?></a> 
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

        <?php require "asidebar.php"; ?>

        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="dashboard-header">
                    <h1 class="page-title">Facility Dashboard</h1>
                    <div class="welcome-message" style="color: #666; font-size: 1.1em; margin-top: -15px; margin-bottom: 25px;">
                        Welcome, <span style="color: #4e73df; font-weight: 600;"><?php echo htmlspecialchars($pro_first_name . ' ' . $pro_last_name); ?></span>!
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-6">
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

                    <div class="col-md-6 col-lg-6">
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

                    <div class="col-md-6 col-lg-6">
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

                 <!--    <div class="col-md-6 col-lg-6">
                        <div class="stat-card">
                            <div class="stat-content bg-purple" style="background-color: #6f42c1;">
                                <div class="stat-icon bg-purple-light">
                                    <i class="fas fa-boxes text-white"></i>
                                </div>
                                <div class="stat-details">
                                    <div class="stat-value text-white"><?php //echo $total_assets; ?></div>
                                    <div class="stat-label">Total Assets</div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="chart-container">
                            <h2 class="chart-title">Asset Report Overview</h2>
                            <canvas id="assetBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <?php require "../admindashboard/include/footer.php" ?>
        </div>
    </div>

    <script src="../admindashboard/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="../admindashboard/assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../admindashboard/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../admindashboard/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../admindashboard/dist/js/waves.js"></script>
    <script src="../admindashboard/dist/js/sidebarmenu.js"></script>
    <script src="../admindashboard/dist/js/custom.min.js"></script>
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