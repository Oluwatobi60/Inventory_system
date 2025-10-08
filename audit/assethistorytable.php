<?php
require "../admindashboard/include/config.php";

function render_pagination($page, $total_pages, $extra_params = '') {
    if ($total_pages > 1) {
        echo '<div class="d-flex justify-content-center mt-4"><ul class="pagination">';
        if ($page > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page-1) . $extra_params . '">Previous</a></li>';
        }
        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<li class="page-item ' . (($i == $page) ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . $extra_params . '">' . $i . '</a></li>';
        }
        if ($page < $total_pages) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($page+1) . $extra_params . '">Next</a></li>';
        }
        echo '</ul></div>';
    }
}

// Helper for pagination params
$extra_params = '';
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $extra_params .= '&start_date=' . urlencode($_GET['start_date']);
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $extra_params .= '&end_date=' . urlencode($_GET['end_date']);
}

try {

  // Withdrawn Assets
    // Get current page for withdrawn assets, default to 1
    $page4 = isset($_GET['page4']) ? (int)$_GET['page4'] : 1;
    // Calculate offset for pagination
    $offset4 = ($page4 - 1) * 7;
    // Build WHERE clause to filter withdrawn assets
    $where4 = "WHERE status = '1' || status = '0'";
    // Add date filters if provided
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $where4 .= " AND DATE(withdrawn_date) >= :start_date4";
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $where4 .= " AND DATE(withdrawn_date) <= :end_date4";
    }
    // Prepare SQL to count total withdrawn assets for pagination
    $count_sql4 = "SELECT COUNT(*) as total FROM withdrawn_asset $where4";
    $stmt4 = $conn->prepare($count_sql4);
    // Bind date parameters if present
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt4->bindValue(':start_date4', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt4->bindValue(':end_date4', $_GET['end_date']);
    }
    // Execute the count query
    $stmt4->execute();
    // Get total number of withdrawn assets
    $total4 = $stmt4->fetch(PDO::FETCH_ASSOC)['total'];
    // Calculate total pages for pagination
    $total_pages4 = ceil($total4 / 7);
    // Prepare SQL to fetch withdrawn assets for current page
    $sql4 = "SELECT * FROM withdrawn_asset $where4 ORDER BY id DESC LIMIT 7 OFFSET $offset4";
    $stmt4 = $conn->prepare($sql4);
    // Bind date parameters if present
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt4->bindValue(':start_date4', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt4->bindValue(':end_date4', $_GET['end_date']);
    }
    // Execute the fetch query
    $stmt4->execute();
    // Fetch all withdrawn assets for display
    $withdrawn_assets = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // Damaged Assets (Under Repair)
    $page1 = isset($_GET['page1']) ? (int)$_GET['page1'] : 1;
    $offset1 = ($page1 - 1) * 7;
    $where1 = "WHERE status = 'Under Repair'";
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $where1 .= " AND DATE(report_date) >= :start_date1";
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $where1 .= " AND DATE(report_date) <= :end_date1";
    }
    $count_sql1 = "SELECT COUNT(*) as total FROM repair_asset $where1";
    $stmt1 = $conn->prepare($count_sql1);
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt1->bindValue(':start_date1', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt1->bindValue(':end_date1', $_GET['end_date']);
    }
    $stmt1->execute();
    $total1 = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages1 = ceil($total1 / 7);
    $sql1 = "SELECT * FROM repair_asset $where1 ORDER BY id DESC LIMIT 7 OFFSET $offset1";
    $stmt1 = $conn->prepare($sql1);
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt1->bindValue(':start_date1', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt1->bindValue(':end_date1', $_GET['end_date']);
    }
    $stmt1->execute();
    $damaged_assets = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Completed Repairs
    $page2 = isset($_GET['page2']) ? (int)$_GET['page2'] : 1;
    $offset2 = ($page2 - 1) * 6;
    $where2 = "WHERE completed = '1'";
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $where2 .= " AND DATE(completed_date) >= :start_date2";
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $where2 .= " AND DATE(completed_date) <= :end_date2";
    }
    $count_sql2 = "SELECT COUNT(*) as total FROM completed_asset $where2";
    $stmt2 = $conn->prepare($count_sql2);
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt2->bindValue(':start_date2', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt2->bindValue(':end_date2', $_GET['end_date']);
    }
    $stmt2->execute();
    $total2 = $stmt2->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages2 = ceil($total2 / 7);
    $sql2 = "SELECT * FROM completed_asset $where2 ORDER BY id DESC LIMIT 7 OFFSET $offset2";
    $stmt2 = $conn->prepare($sql2);
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt2->bindValue(':start_date2', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt2->bindValue(':end_date2', $_GET['end_date']);
    }
    $stmt2->execute();
    $completed_assets = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Replaced Assets
    $page3 = isset($_GET['page3']) ? (int)$_GET['page3'] : 1;
    $offset3 = ($page3 - 1) * 7;
    $where3 = "WHERE replaced = '1'";
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $where3 .= " AND DATE(replaced_date) >= :start_date3";
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $where3 .= " AND DATE(replaced_date) <= :end_date3";
    }
    $count_sql3 = "SELECT COUNT(*) as total FROM repair_asset $where3";
    $stmt3 = $conn->prepare($count_sql3);
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt3->bindValue(':start_date3', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt3->bindValue(':end_date3', $_GET['end_date']);
    }
    $stmt3->execute();
    $total3 = $stmt3->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages3 = ceil($total3 / 7);
    $sql3 = "SELECT * FROM repair_asset $where3 ORDER BY id DESC LIMIT 7 OFFSET $offset3";
    $stmt3 = $conn->prepare($sql3);
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $stmt3->bindValue(':start_date3', $_GET['start_date']);
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $stmt3->bindValue(':end_date3', $_GET['end_date']);
    }
    $stmt3->execute();
    $replaced_assets = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
.table thead th {
    background: linear-gradient(90deg, #e0e7ff 60%, #b6d4fe 100%);
    color: #1e293b;
    font-weight: bold;
    font-size: 1.05rem;
    border-bottom: 2px solid #b6d4fe;
    letter-spacing: 0.5px;
}
.table tbody tr {
    background: #fff;
    transition: background 0.2s;
}
.table tbody tr:hover {
    background: #f1f5fa;
}
.table td, .table th {
    vertical-align: middle !important;
}
.card-header {
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}
</style>
<!-- Damaged Assets Table -->
<?php if (!empty($damaged_assets)): ?>
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger text-white">Damaged Assets (Under Repair)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Asset Name</th>
                                <th>Department</th>
                                <th>Status</th>
                               <!--  <th>Action</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i1 = 1; foreach ($damaged_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i1++; ?></td>
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><span class="badge badge-danger">Under Repair</span></td>
                                   
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php render_pagination($page1, $total_pages1, '&page1='); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- Completed Repairs Table -->
<?php if (!empty($completed_assets)): ?>
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">Completed Repairs</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Asset Name</th>
                                <th>Department</th>
                                <th>Floor</th>
                                <th>Completion Date</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i2 = 1; foreach ($completed_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i2++; ?></td>
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['floor']); ?></td>
                                    <td><?php echo htmlspecialchars($row['completed_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                   
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php render_pagination($page2, $total_pages2, '&page2='); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Withdrawn Assets Table -->
<?php if (!empty($withdrawn_assets)): ?>
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">Withdrawn Assets</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <!-- <th>Reg No</th> -->
                                <th>Asset Name</th>
                                <th>Department</th>
                                <th>Floor</th>
                                <th>Withdrawn Date</th>
                                <th>Quantity</th>
                               <!--  <th>Action</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i4 = 1; foreach ($withdrawn_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i4++; ?></td>
                                 <!--    <td><?php //echo htmlspecialchars($row['reg_no']); ?></td> -->
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['floor']); ?></td>
                                    <td><?php echo htmlspecialchars($row['withdrawn_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['qty']); ?></td>
                                  
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php render_pagination($page4, $total_pages4, '&page4='); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Replaced Assets Table -->
 <?php if (!empty($replaced_assets)): ?>
<div class="row mb-5">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">Replaced Assets</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Asset Name</th>
                                <th>Department</th>
                                <th>Floor</th>
                                <th>Replaced Date</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i3 = 1; foreach ($replaced_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i3++; ?></td>
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['floor']); ?></td>
                                    <td><?php echo htmlspecialchars($row['replaced_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php render_pagination($page3, $total_pages3, '&page3='); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo '<div class="alert alert-danger">An error occurred while fetching the data. Please try again later.</div>';
}
?>