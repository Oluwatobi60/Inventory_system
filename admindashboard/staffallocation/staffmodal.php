<?php
require_once dirname(__FILE__) . "/../include/config.php";
require_once dirname(__FILE__) . "/../../include/utils.php";

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
            }            // Insert request with timestamp
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
        echo "<script>alert('All assets allocated successfully!');</script>";

    } catch (Exception $e) {
        // Roll back the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>

<!-- Add styles for suggestions -->
<style>
.asset-suggestions {
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    border: 1px solid rgba(0,0,0,.125);
    border-radius: 4px;
    background: white;
}
.asset-suggestions .list-group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
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
</style>

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
                                                        <ul class="asset-suggestions list-group" style="position: absolute; z-index: 1000; display: none;"></ul>
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

<script src="../admindashboard/include/info_population.js"></script>

