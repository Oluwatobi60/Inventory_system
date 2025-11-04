<?php
require_once dirname(__FILE__) . "/../../include/utils.php";
require_once dirname(__FILE__) . "/../include/config.php";

// ===== Pagination Configuration =====
// Set the number of items to display per page
$items_per_page = 7; // Minimum items per page

// Get the current page number from URL, default to page 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for SQL LIMIT clause
$offset = ($page - 1) * $items_per_page;

// ===== Filter Configuration =====
// Initialize WHERE clause and params array for SQL query
$where_clause = "WHERE 1=1";
$params = [];

// Add floor filter if provided
if (isset($_GET['floor']) && !empty($_GET['floor'])) {
    $where_clause .= " AND s.floor LIKE :floor";
    $params[':floor'] = "%" . $_GET['floor'] . "%";
}

// Params array is already initialized above with the employee filter

try {
    // Get total count
    $total_sql = "SELECT COUNT(*) AS total FROM staff_table s $where_clause";
    $total_stmt = $conn->prepare($total_sql);
    
    // Bind date parameters if they exist
    foreach ($params as $key => $value) {
        $total_stmt->bindValue($key, $value);
    }
    
    $total_stmt->execute();
    $total_row = $total_stmt->fetch(PDO::FETCH_ASSOC);    $total_items = (int)$total_row['total'];
    $total_pages = max(1, ceil($total_items / $items_per_page));

    // Log pagination details
    logError("Pagination details", [
        'total_items' => $total_items,
        'items_per_page' => $items_per_page,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'offset' => $offset
    ]);

    // Ensure page number is within valid range
    if ($page > $total_pages) {
        $page = $total_pages;
        $offset = ($page - 1) * $items_per_page;
    }    // Fetch staff allocation data with repair status
    $sql = "SELECT s.*, DATE_FORMAT(s.request_date, '%Y-%m-%d %H:%i') as formatted_date,
            CASE WHEN r.status = 'Under Repair' THEN 1 ELSE 0 END as is_under_repair
            FROM staff_table s 
            LEFT JOIN repair_asset r ON s.id = r.asset_id AND r.status = 'Under Repair'
            $where_clause 
            ORDER BY s.request_date DESC 
            LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($sql);
    
    // Bind the date parameters first
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    // Then bind the pagination parameters
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    
    $stmt->execute();
    
} catch (PDOException $e) {
    logError("Database error in stafftable.php: " . $e->getMessage() . 
            "\nSQL Query: " . (isset($sql) ? $sql : $total_sql) . 
            "\nParameters: offset=" . $offset . ", limit=" . $items_per_page);
    $total_items = 0;
    $total_pages = 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Allocation</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
        }
        .employee-filter {
            position: relative;
            margin-bottom: 1rem;
        }
        .employee-filter input {
            width: 100%;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .loading {
            background-image: url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==');
            background-position: right center;
            background-repeat: no-repeat;
            padding-right: 25px;
        }
        .ui-autocomplete-loading {
            background-position: right center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body>
    <!-- Main content container -->
    <div class="row mt-5">
        <div class="col-md-12 col-lg-12 col-xlg-3">
            <!-- Filter Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <form id="filterForm" method="GET" class="row align-items-center">
                        <div class="col-md-4">
                            <div class="employee-filter">
                                <label for="floor" class="form-label">Filter by Floor:</label>
                                <input type="text" class="form-control" id="floor" name="floor" 
                                       value="<?php echo isset($_GET['floor']) ? htmlspecialchars($_GET['floor']) : ''; ?>" 
                                       placeholder="Type floor...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Apply Filter</button>
                        </div>
                        <?php if (isset($_GET['floor'])): ?>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="?<?php echo isset($_GET['page']) ? 'page=' . $_GET['page'] : ''; ?>" 
                                   class="btn btn-secondary d-block">Clear Filter</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <!-- Responsive table wrapper -->
            <div class="table-responsive">
                <table class="table shadow table-striped table-bordered table-hover">               
                     <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Reg No.</th>
                        <th scope="col">Asset Name</th>
                        <th scope="col">Department</th>
                        <th scope="col">Floor</th>
                      <!--   <th scope="col">Requested By</th> -->
                        <th scope="col">Quantity</th>
                        <th scope="col">Allocation Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php                    $s = $offset + 1; // Initialize serial number for the current page
                    // Fetch and display all results using PDO
                    $hasRows = false;
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        // Sanitize data to prevent XSS attacks
                        $id = htmlspecialchars($row['id']);
                        $reg_no = htmlspecialchars($row['reg_no']);
                        $asset_name = htmlspecialchars($row['asset_name']);
                        $department = htmlspecialchars($row['department']);
                        $floor = htmlspecialchars($row['floor']);
                      /*   $requested_by = htmlspecialchars($row['requested_by']); */
                        $quantity = (int)$row['quantity'];
                        $request_date = htmlspecialchars($row['formatted_date']);
                        $status = isset($row['status']) ? htmlspecialchars($row['status']) : 'Pending';
                        $repair_qty = isset($row['repair_quantity']) ? (int)$row['repair_quantity'] : 1;

                        // Get total quantity under repair for this asset
                        $repair_qty_stmt = $conn->prepare("SELECT SUM(quantity) AS total_repair_qty FROM repair_asset WHERE asset_id = :asset_id AND status = 'Under Repair'");
                        $repair_qty_stmt->bindParam(':asset_id', $id, PDO::PARAM_INT);
                        $repair_qty_stmt->execute();
                        $repair_qty_row = $repair_qty_stmt->fetch(PDO::FETCH_ASSOC);
                        $repair_qty = $repair_qty_row && $repair_qty_row['total_repair_qty'] ? (int)$repair_qty_row['total_repair_qty'] : 0;

                        // Check if asset is withdrawn (withdrawn=1 in repair_asset)
                        $is_withdrawn = false;
                        $withdrawn_stmt = $conn->prepare("SELECT withdrawn FROM repair_asset WHERE asset_id = :asset_id AND withdrawn = 1 LIMIT 1");
                        $withdrawn_stmt->bindParam(':asset_id', $id, PDO::PARAM_INT);
                        $withdrawn_stmt->execute();
                        $withdrawn_row = $withdrawn_stmt->fetch(PDO::FETCH_ASSOC);
                        if ($withdrawn_row && $withdrawn_row['withdrawn'] == 1) {
                            $is_withdrawn = true;
                        }

                        // Check if asset is replaced and status is NULL
                        $is_replaced_need_repair = false;
                        $replaced_stmt = $conn->prepare("SELECT replaced, status FROM repair_asset WHERE asset_id = :asset_id AND replaced = 1 AND status IS NULL LIMIT 1");
                        $replaced_stmt->bindParam(':asset_id', $id, PDO::PARAM_INT);
                        $replaced_stmt->execute();
                        $replaced_row = $replaced_stmt->fetch(PDO::FETCH_ASSOC);
                        if ($replaced_row && $replaced_row['replaced'] == 1 && $replaced_row['status'] === null) {
                            $is_replaced_need_repair = true;
                        }
                        $hasRows = true;
                        ?>
                        <tr>
                            <th scope="row"><?php echo $s++; ?></th>
                            <td><?php echo $reg_no; ?></td>
                            <td><?php echo $asset_name; ?></td>
                            <td><?php echo $department; ?></td>
                            <td><?php echo $floor; ?></td>
                           <!--  <td><?php //echo $requested_by; ?></td>  -->
                            <td><span class="badge badge-info"><?php echo $quantity; ?></span></td>
                            <td><?php echo $request_date; ?></td>                            <td>
                                <a href="staffallocation/viewallocation.php?id=<?php echo $id; ?>" class="btn btn-info btn-sm">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a href="staffallocation/deleteallocation.php?id=<?php echo $id; ?>" class="btn btn-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </a>
                                <?php
                                if ($row['is_under_repair'] && (!isset($row['withdrawn']) || !$row['withdrawn'])): ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fa fa-wrench"></i> Under Repair
                                    </button>
                                    <button onclick="promptRepairCompleted(<?php echo $id; ?>, <?php echo $repair_qty; ?>, <?php echo $repair_qty; ?>, this)" class="btn btn-success btn-sm">
                                        <i class="fa fa-check"></i> Repair Completed
                                    </button>
                                    <button onclick="withdrawAsset(<?php echo $id; ?>)" class="btn btn-danger btn-sm">
                                        <i class="fa fa-ban"></i> Withdrawn
                                    </button>
                                <?php else:
                                    // Fetch repair_asset row for this asset
                    $btn_stmt = $conn->prepare("SELECT id, withdrawn, quantity, status, replaced FROM repair_asset WHERE asset_id = :asset_id ORDER BY id DESC LIMIT 1");
                                    $btn_stmt->bindParam(':asset_id', $id, PDO::PARAM_INT);
                                    $btn_stmt->execute();
                                    $btn_row = $btn_stmt->fetch(PDO::FETCH_ASSOC);
                    // Debug output for button logic (shows repair_asset.id when available)
                    /* echo '<div style="color: red; font-size: 12px;">DEBUG: staff_id=' . $id . ' repair_id=' . ($btn_row ? $btn_row['id'] : 'NULL') . ' withdrawn=' . ($btn_row ? $btn_row['withdrawn'] : 'NULL') . ' quantity=' . ($btn_row ? $btn_row['quantity'] : 'NULL') . ' status=' . ($btn_row ? var_export($btn_row['status'], true) : 'NULL') . ' replaced=' . ($btn_row ? $btn_row['replaced'] : 'NULL') . '</div>'; */
                        if ($btn_row && $btn_row['withdrawn'] == 1 && (int)$btn_row['quantity'] === 0 && $btn_row['status'] === null && $btn_row['replaced'] == 0): ?>
                        <button onclick="promptReplaceAsset(<?php echo $id; ?>, <?php echo $btn_row ? $btn_row['id'] : 'null'; ?>, this)" class="btn btn-primary btn-sm">
                                                <i class="fa fa-refresh"></i> Replace
                                            </button>
                                        <?php elseif ($btn_row && ($btn_row['withdrawn'] == 1 || $btn_row['withdrawn'] == 0) && (int)$btn_row['quantity'] === 0 && $btn_row['status'] === null && $btn_row['replaced'] == 1): ?>
                                            <button onclick="promptRepairCount(<?php echo $id; ?>, <?php echo $quantity; ?>, <?php 
                                                echo htmlspecialchars(json_encode([
                                                    'reg_no' => $reg_no,
                                                    'asset_name' => $asset_name,
                                                    'department' => $department,
                                                    'category' => 'General'
                                                ]), ENT_QUOTES); 
                                            ?>)" class="btn btn-warning btn-sm">
                                                <i class="fa fa-wrench"></i> Need Repair
                                            </button>
                                    <?php else: ?>
                                        <button onclick="promptRepairCount(<?php echo $id; ?>, <?php echo $quantity; ?>, <?php 
                                            echo htmlspecialchars(json_encode([
                                                'reg_no' => $reg_no,
                                                'asset_name' => $asset_name,
                                                'department' => $department,
                                                'category' => 'General'
                                            ]), ENT_QUOTES); 
                                        ?>)" class="btn btn-warning btn-sm">
                                            <i class="fa fa-wrench"></i> Need Repair
                                        </button>
                                    <?php endif;
                                endif; ?>
                            </td>
                        </tr>
                    <?php
                    }
                    if (!$hasRows) {
                        echo "<tr><td colspan='8' class='text-center text-danger font-weight-bold'>No record found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div><!-- End of table-responsive -->        <!-- Pagination controls -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php
                // Preserve filter parameters in pagination URLs
                $filter_params = '';
                if (isset($_GET['floor']) && !empty($_GET['floor'])) {
                    $filter_params .= '&floor=' . urlencode($_GET['floor']);
                }

                // Show pagination only if there are items
                if ($total_items > 0):               // Previous page link
                    if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page - 1) . $filter_params; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                                <span class="sr-only">Previous</span>
                            </a>
                        </li>
                    <?php endif;

                    // Calculate range of page numbers to show
                    $range = 2; // Show 2 pages before and after current page
                    $start_page = max(1, $page - $range);
                    $end_page = min($total_pages, $page + $range);

                    // Show first page if not in range
                    if ($start_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?php echo $filter_params; ?>">1</a>
                        </li>
                        <?php if ($start_page > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif;
                    endif;

                    // Page numbers
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i . $filter_params; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor;

                    // Show last page if not in range
                    if ($end_page < $total_pages): 
                        if ($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages . $filter_params; ?>"><?php echo $total_pages; ?></a>
                        </li>
                    <?php endif;

                    // Next page link
                    if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo ($page + 1) . $filter_params; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                                <span class="sr-only">Next</span>
                            </a>
                        </li>
                    <?php endif;
                endif; ?>
            </ul>
        </nav>
    </div>
</div><!-- End of row for asset list -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        $("#floor").autocomplete({
            source: "get_floor_suggestions.php",
            minLength: 2,
            select: function(event, ui) {
                if (ui.item) {
                    $("#floor").val(ui.item.value);
                    $("#filterForm").submit();
                }
            },
            response: function(event, ui) {
                if (!ui.content.length) {
                    var noResult = { label: "No matches found", value: "" };
                    ui.content.push(noResult);
                }
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div>" + (item.label || item.value) + "</div>")
                .appendTo(ul);
        };
          // Make the autocomplete dropdown width match the input field
        $.ui.autocomplete.prototype._resizeMenu = function() {
            const ul = this.menu.element;
            ul.outerWidth(this.element.outerWidth());
        };

        // Add loading indicator
        $(document).ajaxStart(function() {
            $("#floor").addClass("loading");
        }).ajaxStop(function() {
            $("#floor").removeClass("loading");
        });
    });

    //mark asset for repair
    async function markForRepair(assetId, assetInfo, count = 1) {
        try {
            const button = event.target.closest('button');
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

            const response = await fetch('/admindashboard/staffallocation/submit_repair.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    asset_id: assetId,
                    asset_info: assetInfo,
                    quantity: count
                })
            });

            const data = await response.json();

            if (data.success) {
                const disabledBtn = document.createElement('button');
                disabledBtn.className = 'btn btn-secondary btn-sm';
                disabledBtn.disabled = true;
                disabledBtn.innerHTML = '<i class="fa fa-wrench"></i> Under Repair';
                button.parentNode.replaceChild(disabledBtn, button);
                alert('Asset has been marked for repair');
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-wrench"></i> Need Repair';
                alert(data.message || 'Failed to mark asset for repair');
            }
        } catch (error) {
            console.error('Error marking asset for repair:', error);
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-wrench"></i> Need Repair';
            alert('An error occurred while marking the asset for repair');
        }
    }

    //mark repair as completed
    async function markRepairCompleted(assetId, repairId = null, count = 1, button = null) {
        if (!button) button = document.activeElement;
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            const response = await fetch('staffallocation/complete_repair.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ asset_id: assetId, id: repairId, quantity: count })
            });
            let data;
            try {
                data = await response.json();
            } catch (e) {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-check"></i> Repair Completed';
                alert('Server error: Invalid response format.');
                return;
            }
            if (data.success) {
                button.innerHTML = '<i class="fa fa-check"></i> Repair Completed';
                button.className = 'btn btn-success btn-sm';
                button.disabled = true;
                alert('Repair marked as completed');
                location.reload();
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-check"></i> Repair Completed';
                alert(data.message || 'Failed to mark repair as completed');
            }
        } catch (error) {
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-check"></i> Repair Completed';
            alert('An error occurred while marking the repair as completed');
        }
    }

    //mark repair as withdrawn
    async function withdrawAsset(assetId) {
        try {
            const button = event.target.closest('button');
            // Prompt for quantity to withdraw
            let qty = prompt('Enter quantity to withdraw:', '1');
            if (qty === null) return; // Cancelled
            qty = parseInt(qty, 10);
            if (isNaN(qty) || qty < 1) {
                alert('Please enter a valid quantity.');
                return;
            }
            // Prompt for reason
            let reason = prompt('Enter reason for withdrawal:', '');
            if (reason === null || reason.trim() === '') {
                alert('Withdrawal reason is required.');
                return;
            }
            // Optionally, prompt for withdrawn_by (could use session user)
            let withdrawn_by = 'admin'; // Replace with actual user if available

            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            const response = await fetch('/admindashboard/staffallocation/withdraw_asset.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ asset_id: assetId, quantity: qty, withdrawn_by, reason })
            });
            const data = await response.json();
            if (data.success) {
                button.innerHTML = '<i class="fa fa-ban"></i> Withdrawn';
                button.className = 'btn btn-danger btn-sm';
                button.disabled = true;
                alert('Asset has been withdrawn');
                location.reload();
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-ban"></i> Withdrawn';
                alert(data.message || 'Failed to withdraw asset');
            }
        } catch (error) {
            console.error('Error withdrawing asset:', error);
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-ban"></i> Withdrawn';
            alert('An error occurred while withdrawing the asset');
        }
    }

    //mark asset as replaced
    async function promptReplaceAsset(assetId, repairId, button) {
        // Fetch repair quantity and withdrawn quantity for this asset
        let maxQty = 0;
        try {
            const response = await fetch(`/admindashboard/staffallocation/get_asset_quantities.php?asset_id=${assetId}`);
            const data = await response.json();
            if (data.success) {
                maxQty = data.withdrawn_qty;
            } else {
                alert('Could not fetch asset quantities.');
                return;
            }
        } catch (e) {
            alert('Error fetching asset quantities.');
            return;
        }
        let qty = prompt(`Enter number of assets replaced (max: ${maxQty}):`, maxQty);
        if (qty === null) return;
        qty = parseInt(qty, 10);
        if (isNaN(qty) || qty < 1 || qty > maxQty) {
            alert(`Please enter a valid number between 1 and ${maxQty}`);
            return;
        }
        replaceAsset(assetId, repairId, qty, button);
    }

    async function replaceAsset(assetId, repairId, qty, button) {
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
            const response = await fetch('/admindashboard/staffallocation/replace_asset.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ asset_id: assetId, id: repairId, quantity: qty })
            });
            const data = await response.json();
            if (data.success) {
                button.innerHTML = '<i class="fa fa-refresh"></i> Replaced';
                button.className = 'btn btn-primary btn-sm';
                button.disabled = true;
                alert('Asset has been replaced');
                location.reload();
            } else {
                button.disabled = false;
                button.innerHTML = '<i class="fa fa-refresh"></i> Replace';
                alert(data.message || 'Failed to replace asset');
            }
        } catch (error) {
            console.error('Error replacing asset:', error);
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-refresh"></i> Replace';
            alert('An error occurred while replacing the asset');
        }
    }

    function promptRepairCount(assetId, maxQty, assetInfo) {
        let count = prompt("Enter number of units to mark as 'Need Repair' (max: " + maxQty + "):", "1");
        if (count === null) return; // Cancelled
        count = parseInt(count, 10);
        if (isNaN(count) || count < 1 || count > maxQty) {
            alert("Please enter a valid number between 1 and " + maxQty);
            return;
        }
        markForRepair(assetId, assetInfo, count);
    }

    function promptRepairCompleted(assetId, repairId, maxQty, button) {
        // Prompt for number of units repaired, allow up to maxQty
        let count = prompt("Enter number of units repaired (max: " + maxQty + "):", maxQty);
        if (count === null) return; // Cancelled
        count = parseInt(count, 10);
        // Fix: allow maxQty >= 1, not just 1
        if (isNaN(count) || count < 1 || count > maxQty) {
            alert("Please enter a valid number between 1 and " + maxQty);
            return;
        }
        markRepairCompleted(assetId, repairId, count, button);
    }
</script>
</body>
</html>