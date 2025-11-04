<?php
session_start();
// Update the path to the correct location of config.php
require "../../admindashboard/include/config.php"; 
 
$id = $_GET['id']; // Retrieve the id from the URL parameters

if (isset($_POST['submit'])) {
    try {
        // Sanitize and validate input
        $desc = trim($_POST['desc']);
        $qty = (int)$_POST['qty'];
        $date = date('Y-m-d'); // Use current date for update
        
        if (empty($desc)) {
            throw new Exception("All fields are required and quantity must be non-negative");
        }

        $update_sql = "UPDATE asset_table
                       SET description = :description, 
                          quantity = :quantity, 
                          updated_at = :date 
                            WHERE id = :id";
                      
        $stmt = $conn->prepare($update_sql);
        $stmt->bindParam(':description', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $qty, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            error_log("Asset updated successfully: ID = $id");
            echo "<script>alert('Record updated successfully'); window.location.href='../assets.php';</script>";
        } else {
            throw new Exception("Failed to update record");
        }
    } catch (Exception $e) {
        error_log("Error updating asset in editasset.php: " . $e->getMessage());
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}

// Get the first name and last name of the logged-in admin
require "../layouts/layout.php";
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
    <link rel="icon" type="image/png" sizes="16x16" href="../../admindashboard/assets/images/isalu-logo.png">
    <title>Procurement||Edit Page</title>
    <!-- Custom CSS -->
    <link href="../../admindashboard/assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../admindashboard/dist/css/style.min.css" rel="stylesheet">
    
    
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
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
 <!--    <div class="preloader">
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
                            <img src="../../admindashboard/assets/images/isalu-logo.png" alt="homepage" class="light-logo" />

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
                            <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="../../admindashboard/assets/images/users/1.jpg" alt="user" class="rounded-circle" width="31"> <?php echo htmlspecialchars($pro_first_name . ' ' . $pro_last_name); ?></a> 
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
                        <h4 class="page-title">Update Asset</h4>
                        <div class="ml-auto text-right">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Library</li>
                                </ol>
                            </nav>
                        </div>
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
                   <!--END OF MODAL SECTION-->                <!-- ============================================================== -->                <?php
                    require "../../admindashboard/include/config.php";
                    try {
                        $id = isset($_GET['id']) ? $_GET['id'] : 0;
                        $sql = "SELECT * FROM asset_table WHERE id = :id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (!$row) {
                            echo "<script>alert('Asset not found'); window.location.href='../assets.php';</script>";
                            exit;
                        }
                    } catch (PDOException $e) {
                        error_log("Error loading asset in editasset.php: " . $e->getMessage());
                        echo "<script>alert('Error loading asset details'); window.location.href='../assets.php';</script>";
                        exit;
                    }
                ?>

                <!-- START OF UPDATING -->
                 <form action="" method="POST" class="shadow p-4 mt-5 bg-white rounded">
                  
                 <div class="row bg-light rounded"><!-- start row-->
                    <div class="col-md-8 m-auto">
                        <div class="form-group shadow-sm">
                            <label for="" class="form-label">Registration No:</label>
                                <input type="text" id="" class="form-control" name="asset_model" value="<?php echo $row['reg_no']; ?>"  disabled>
                        </div>
                    </div>

                    <div class="col-md-8 m-auto">
                        <div class="form-group shadow-sm">
                            <label for="" class="form-label">Asset Name:</label>
                                <input type="text" id="" class="form-control" name="asset_name" value="<?php echo $row['asset_name']; ?>" disabled>
                        </div>
                    </div>

                    <div class="col-md-8 m-auto">
                        <div class="form-group shadow-sm">
                            <label for="" class="form-label">Description:</label>
                                <textarea name="desc" id="" rows="3" class="form-control" ><?php echo $row['description']; ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-8 m-auto">
                        <div class="form-group shadow-sm">
                            <label for="" class="form-label">Quantity:</label>
                                <input type="number" id="" class="form-control" name="qty" value="<?php echo $row['quantity']; ?>">
                        </div>
                    </div>

                    <div class="col-md-8 m-auto">
                        <div class="form-group shadow-sm">
                            <label for="category" class="col-form-label">Category:</label>
                                <select id="category" class="form-control" name="asset_cat">
                                    <option selected disabled><?php echo $row['category']; ?></option>                                        <?php
                                       /*  try {
                                            $cat_sql = "SELECT * FROM category";
                                            $cat_stmt = $conn->prepare($cat_sql);
                                            $cat_stmt->execute();
                                            
                                            while($cat_row = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='" . htmlspecialchars($cat_row['category']) . "'>" . 
                                                     htmlspecialchars($cat_row['category']) . "</option>";
                                            }
                                        } catch (PDOException $e) {
                                            error_log("Error loading categories in editasset.php: " . $e->getMessage());
                                            echo "<option value=''>Error loading categories</option>";
                                        } */
                                        ?>
                                </select>
                                                               
                        </div>
                    </div>
                    <div class="col-md-8 m-auto">
                    <input type="submit" name="submit" id="" class="btn btn-primary shadow" value="Update">
                    </div>
                 </div><!-- End row-->
                 </form>
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
            <?php require "../../admindashboard/include/footer.php" ?>
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
    <script src="../../admindashboard/assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="../../admindashboard/assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="../../admindashboard/assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../../admindashboard/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="../../admindashboard/assets/extra-libs/sparkline/sparkline.js"></script>
    <!--Wave Effects -->
    <script src="../../admindashboard/dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="../../admindashboard/dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="../../admindashboard/dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <!-- <script src="dist/js/pages/dashboards/dashboard1.js"></script> -->
    <!-- Charts js Files -->
    <script src="../../admindashboard/assets/libs/flot/excanvas.js"></script>
    <script src="../../admindashboard/assets/libs/flot/jquery.flot.js"></script>
    <script src="../../admindashboard/assets/libs/flot/jquery.flot.pie.js"></script>
    <script src="../../admindashboard/assets/libs/flot/jquery.flot.time.js"></script>
    <script src="../../admindashboard/assets/libs/flot/jquery.flot.stack.js"></script>
    <script src="../../admindashboard/assets/libs/flot/jquery.flot.crosshair.js"></script>
    <script src="../../admindashboard/assets/libs/flot.tooltip/js/jquery.flot.tooltip.min.js"></script>
    <script src="../../admindashboard/dist/js/pages/chart/chart-page-init.js"></script>

</body>

</html>

