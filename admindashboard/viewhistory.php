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
    <link rel="icon" type="image/png" sizes="16x16" href="../admindashboard/assets/images/logo.png">
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
                    <a class="navbar-brand" href="../index.php">
                        <!-- Logo icon -->
                        <b class="logo-icon p-l-10">
                            <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                            <!-- Dark Logo icon -->
                            <img src="../admindashboard/assets/images/logo.png" alt="homepage" class="light-logo" width="100px"/>
                           
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
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31"></a>
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
             <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-12 d-flex no-block align-items-center mt-3">
                        <h4 class="page-title">View Report Status</h4>
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
                <?php 
                   /*  require "modal.php"; */
                ?>
                   <!--END OF MODAL SECTION-->
                <!-- ============================================================== -->                <?php
                    require "../admindashboard/include/config.php";
                    
                    try {
                        // Validate and sanitize input
                        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                            throw new Exception('Invalid request ID');
                        }
                        $id = (int)$_GET['id'];
                        
                        // Prepare and execute the query to fetch repair asset details
                        $sql = "SELECT * FROM repair_asset WHERE id = :id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        // Fetch the result
                        if (!$row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            throw new Exception('Request not found');
                        }

                        // Fetch commplete asset details from completed_asset table
                        $sql = "SELECT * FROM completed_asset WHERE id = :id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $completed_row = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($completed_row) {
                            // Merge completed asset details into $row for display
                            $row = array_merge($row, $completed_row);
                        }

                    } catch (Exception $e) {
                        echo "<div class='alert alert-danger'>" . htmlspecialchars($e->getMessage()) . "</div>";
                        echo "<div class='col-md-8'><a href='assethistory.php' class='btn btn-primary'><i class='fa fa-backward'></i> Back</a></div>";
                        exit;
                    }
                ?>

                <!-- START OF UPDATING -->
                <div class="shadow p-4 mt-5 bg-white rounded">
                    <div class="row bg-light rounded">
                        <?php
                        // Fetch all history types for this asset using id for each table
                        $id = isset($row['id']) ? $row['id'] : 0;
                        if ($id > 0) {
                            // Completed Repairs
                            $stmt_completed = $conn->prepare("SELECT * FROM completed_asset WHERE id = :id ORDER BY completed_date DESC");
                            $stmt_completed->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt_completed->execute();
                            $completed_repairs = $stmt_completed->fetchAll(PDO::FETCH_ASSOC);

                            // Withdrawn Assets
                            $stmt_withdrawn = $conn->prepare("SELECT * FROM withdrawn_asset WHERE id = :id ORDER BY withdrawn_date DESC");
                            $stmt_withdrawn->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt_withdrawn->execute();
                            $withdrawn_assets = $stmt_withdrawn->fetchAll(PDO::FETCH_ASSOC);

                            // Replaced Assets
                            $stmt_replaced = $conn->prepare("SELECT * FROM asset_replacement_log WHERE id = :id ORDER BY replaced_at DESC");
                            $stmt_replaced->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt_replaced->execute();
                            $replaced_assets = $stmt_replaced->fetchAll(PDO::FETCH_ASSOC);

                            // Damaged Assets (Under Repair)
                            $stmt_damaged = $conn->prepare("SELECT * FROM repair_asset WHERE id = :id AND status = 'Under Repair' ORDER BY report_date DESC");
                            $stmt_damaged->bindParam(':id', $id, PDO::PARAM_INT);
                            $stmt_damaged->execute();
                            $damaged_assets = $stmt_damaged->fetchAll(PDO::FETCH_ASSOC);
                        }
                        ?>
                        <!-- Completed Repairs -->
                        <div class="col-md-12 mb-4">
                            <h5 class="text-success">Completed Repairs</h5>
                            <?php if (!empty($completed_repairs)): ?>
                                <ul class="list-group">
                                <?php foreach ($completed_repairs as $item): ?>
                                    <li class="list-group-item">Completed on <?php echo htmlspecialchars($item['completed_date']); ?> | Qty: <?php echo htmlspecialchars($item['quantity']); ?> | By: <?php echo htmlspecialchars($item['reported_by']); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted">No completed repairs found.</div>
                            <?php endif; ?>
                        </div>
                        <!-- Withdrawn Assets -->
                        <div class="col-md-12 mb-4">
                            <h5 class="text-warning">Withdrawn Assets</h5>
                            <?php if (!empty($withdrawn_assets)): ?>
                                <ul class="list-group">
                                <?php foreach ($withdrawn_assets as $item): ?>
                                    <li class="list-group-item">Withdrawn on <?php echo htmlspecialchars($item['withdrawn_date']); ?> | Qty: <?php echo htmlspecialchars($item['qty']); ?> | By: <?php echo htmlspecialchars($item['withdrawn_by']); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted">No withdrawn assets found.</div>
                            <?php endif; ?>
                        </div>
                        <!-- Replaced Assets -->
                        <div class="col-md-12 mb-4">
                            <h5 class="text-info">Replaced Assets</h5>
                            <?php if (!empty($replaced_assets)): ?>
                                <ul class="list-group">
                                <?php foreach ($replaced_assets as $item): ?>
                                    <li class="list-group-item">Replaced on <?php echo htmlspecialchars($item['replaced_at']); ?> | Qty: <?php echo htmlspecialchars($item['replaced_quantity']); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted">No replaced assets found.</div>
                            <?php endif; ?>
                        </div>
                        <!-- Damaged Assets -->
                        <div class="col-md-12 mb-4">
                            <h5 class="text-danger">Damaged Assets (Under Repair)</h5>
                            <?php if (!empty($damaged_assets)): ?>
                                <ul class="list-group">
                                <?php foreach ($damaged_assets as $item): ?>
                                    <li class="list-group-item">Reported on <?php echo htmlspecialchars($item['report_date']); ?> | Qty: <?php echo htmlspecialchars($item['quantity']); ?> | Status: <?php echo htmlspecialchars($item['status']); ?></li>
                                <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="text-muted">No damaged assets found.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8 text-center mb-3">
                            <a href="assethistory.php"><button class="btn btn-primary"><i class='fa fa-backward'></i> Back</button></a>
                        </div>
                    </div>
                </div>
                <!-- END OF UPDATING -->
                 
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
    <style>
.form-label {
    font-size: 1.1rem;
    margin-bottom: 0.3em;
    letter-spacing: 0.5px;
}
.input-group .form-control {
    background: #f8f9fa;
    border-radius: 8px;
    border: 1.5px solid #b6d4fe;
    box-shadow: 0 2px 8px rgba(30,144,255,0.07);
    font-size: 1rem;
    padding: 0.7em 1em;
}
.form-group {
    margin-bottom: 1.5em;
}
.shadow {
    box-shadow: 0 8px 32px rgba(30,144,255,0.13), 0 1.5px 6px rgba(0,0,0,0.09);
}
.bg-light {
    background: linear-gradient(135deg, #e0e7ff 60%, #fff 100%);
}
</style>

</body>

</html>

