<?php
require_once "../admindashboard/include/config.php";

require_once 'usersession.php'; // Include user session management
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
    <title>Facility||Dashboard</title>

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
    <!-- Custom CSS -->
    <link href="../admindashboard/assets/libs/flot/css/float-chart.css" rel="stylesheet">
    <link href="../admindashboard/dist/css/style.min.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <!-- <div class="preloader">
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
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->

        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <?php require "asidebar.php"; ?>
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
                        <h4 class="page-title">QR Code Generator</h4>
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
                <!-- QR CODE GENERATOR FORM -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Generate QR Code</h4>
                                <form id="qrForm" method="POST" action="">
                                
                                    <div class="form-group">
                                        <label for="asset_name" class="col-form-label">Asset Name:</label>
                                            <select id="asset_name" class="form-control" name="asset_name">
                                                <option selected disabled>Select Asset</option>
                                                    <?php
                                                        try {
                                                            $stmt = $conn->prepare("SELECT asset_name FROM asset_table");
                                                            $stmt->execute();
                                                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                                                                echo "<option value='".htmlspecialchars($row['asset_name'], ENT_QUOTES)."'>".htmlspecialchars($row['asset_name'], ENT_QUOTES)."</option>";
                                                            }
                                                        } catch(PDOException $e) {
                                                            error_log("Error fetching assets: " . $e->getMessage());
                                                            echo "<option disabled>Error loading assets</option>";
                                                        }
                                                    ?>
                                            </select>
                                                               
                                    </div>
                                
                                    <div class="form-group">
                                        <label for="assetRegNumber">Asset Reg Number</label>
                                        <input type="text" class="form-control" id="assetRegNumber" placeholder="Enter asset registration number">
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <input type="text" class="form-control" id="description" placeholder="Enter description">
                                    </div>
                                    <div class="form-group">
                                        <label for="purchaseDate">Purchase Date</label>
                                        <input type="date" class="form-control" id="purchaseDate">
                                    </div>

                                    <div class="form-group">
                                        <label for="purchaseDate">Last Service Date</label>
                                        <input type="date" class="form-control" id="lastServiceDate">
                                    </div>

                                    <div class="form-group">
                                        <label for="purchaseDate">Predicted Next Service Date</label>
                                        <input type="date" class="form-control" id="predictdate">
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Generate</button>
                                        <button type="button" id="printButton" class="btn btn-secondary" disabled>Print</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">QR Code</h4>
                                <div id="qrCode"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- End QR CODE GENERATOR FORM -->
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
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#qrForm').on('submit', function(e) {
                e.preventDefault();
                var assetName = $('#asset_name').val();
                var assetRegNumber = $('#assetRegNumber').val();
                var description = $('#description').val();
                var purchaseDate = $('#purchaseDate').val();
                var lastServiceDate = $('#lastServiceDate').val();
                var predictDate = $('#predictdate').val();
                var qrData = JSON.stringify({
                    assetName: assetName,
                    assetRegNumber: assetRegNumber,
                    description: description,
                    purchaseDate: purchaseDate,
                    lastServiceDate: lastServiceDate,
                    predictDate: predictDate
                });

                if (assetName && assetRegNumber && description && purchaseDate) {
                    $('#qrCode').html('');
                    var qrCode = new QRCode(document.getElementById("qrCode"), {
                        text: qrData,
                        width: 256, // Set width for better readability
                        height: 256, // Set height for better readability
                        correctLevel: QRCode.CorrectLevel.H // Set error correction level to High
                    });
                    $('#printButton').prop('disabled', false);
                } else {
                    alert('Please fill in all required fields.');
                }
            });

            $('#printButton').on('click', function() {
                var printContents = document.getElementById("qrCode").innerHTML;
                var originalContents = document.body.innerHTML;
                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload();
            });

            $('#asset_name').on('change', function() {
                var assetName = $(this).val();
                if (assetName) {
                    $.ajax({
                        url: '../admindashboard/fetch_asset_details.php',
                        type: 'POST',
                        data: { asset_name: assetName },
                        dataType: 'json',
                        success: function(data) {
                            if (data) {
                                $('#assetRegNumber').val(data.reg_no);
                                $('#description').val(data.description);
                                $('#purchaseDate').val(data.dateofpurchase);
                                $('#lastServiceDate').val(data.last_service || '');
                                $('#predictdate').val(data.next_service || '');
                            } else {
                                alert('No data found for the selected asset.');
                            }
                        },
                        error: function() {
                            alert('Error fetching asset details.');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>