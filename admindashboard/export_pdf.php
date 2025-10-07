<?php
require "../admindashboard/include/config.php";
require '../vendor/autoload.php';

// Use PDO for database connection
// Assuming $conn is a PDO instance, if not, create it:
if (!isset($conn) || !($conn instanceof PDO)) {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

// Initialize the WHERE clause
$where_clause = "WHERE 1=1";
$params = [];

// Add date filtering if dates are provided
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $where_clause .= " AND DATE(dateofpurchase) >= :start_date";
    $params[':start_date'] = $_GET['start_date'];
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $where_clause .= " AND DATE(dateofpurchase) <= :end_date";
    $params[':end_date'] = $_GET['end_date'];
}

$query = "SELECT reg_no, asset_name, description, category, dateofpurchase, quantity 
         FROM asset_table 
         $where_clause 
         ORDER BY reg_no ASC";

$stmt = $conn->prepare($query);
$stmt->execute($params);

use TCPDF as TCPDF;

// Create new PDF document with landscape orientation and custom page size
$pdf = new TCPDF('L', PDF_UNIT, 'A3', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Asset Management System');
$pdf->SetTitle('Asset History Report');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set smaller margins to maximize content space
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(TRUE, 5);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Column headers
$headers = array('Reg No.', 'Asset Name', 'Description', 'Category', 'Quantity', 'Requested By', 'Department', 'Request Date', 'Procurement', 'HOD');
// Adjusted widths to fill A3 landscape page (420mm width - 10mm total margins = 410mm available)
$width = array(25, 35, 50, 70, 35, 25, 45, 45, 35, 30, 30); 

// Create header row
$pdf->SetFillColor(52, 73, 94);
$pdf->SetTextColor(255);
$xPos = 5;
$yPos = 5;
foreach($headers as $key => $header) {
    $pdf->SetXY($xPos, $yPos);
    $pdf->Cell($width[$key], 12, $header, 1, 0, 'C', true);
    $xPos += $width[$key];
}

// Reset text color to black for data
$pdf->SetTextColor(0);

// Fetch data from database
// Helper to render a section
function renderSection($pdf, $title, $headers, $rows, $width, &$yPos) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetXY(5, $yPos);
    $pdf->Cell(array_sum($width), 10, $title, 0, 1, 'L');
    $yPos += 10;
    $pdf->SetFont('helvetica', '', 10);
    // Header row
    $pdf->SetFillColor(52, 73, 94);
    $pdf->SetTextColor(255);
    $xPos = 5;
    foreach($headers as $key => $header) {
        $pdf->SetXY($xPos, $yPos);
        $pdf->Cell($width[$key], 12, $header, 1, 0, 'C', true);
        $xPos += $width[$key];
    }
    $yPos += 12;
    $pdf->SetTextColor(0);
    // Data rows
    foreach ($rows as $row) {
        $xPos = 5;
        foreach ($headers as $key) {
            $pdf->SetXY($xPos, $yPos);
            $pdf->Cell($width[$key], 12, isset($row[$key]) ? $row[$key] : '', 1, 0, 'C', false);
            $xPos += $width[$key];
        }
        $yPos += 12;
    }
    $yPos += 5;
}

$yPos = 17;

// 1. Completed Asset
$headers1 = ['id','asset_id','quantity','completed_date','reg_no','asset_name','department','reported_by'];
$width1 = array(10,15,15,30,20,30,30,30);
$stmt1 = $conn->prepare("SELECT id, asset_id, quantity, completed_date, reg_no, asset_name, department, reported_by FROM completed_asset WHERE completed = 1 ORDER BY id DESC");
$stmt1->execute();
$rows1 = $stmt1->fetchAll(PDO::FETCH_ASSOC);
renderSection($pdf, 'Completed Asset', $headers1, $rows1, $width1, $yPos);

// 2. Withdrawn Asset
$headers2 = ['id','asset_id','qty','withdrawn_date','reg_no','asset_name','department','withdrawn_by'];
$width2 = array(10,15,15,30,20,30,30,30);
$stmt2 = $conn->prepare("SELECT id, asset_id, qty, withdrawn_date, reg_no, asset_name, department, withdrawn_by FROM withdrawn_asset WHERE status = 1 OR status = 0 ORDER BY id DESC");
$stmt2->execute();
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
renderSection($pdf, 'Withdrawn Asset', $headers2, $rows2, $width2, $yPos);

// 3. Asset Replacement Log
$headers3 = ['id','asset_id','replaced_quantity','replaced_at','reg_no','asset_name','department'];
$width3 = array(10,15,20,30,20,30,30);
$stmt3 = $conn->prepare("SELECT id, asset_id, replaced_quantity, replaced_at, reg_no, asset_name, department FROM asset_replacement_log WHERE replaced = 1 ORDER BY id DESC");
$stmt3->execute();
$rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
renderSection($pdf, 'Asset Replacement Log', $headers3, $rows3, $width3, $yPos);

// 4. Repair Asset
$headers4 = ['id','asset_id','quantity','report_date','reg_no','asset_name','department','reported_by','status'];
$width4 = array(10,15,15,30,20,30,30,30,20);
$stmt4 = $conn->prepare("SELECT id, asset_id, quantity, report_date, reg_no, asset_name, department, reported_by, status FROM repair_asset WHERE status = 'Under Repair' ORDER BY id DESC");
$stmt4->execute();
$rows4 = $stmt4->fetchAll(PDO::FETCH_ASSOC);
renderSection($pdf, 'Repair Asset', $headers4, $rows4, $width4, $yPos);
    foreach($rowData as $key => $value) {
        // Add extra padding to height calculation
        $cellHeight = $pdf->getStringHeight($width[$key], $value) + 2;
        $maxHeight = max($maxHeight, $cellHeight);
    }

    // Second pass: print cells with calculated height
    foreach($rowData as $key => $value) {
        $pdf->SetXY($xPos, $yPos);
        // Use MultiCell for text that might need to wrap
        $pdf->MultiCell($width[$key], $maxHeight, $value, 1, 'C', false, 0);
        $xPos += $width[$key];
    }
    
    $yPos += $maxHeight;

    // Add a new page if we're near the bottom
    if ($yPos > ($pdf->getPageHeight() - 15)) {
        $pdf->AddPage();
        $yPos = 5;
        
        // Reprint headers on new page
        $xPos = 5;
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255);
        foreach($headers as $key => $header) {
            $pdf->SetXY($xPos, $yPos);
            $pdf->Cell($width[$key], 12, $header, 1, 0, 'C', true);
            $xPos += $width[$key];
        }
        $pdf->SetTextColor(0);
        $yPos = 17;
    }
}

// Output PDF
$pdf->Output('asset_history.pdf', 'D');