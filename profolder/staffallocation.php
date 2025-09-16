<?php  
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Handle AJAX request for asset suggestions and details
if (isset($_GET['q'])) {
    header('Content-Type: application/json');
    $searchTerm = $_GET['q'];
    error_log("Search Term Received: $searchTerm");
    
    $sql = "SELECT asset_name, reg_no, category, description, CAST(quantity AS SIGNED) as quantity 
            FROM asset_table 
            WHERE LOWER(asset_name) LIKE LOWER(:search)
               OR LOWER(reg_no) LIKE LOWER(:search)
               OR LOWER(category) LIKE LOWER(:search)
            ORDER BY 
                CASE WHEN LOWER(asset_name) = LOWER(:exact) THEN 1
                     WHEN LOWER(reg_no) = LOWER(:exact) THEN 2
                     ELSE 3 
                END,
                asset_name 
            LIMIT 10";
    
    try {
        $stmt = $conn->prepare($sql);
        $searchPattern = "%" . $searchTerm . "%";
        $stmt->bindParam(':search', $searchPattern, PDO::PARAM_STR);
        $stmt->bindParam(':exact', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure quantity is treated as a number
        foreach ($assets as &$asset) {
            $asset['quantity'] = intval($asset['quantity']);
        }
        
        error_log("Found assets: " . json_encode($assets));
        echo json_encode($assets);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(array('error' => 'Database error'));
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-request'])) {
    $assetNames = isset($_POST['asset-name']) ? $_POST['asset-name'] : [];
    $regNos = isset($_POST['reg-no']) ? $_POST['reg-no'] : [];
    $descriptions = isset($_POST['description']) ? $_POST['description'] : [];
    $qtys = isset($_POST['qty']) ? $_POST['qty'] : [];
    $categories = isset($_POST['category']) ? $_POST['category'] : [];
    $department = isset($_POST['department']) ? $_POST['department'] : '';
    $floor = isset($_POST['floor']) ? $_POST['floor'] : '';
    $requestedBy = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $date = isset($_POST['dates']) ? $_POST['dates'] : '';

    // Basic validation
    if (empty($department) || empty($floor) || empty($date) || empty($assetNames)) {
        echo "<script>alert('Please fill in all required fields and add at least one asset.');</script>";
        exit;
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Process each asset
        for ($i = 0; $i < count($assetNames); $i++) {
            $assetName = $assetNames[$i];
            $regNo = $regNos[$i];
            $description = $descriptions[$i];
            $qty = intval($qtys[$i]);
            $category = $categories[$i];

            if (empty($assetName) || empty($qty)) {
                throw new Exception("Missing required fields for asset #" . ($i + 1));
            }

            // Check available quantity with FOR UPDATE lock
            $sql = "SELECT quantity FROM asset_table WHERE asset_name = :asset FOR UPDATE";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':asset', $assetName, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception("Asset '$assetName' not found");
            }

            $availableQty = $result['quantity'];
            if ($availableQty < $qty) {
                throw new Exception("Insufficient quantity for '$assetName'. Available: $availableQty, Requested: $qty");
            }
            
            // Insert request with timestamp
            $sql = "INSERT INTO staff_table (reg_no, asset_name, description, quantity, category, department, floor, requested_by, request_date) 
                    VALUES (:reg_no, :asset_name, :description, :quantity, :category, :department, :floor, :requested_by, :date)";
            $stmt = $conn->prepare($sql);
            
            // Convert date to datetime format
            $datetime = date('Y-m-d H:i:s', strtotime($date));
            
            $stmt->bindParam(':reg_no', $regNo, PDO::PARAM_STR);
            $stmt->bindParam(':asset_name', $assetName, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':quantity', $qty, PDO::PARAM_INT);
            $stmt->bindParam(':category', $category, PDO::PARAM_STR);
            $stmt->bindParam(':department', $department, PDO::PARAM_STR);
            $stmt->bindParam(':floor', $floor, PDO::PARAM_STR);
            $stmt->bindParam(':requested_by', $requestedBy, PDO::PARAM_STR);
            $stmt->bindParam(':date', $datetime, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert request for $assetName");
            }

            // Update asset quantity
            $sql = "UPDATE asset_table SET quantity = quantity - :qty WHERE asset_name = :asset_name";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':qty', $qty, PDO::PARAM_INT);
            $stmt->bindParam(':asset_name', $assetName, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update quantity for $assetName");
            }
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = 'All assets allocated successfully!';
        echo '<script>window.location.href = "staffallocation.php";</script>';
        exit;
        
    } catch (Exception $e) {
        // Roll back the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

if (isset($_SESSION['success_message'])) {
    echo '<script>alert("' . addslashes($_SESSION['success_message']) . '");</script>';
    unset($_SESSION['success_message']);
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
        
        /* Styles for suggestions */
        .asset-suggestions {
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
        .asset-suggestions .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            cursor: pointer;
        }
        .asset-suggestions .list-group-item.disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        .asset-quantity {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 8px;
        }
        .quantity-available {
            background-color: #d4edda;
            color: #155724;
        }
        .quantity-empty {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        /* Custom styling for the modal */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        .nav-tabs .nav-link.active {
            color: #1e88e5;
            font-weight: 600;
        }
    </style>
    <title>Admin||Dashboard</title>
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
  <!--  <div class="preloader">
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
                        <span class="logo-text">
                            <!--You can put here text as well // <i class="wi wi-sunset"></i> //-->
                        </span>
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
          <!--    <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center mt-3">
                        <h4 class="page-title">Staff Allocation</h4>
                    </div>
                </div>
            </div> -->
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid px-0">
                <!-- ============================================================== -->
                <!-- Sales Cards  -->
                
                <!-- ============================================================== -->

                <!-- MODAL SECTION-->
                <div class="row"><!-- Begin of row for modal -->
                    <!-- Column -->
                    <div class="col-md-12 col-lg-6 col-xlg-6"> 
                        <div class=" m-4">
                            <h3 class="text-primary">Staff Asset Allocation</h3> <!-- Title for the modal -->
                            <p class="text-muted">Allocate assets to staff members</p> <!-- Subtitle for the modal -->
                        <button type="button" class="btn btn-primary " data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo">Staff Asset</button> <!-- Button to open the modal -->
                        </div>
                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"> 
                            <!-- Modal structure -->
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Asset Information</h5> <!-- Modal title -->
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <!-- Close button -->
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="nav nav-tabs" id="myTab" role="tablist"> <!-- Tab navigation -->
                                            <li class="nav-item">
                                                <a class="nav-link active" id="basic-info-tab" data-toggle="tab" href="#basic-info" role="tab" aria-controls="basic-info" aria-selected="true">Basic Info</a> <!-- Tab for basic info -->
                                            </li>
                                        </ul>
                                        <div class="tab-content" id="myTabContent"> <!-- Tab content -->
                                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-info-tab">
                                                <form action="" method="POST"> <!-- Form for submitting asset request -->
                                            <div id="assets-container">
                                                <div class="asset-entry mb-4">
                                                    <div class="row"><!--first row-->
                                                         <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="asset-name-0" class="col-form-label">Asset Name:</label>
                                                                        <input type="text" id="asset-name-0" class="form-control asset-name" name="asset-name[]" placeholder="Type to search assets" autocomplete="off">
                                                                        <ul class="asset-suggestions list-group"></ul>
                                                                    </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="description" class="col-form-label">Description:</label> <!-- Label for description -->
                                                                <textarea class="form-control" id="description-0" name="description[]" readonly></textarea> <!-- Textarea for description -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">                                                <label for="unit-0" class="col-form-label">Asset Quantity:</label> <!-- Label for quantity -->
                                                                <input type="number" class="form-control quantity" id="unit-0" name="qty[]" min="1" placeholder="Enter quantity" disabled> <!-- Input for quantity -->
                                                            </div>
                                                        </div>
                                                       
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="category-0" class="col-form-label">Category:</label> <!-- Label for category -->
                                                                <input type="text" class="form-control categor" id="category-0" name="category[]" readonly> <!-- Input for category (readonly) -->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="reg-no-0" class="col-form-label">Registration Number:</label>
                                                                        <input type="text" class="form-control reg-no" id="reg-no-0" name="reg-no[]" readonly>
                                                                    </div>
                                                        </div>
                                                         <div class="col-12">
                                                                    <button type="button" class="btn btn-danger btn-sm remove-asset" style="display: none;">Remove Asset</button>
                                                        </div>
                                                    </div><!-- end of first row-->
                                                </div><!-- End of first asset entry -->
                                            </div><!-- End of assets container -->

                                                     <div class="row mt-3">
                                                        <div class="col-md-12 mb-3">
                                                            <button type="button" class="btn btn-info" id="add-asset-btn">Add Another Asset</button>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="department-select" class="col-form-label">Department:</label> <!-- Label for department -->
                                                                <select id="department-select" class="form-control" name="department"> <!-- Dropdown for department -->
                                                                    <option selected disabled>Select Department</option> 
                                                                    <?php                                     
                                                                    $sql = "SELECT department FROM department_table ORDER BY department";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute();
                                                                    while($row = $stmt->fetch()){
                                                                        $dept = trim($row['department']);
                                                                        echo "<option value='".htmlspecialchars($dept)."'>".htmlspecialchars($dept)."</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="floor-select" class="col-form-label">Floor:</label> <!-- Label for assigned floor -->
                                                                <select id="floor-select" class="form-control" name="floor"> <!-- Dropdown for assigned floor -->
                                                                    <option selected disabled>Select Floor</option>                                
                                                                <?php                                       
                                                                $sql_floor = "SELECT floor FROM department_table";
                                                                    $stmt_floor = $conn->prepare($sql_floor);
                                                                    $stmt_floor->execute();
                                                                    while($row = $stmt_floor->fetch()){
                                                                        echo "<option value='".htmlspecialchars($row['floor'])."'>".htmlspecialchars($row['floor'])."</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                       

                                                         <div class="col-md-6">
                                                            <div class="form-group">                                  <label for="dates" class="col-form-label">Request Date and Time:</label>
                                                                <input type="datetime-local" class="form-control" id="dates" name="dates">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <button type="submit" class="btn btn-primary" name="submit-request">Submit Request</button>
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
                <?php  require "staffallocation/stafftable.php";   ?>

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

    <script>
    // JavaScript for asset search functionality
    $(document).ready(function() {
        // Asset search functionality
        $('.asset-name').on('input', function() {
            const searchTerm = $(this).val();
            const suggestionsContainer = $(this).next('.asset-suggestions');
            
            if (searchTerm.length > 2) {
                $.get('staffallocation.php', { q: searchTerm }, function(data) {
                    try {
                        const assets = JSON.parse(data);
                        suggestionsContainer.empty().show();
                        
                        if (assets.length > 0) {
                            assets.forEach(asset => {
                                const quantityClass = asset.quantity > 0 ? 'quantity-available' : 'quantity-empty';
                                suggestionsContainer.append(
                                    `<div class="list-group-item" data-asset='${JSON.stringify(asset)}'>
                                        <div>${asset.asset_name} (${asset.reg_no})</div>
                                        <span class="asset-quantity ${quantityClass}">${asset.quantity} available</span>
                                    </div>`
                                );
                            });
                        } else {
                            suggestionsContainer.append('<div class="list-group-item">No assets found</div>');
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                    }
                });
            } else {
                suggestionsContainer.hide();
            }
        });

        // Handle asset selection from suggestions
        $(document).on('click', '.asset-suggestions .list-group-item', function() {
            const asset = $(this).data('asset');
            const entry = $(this).closest('.asset-entry');
            
            entry.find('.asset-name').val(asset.asset_name);
            entry.find('.reg-no').val(asset.reg_no);
            entry.find('.categor').val(asset.category);
            entry.find('#description-0').val(asset.description);
            entry.find('.quantity').prop('disabled', false);
            
            $(this).closest('.asset-suggestions').hide();
        });

        // Hide suggestions when clicking elsewhere
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.asset-suggestions').length && !$(e.target).hasClass('asset-name')) {
                $('.asset-suggestions').hide();
            }
        });

        // Add another asset entry
        $('#add-asset-btn').click(function() {
            const container = $('#assets-container');
            const index = container.children().length;
            const newEntry = container.children().first().clone();
            
            newEntry.find('input, textarea').val('');
            newEntry.find('.quantity').prop('disabled', true);
            newEntry.find('.remove-asset').show();
            
            newEntry.find('[id]').each(function() {
                const oldId = $(this).attr('id');
                $(this).attr('id', oldId.replace(/-0$/, `-${index}`));
            });
            
            newEntry.find('[for]').each(function() {
                const oldFor = $(this).attr('for');
                $(this).attr('for', oldFor.replace(/-0$/, `-${index}`));
            });
            
            container.append(newEntry);
        });

        // Remove asset entry
        $(document).on('click', '.remove-asset', function() {
            if ($('#assets-container').children().length > 1) {
                $(this).closest('.asset-entry').remove();
            }
        });
    });
    </script>
</body>

</html>

<script src="../admindashboard/include/info_population.js"></script>