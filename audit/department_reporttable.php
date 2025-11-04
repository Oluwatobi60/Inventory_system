<?php

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination logic
$per_page_options = [5, 15, 50, 'all'];
$per_page = isset($_GET['per_page']) && in_array($_GET['per_page'], array_map('strval', $per_page_options)) ? $_GET['per_page'] : 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Count total records
$count_sql = "SELECT COUNT(*) FROM staff_table WHERE (:search = '' OR department LIKE :search OR floor LIKE :search OR asset_name LIKE :search)";
$count_stmt = $conn->prepare($count_sql);
$search_param = "%$search%";
$count_stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
$count_stmt->execute();
$total_records = $count_stmt->fetchColumn();

// Fetch assets allocated to departments/floors
if ($per_page === 'all') {
    $sql = "SELECT department, floor, asset_name, quantity 
            FROM staff_table 
            WHERE (:search = '' OR department LIKE :search OR floor LIKE :search OR asset_name LIKE :search)
            ORDER BY department, floor, asset_name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_pages = 1;
    $page = 1;
} else {
    $offset = ($page - 1) * intval($per_page);
    $sql = "SELECT department, floor, asset_name, quantity 
            FROM staff_table 
            WHERE (:search = '' OR department LIKE :search OR floor LIKE :search OR asset_name LIKE :search)
            ORDER BY department, floor, asset_name
            LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $stmt->bindValue(':limit', intval($per_page), PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_pages = ceil($total_records / intval($per_page));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/isalu-logo.png">
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
    <title>Department/Floor Asset Report</title>
    <link rel="stylesheet" href="assets/libs/bootstrap/dist/css/bootstrap.min.css">
    <script>
    // Clear search field and submit form
    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('reportForm').submit();
    }
    // Change per_page and keep search value
    function changePerPage(sel) {
        document.getElementById('reportForm').submit();
    }
    </script>
</head>
<body>
<div class="container mt-5">
    <!-- <h2>Assets Allocated to Department/Floor</h2> -->
    <form method="get" class="mb-3 form-inline" id="reportForm">
        <input type="text" name="search" id="searchInput" class="form-control mb-2 mr-2" placeholder="Search by department, floor, or asset..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:350px;" autofocus>
        <button type="button" class="btn btn-secondary mb-2 mr-2" onclick="clearSearch()">Clear</button>
        <select name="per_page" class="form-control mb-2 mr-2" onchange="changePerPage(this)">
            <?php foreach ($per_page_options as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php echo ($per_page == $opt) ? 'selected' : ''; ?>>
                    <?php echo ($opt === 'all') ? 'All' : $opt; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary mb-2">Search</button>
    </form>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Department</th>
                <th>Floor</th>
                <th>Asset Name</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($rows): ?>
            <?php $sn = ($per_page === 'all') ? 1 : (($page - 1) * intval($per_page)) + 1; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td><?php echo htmlspecialchars($row['floor']); ?></td>
                    <td><?php echo htmlspecialchars($row['asset_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center">No records found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($per_page !== 'all' && $total_pages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item<?php echo ($i == $page) ? ' active' : ''; ?>">
                    <a class="page-link" href="?search=<?php echo urlencode($search); ?>&per_page=<?php echo $per_page; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
</body>
</html>