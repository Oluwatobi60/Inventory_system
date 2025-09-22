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

// Include configuration
require_once "../admindashboard/include/config.php";
require_once "../include/utils.php";

// Function to calculate predicted maintenance interval based on historical data
function calculateMaintenanceInterval($conn, $assetName, $category) {
    try {
        // Get historical maintenance data
        $sql = "SELECT 
                    m.last_service,
                    m.next_service,
                    COUNT(r.id) as usage_count,
                    AVG(DATEDIFF(m2.last_service, m.last_service)) as avg_interval
                FROM maintenance_table m
                LEFT JOIN request_table r ON r.asset_name = m.asset_name 
                    AND r.request_date BETWEEN m.last_service AND IFNULL(m.next_service, CURDATE())
                LEFT JOIN maintenance_table m2 ON m2.asset_name = m.asset_name 
                    AND m2.last_service > m.last_service
                WHERE m.asset_name = :asset_name AND m.category = :category
                GROUP BY m.id
                ORDER BY m.last_service DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':asset_name', $assetName, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->execute();
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats && $stats['avg_interval'] > 0) {
            $baseInterval = $stats['avg_interval'];
            $usageCount = $stats['usage_count'];
            
            // Adjust interval based on usage frequency
            if ($category === 'Printers') {
                if ($usageCount > 30) {
                    return round($baseInterval * 0.7); // Heavy usage - reduce by 30%
                } elseif ($usageCount > 10) {
                    return round($baseInterval * 0.85); // Medium usage - reduce by 15%
                }
                return round($baseInterval); // Light usage - keep standard interval
            }
            return round($baseInterval); // For non-printer assets
        }
        
        // Default intervals if no history exists
        $defaultIntervals = [
            'Printers' => 60,    // 2 months default
            'AC' => 90,          // 3 months
            'Computers' => 120,   // 4 months
            'Network Equipment' => 180 // 6 months
        ];
        
        // For printers, adjust default interval based on recent usage
        if ($category === 'Printers') {
            // Check recent usage (last 2 months)
            $usageSql = "SELECT COUNT(*) as recent_usage 
                        FROM request_table 
                        WHERE asset_name = :asset_name 
                        AND request_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)";
            $usageStmt = $conn->prepare($usageSql);
            $usageStmt->bindParam(':asset_name', $assetName, PDO::PARAM_STR);
            $usageStmt->execute();
            $usage = $usageStmt->fetch(PDO::FETCH_ASSOC)['recent_usage'];
            
            if ($usage > 30) {
                return 45; // 1.5 months for heavy usage
            } elseif ($usage > 10) {
                return 52; // ~1.75 months for medium usage
            }
        }
        
        return $defaultIntervals[$category] ?? 90; // Default to 90 days if category not found
    } catch (PDOException $e) {
        error_log("Error in calculateMaintenanceInterval: " . $e->getMessage());
        return 90; // Default to 90 days on error
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-request'])) {
    try {
        $assetName = $_POST['asset-name'] ?? '';
        $regNo = $_POST['reg-no'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $department = $_POST['department'] ?? '';
        $last_dates = $_POST['last_dates'] ?? '';

        // Predict next service date
        $next_date = '';
        if (!empty($last_dates) && !empty($category)) {
            $lastServiceDate = new DateTime($last_dates);
            $interval = calculateMaintenanceInterval($conn, $assetName, $category);
            $lastServiceDate->add(new DateInterval("P{$interval}D"));
            $next_date = $lastServiceDate->format('Y-m-d');
        }

        // Validate required fields
        if (empty($assetName) || empty($department) || empty($last_dates) || empty($next_date)) {
            throw new Exception('Please fill in all required fields.');
        }

        // Set default next_service if empty
        if (empty($next_date)) {
            $next_date = date('Y-m-d');
        }

        // Insert into maintenance_table
        $insertSql = "INSERT INTO maintenance_table (reg_no, asset_name, description, category, department, last_service, next_service) 
                      VALUES (:reg_no, :asset_name, :description, :category, :department, :last_service, :next_service)";
        $stmt = $conn->prepare($insertSql);
        
        $stmt->bindParam(':reg_no', $regNo, PDO::PARAM_STR);
        $stmt->bindParam(':asset_name', $assetName, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':category', $category, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':last_service', $last_dates, PDO::PARAM_STR);
        $stmt->bindParam(':next_service', $next_date, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Maintenance schedule created successfully!';
            // Redirect to prevent form resubmission
            header("Location: maintenance.php");
            exit();
        } else {
            throw new Exception('Failed to create maintenance schedule.');
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        error_log("Error in maintenance form submission: " . $e->getMessage());
    }
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
    <link rel="icon" type="image/png" sizes="16x16" href="../admindashboard/assets/images/isalu-logo.png">
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
        .navbar-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-nav {
            gap: 0.7rem;
        }
        .navbar-nav > li > a, .navbar-nav .nav-link {
            font-family: 'Montserrat', Arial, sans-serif;
            font-weight: 600;
            color: #1e293b !important;
            border-radius: 8px;
            padding: 8px 16px;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        }
        .navbar-nav > li > a:hover, .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            background: linear-gradient(90deg, #1e90ff 0%, #00c6ff 100%);
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(30,144,255,0.10);
        }
        .navbar-nav .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(30,144,255,0.10);
        }
        
        /* Styles for suggestions */
        #asset-suggestions {
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            border: 1px solid rgba(0,0,0,.125);
            border-radius: 4px;
            background: white;
            z-index: 1000;
            position: absolute;
            display: none;
        }
        #asset-suggestions .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            cursor: pointer;
        }
        #asset-suggestions .list-group-item:hover {
            background-color: #f8f9fa;
        }
        
        @media (max-width: 600px) {
            .logo-icon img.light-logo {
                width: 40px !important;
                max-height: 40px;
            }
            .navbar-nav > li > a, .navbar-nav .nav-link {
                padding: 6px 10px;
                font-size: 0.95rem;
            }
        }
    </style>
    <title>Facility||Dashboard</title>
    <!-- Custom CSS -->
    <link href="../admindashboard/assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../admindashboard/dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
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
    </div>  --> 
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
                            <img src="../admindashboard/assets/images/isalu-logo.png" alt="homepage" class="light-logo" />
                        </b>
                        <!--End Logo icon -->
                        <!-- Logo text -->
                        
                    </a>
                    <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
                </div>
                <!-- ============================================================== -->
                <!-- End Logo -->
                <!-- ============================================================== -->
                <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">
                    <!-- ============================================================== -->
                    <!-- toggle and nav items -->
                    <!-- ============================================================== -->
                    <ul class="navbar-nav float-left mr-auto d-flex align-items-center" style="gap: 0.7rem;">
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
                    <ul class="navbar-nav float-right d-flex align-items-center" style="gap: 0.7rem;">
                        <!-- ============================================================== -->
                        <!-- Comment -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true, aria-expanded="false"> <i class="mdi mdi-bell font-24"></i>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Action</a>
                                <a class="dropdown-item" href="#">Another action</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">Something else here</a>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="font-24 mdi mdi-comment-processing"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right mailbox animated bounceInDown" aria-labelledby="2">
                                <ul class="list-style-none">
                                    <li>
                                        <div class="">
                                            <!-- Message -->
                                            <a href="javascript:void(0)" class="link border-top">
                                                <div class="d-flex no-block align-items-center p-10">
                                                    <span class="btn btn-success btn-circle"><i class="ti-calendar"></i></span>
                                                    <div class="m-l-10">
                                                        <h5 class="m-b-0">Event today</h5>
                                                        <span class="mail-desc">Just a reminder that event</span>
                                                    </div>
                                                </div>
                                            </a>
                                            <!-- Message -->
                                            <a href="javascript:void(0)" class="link border-top">
                                                <div class="d-flex no-block align-items-center p-10">
                                                    <span class="btn btn-info btn-circle"><i class="ti-settings"></i></span>
                                                    <div class="m-l-10">
                                                        <h5 class="m-b-0">Settings</h5>
                                                        <span class="mail-desc">You can customize this template</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <!-- ============================================================== -->
                        <!-- User profile and search -->
                        <!-- ============================================================== -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="../admindashboard/assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31"></a>
                            <div class="dropdown-menu dropdown-menu-right user-dd animated">
                                <a class="dropdown-item" href="javascript:void(0)"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
                                <a class="dropdown-item" href="javascript:void(0)"><i class="ti-wallet m-r-5 m-l-5"></i> My Balance</a>
                                <a class="dropdown-item" href="javascript:void(0)"><i class="ti-email m-r-5 m-l-5"></i> Inbox</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)"><i class="ti-settings m-r-5 m-l-5"></i> Account Setting</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)"><i class="ti-email m-r-5 m-l-5"></i> Inbox</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)"><i class="ti-settings m-r-5 m-l-5"></i> Account Setting</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)"><i class="fa fa-power-off m-r-5 m-l-5"></i> Logout</a>
                                <div class="dropdown-divider"></div>
                                <div class="p-l-30 p-10"><a href="javascript:void(0)" class="btn btn-sm btn-success btn-rounded">View Profile</a></div>
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
                    <div class="col-12 d-flex no-block align-items-center mt-3">
                        <h4 class="page-title">Schedule Maintenance</h4>
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
                <!-- ============================================================== -->
                <!-- Sales Cards  -->
                
                <!-- ============================================================== -->

                <!-- MODAL SECTION-->
                <div class="row"><!-- Begin of row for modal -->
                    <!-- Column -->
                    <div class="col-md-12 col-lg-6 col-xlg-6"> 
                      <!--   <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo">Schedule Maintenance</button> --> <!-- Button to open the modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"> <!-- Modal structure -->
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Asset Information</h5> <!-- Modal title -->
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <!-- Close button -->
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="tab-content" id="myTabContent"> <!-- Tab content -->
                                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-info-tab">
                                                <form action="" method="POST"> <!-- Form for submitting asset request -->
                                                    <div class="row">
                                                    <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="asset-name" class="col-form-label">Asset Name:</label> <!-- Label for asset name -->
                                                                <input type="text" id="asset-name" class="form-control" name="asset-name" placeholder="Type to search assets" autocomplete="off"> <!-- Input for asset name -->
                                                                <ul id="asset-suggestions" class="list-group"></ul> <!-- Suggestions dropdown -->
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="description" class="col-form-label">Description:</label> <!-- Label for description -->
                                                                <textarea class="form-control" id="description" name="description"></textarea> <!-- Textarea for description -->
                                                            </div>
                                                        </div>
                                                      
                                                       
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="category" class="col-form-label">Category:</label> <!-- Label for category -->
                                                                <input type="text" class="form-control" id="category" name="category" readonly> <!-- Input for category (readonly) -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="department" class="col-form-label">Department:</label> <!-- Label for department -->
                                                                <select id="department" class="form-control" name="department"> <!-- Dropdown for department -->
                                                                    <option selected disabled>Select Department</option>
                                                                    <?php
                                                                    try {
                                                                        $sql = "SELECT department FROM department_table ORDER BY department";
                                                                        $stmt = $conn->query($sql);
                                                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                                            $dept = htmlspecialchars(trim($row['department']));
                                                                            echo "<option value='{$dept}'>{$dept}</option>";
                                                                        }
                                                                    } catch (PDOException $e) {
                                                                        error_log("Error fetching departments: " . $e->getMessage());
                                                                        echo "<option value=''>Error loading departments</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                     
    
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="reg-no" class="col-form-label">Registration Number:</label> <!-- Label for registration number -->
                                                                <input type="text" class="form-control" id="reg-no" name="reg-no" readonly> <!-- Input for registration number (readonly) -->
                                                            </div>
                                                        </div>
    
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="last_dates" class="col-form-label">Last Service Date:</label>
                                                                <input type="date" class="form-control" id="last_dates" name="last_dates" required>
                                                            </div>
                                                        </div>
    
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="next_dates" class="col-form-label">Next Service Date:</label>
                                                                <input type="date" class="form-control" id="next_dates" name="next_date" readonly>
                                                            </div>
                                                        </div>
    
                                                        <div class="col-md-12 text-center">
                                                            <button type="submit" name="submit-request" class="btn btn-primary">Schedule Maintenance</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button> 
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                    </div><!-- End of column -->
                </div><!-- End of row for modal -->
                   <!--END OF MODAL SECTION-->
                <!-- ============================================================== -->
                 

                <!-- START OF ASSET LIST TABLE -->
                <?php require "maintenancefolder/maintable.php";  ?>

                <!-- END OF ASSET LIST TABLE -->
                 
                <!-- Sales chart -->
                <!-- ============================================================== -->
              
                <!-- ============================================================== -->
                <!-- Sales chart -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Recent comment and chats -->
                <!-- ============================================================== -->
              
                <!-- ============================================================== -->
                <!-- Recent comment and chats -->
                <!-- ============================================================== -->
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

    <script>
    // Display success/error messages
    <?php if (isset($_SESSION['success_message'])): ?>
        alert('<?php echo $_SESSION['success_message']; ?>');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        alert('<?php echo $_SESSION['error_message']; ?>');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    const assetNameInput = document.getElementById('asset-name');
    const suggestionsList = document.getElementById('asset-suggestions');
    
    assetNameInput.addEventListener('input', async function() {
        const searchTerm = this.value.trim();
        suggestionsList.innerHTML = '';
        
        if (searchTerm.length > 0) {
            try {
                // Using absolute path to search_asset.php
                const response = await fetch(`maintenancefolder/search_asset.php?q=${encodeURIComponent(searchTerm)}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Response data:', data); // Debug log
                
                if (!data || (Array.isArray(data) && data.length === 0)) {
                    const li = document.createElement('li');
                    li.textContent = 'No assets found';
                    li.className = 'list-group-item';
                    suggestionsList.appendChild(li);
                    suggestionsList.style.display = 'block';
                    return;
                }
                
                if (data.status === 'error') {
                    throw new Error(data.message || 'An error occurred');
                }
                
                suggestionsList.style.display = 'block';
                
                data.forEach(asset => {
                    const li = document.createElement('li');
                    li.textContent = `${asset.asset_name} (${asset.reg_no})`;
                    li.className = 'list-group-item list-group-item-action';
                    
                    li.addEventListener('click', function() {
                        // Set values in the form
                        assetNameInput.value = asset.asset_name || '';
                        document.getElementById('reg-no').value = asset.reg_no || '';
                        document.getElementById('category').value = asset.category || '';
                        document.getElementById('description').value = asset.description || '';
                        
                        // Hide suggestions
                        suggestionsList.style.display = 'none';
                    });
                    
                    suggestionsList.appendChild(li);
                });
                
            } catch (error) {
                console.error('Error fetching assets:', error);
                suggestionsList.innerHTML = '';
                const li = document.createElement('li');
                li.textContent = 'Error loading suggestions';
                li.className = 'list-group-item text-danger';
                suggestionsList.appendChild(li);
                suggestionsList.style.display = 'block';
            }
        } else {
            suggestionsList.style.display = 'none';
        }
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!assetNameInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.style.display = 'none';
        }
    });

    // Add an event listener to the last service date input field
    document.getElementById('last_dates').addEventListener('change', function() {
        const lastServiceDate = new Date(this.value); // Parse the selected last service date
        const category = document.getElementById('category').value; // Get the selected category
        const nextServiceInput = document.getElementById('next_dates'); // Get the next servicing date input field

        if (!isNaN(lastServiceDate.getTime())) { // Check if the date is valid
            switch (category) { // Determine the next service date based on the category
                case 'Printers':
                    lastServiceDate.setMonth(lastServiceDate.getMonth() + 2); // Add 2 months for printers
                    break;
                case 'AC':
                    lastServiceDate.setMonth(lastServiceDate.getMonth() + 3); // Add 3 months for ACs
                    break;
                case 'Laptops':
                    lastServiceDate.setMonth(lastServiceDate.getMonth() + 4); // Add 4 months for laptops
                    break;
                default:
                    nextServiceInput.value = ''; // Clear the next servicing date if the category is unrecognized
                    alert('Unrecognized category. Please select a valid category.'); // Show an alert for unrecognized category
                    return;
            }
            nextServiceInput.value = lastServiceDate.toISOString().split('T')[0]; // Set the next servicing date in the input field
        } else {
            nextServiceInput.value = ''; // Clear the next servicing date if the input is invalid
            alert('Invalid last service date. Please select a valid date.'); // Show an alert for invalid date
        }
    });

    // Ensure the next servicing date is updated when the form is reloaded
    window.addEventListener('load', function() {
        const lastServiceDateInput = document.getElementById('last_dates'); // Get the last service date input field
        const categoryInput = document.getElementById('category'); // Get the category input field
        const nextServiceInput = document.getElementById('next_dates'); // Get the next servicing date input field

        if (lastServiceDateInput.value && categoryInput.value) { // Check if both last service date and category are set
            const lastServiceDate = new Date(lastServiceDateInput.value); // Parse the last service date
            if (!isNaN(lastServiceDate.getTime())) { // Check if the date is valid
                switch (categoryInput.value) { // Determine the next service date based on the category
                    case 'Printers':
                        lastServiceDate.setMonth(lastServiceDate.getMonth() + 2); // Add 2 months for printers
                        break;
                    case 'AC':
                        lastServiceDate.setMonth(lastServiceDate.getMonth() + 3); // Add 3 months for ACs
                        break;
                    default:
                        nextServiceInput.value = ''; // Clear the next servicing date if the category is unrecognized
                        return;
                }
                nextServiceInput.value = lastServiceDate.toISOString().split('T')[0]; // Set the next servicing date in the input field
            }
        }
    });
    </script>
</body>

</html>