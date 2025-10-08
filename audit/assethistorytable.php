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
    $offset2 = ($page2 - 1) * 7;
    $where2 = "WHERE completed = '1'";
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $where2 .= " AND DATE(completed_date) >= :start_date2";
    }
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $where2 .= " AND DATE(completed_date) <= :end_date2";
    }
    $count_sql2 = "SELECT COUNT(*) as total FROM repair_asset $where2";
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
    $sql2 = "SELECT * FROM repair_asset $where2 ORDER BY id DESC LIMIT 7 OFFSET $offset2";
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i1 = 1; foreach ($damaged_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i1++; ?></td>
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><span class="badge badge-danger">Under Repair</span></td>
                                    <td>
                                        <a href="viewhistory.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
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
<!-- Completed Repairs Table -->
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i2 = 1; foreach ($completed_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i2++; ?></td>
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><span class="badge badge-success">Completed</span></td>
                                    <td>
                                        <a href="viewhistory.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
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
<!-- Replaced Assets Table -->
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i3 = 1; foreach ($replaced_assets as $row): ?>
                                <tr>
                                    <td><?php echo $i3++; ?></td>
                                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><span class="badge badge-info">Replaced</span></td>
                                    <td>
                                        <a href="viewhistory.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
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
<?php
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo '<div class="alert alert-danger">An error occurred while fetching the data. Please try again later.</div>';
}
?>