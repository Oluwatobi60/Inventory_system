<?php
 require "../admindashboard/include/config.php";
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


// Get start and end date filters from request
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;


$spreadsheet = new Spreadsheet();

// Helper function to write data to a sheet
function writeSheet($spreadsheet, $sheetIndex, $title, $headers, $dataRows) {
    $sheet = ($sheetIndex === 0) ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet($sheetIndex);
    $sheet->setTitle($title);
    // Set headers
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }
    // Write data
    $rowNum = 2;
    foreach ($dataRows as $row) {
        $col = 'A';
        foreach ($headers as $key) {
            $sheet->setCellValue($col . $rowNum, isset($row[$key]) ? $row[$key] : '');
            $col++;
        }
        $rowNum++;
    }
    // Auto size columns
    foreach(range('A', chr(ord('A') + count($headers) - 1)) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// 1. Completed Asset
$where1 = "WHERE completed = 1";
if ($start_date) {
    $where1 .= " AND DATE(completed_date) >= :start_date1";
}
if ($end_date) {
    $where1 .= " AND DATE(completed_date) <= :end_date1";
}
$stmt1 = $conn->prepare("SELECT * FROM completed_asset $where1 ORDER BY id DESC");
if ($start_date) {
    $stmt1->bindValue(':start_date1', $start_date);
}
if ($end_date) {
    $stmt1->bindValue(':end_date1', $end_date);
}
$stmt1->execute();
$completedRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);
$completedHeaders = array_keys($completedRows ? $completedRows[0] : ['id'=>'']);
writeSheet($spreadsheet, 0, 'Completed Asset', $completedHeaders, $completedRows);

// 2. Withdrawn Asset
$where2 = "WHERE status = 1 OR status = 0";
if ($start_date) {
    $where2 .= " AND DATE(withdrawn_date) >= :start_date2";
}
if ($end_date) {
    $where2 .= " AND DATE(withdrawn_date) <= :end_date2";
}
$stmt2 = $conn->prepare("SELECT * FROM withdrawn_asset $where2 ORDER BY id DESC");
if ($start_date) {
    $stmt2->bindValue(':start_date2', $start_date);
}
if ($end_date) {
    $stmt2->bindValue(':end_date2', $end_date);
}
$stmt2->execute();
$withdrawnRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
$withdrawnHeaders = array_keys($withdrawnRows ? $withdrawnRows[0] : ['id'=>'']);
writeSheet($spreadsheet, 1, 'Withdrawn Asset', $withdrawnHeaders, $withdrawnRows);

// 3. Asset Replacement Log
$where3 = "WHERE replaced = 1";
if ($start_date) {
    $where3 .= " AND DATE(replaced_at) >= :start_date3";
}
if ($end_date) {
    $where3 .= " AND DATE(replaced_at) <= :end_date3";
}
$stmt3 = $conn->prepare("SELECT * FROM asset_replacement_log $where3 ORDER BY id DESC");
if ($start_date) {
    $stmt3->bindValue(':start_date3', $start_date);
}
if ($end_date) {
    $stmt3->bindValue(':end_date3', $end_date);
}
$stmt3->execute();
$replacementRows = $stmt3->fetchAll(PDO::FETCH_ASSOC);
$replacementHeaders = array_keys($replacementRows ? $replacementRows[0] : ['id'=>'']);
writeSheet($spreadsheet, 2, 'Asset Replacement Log', $replacementHeaders, $replacementRows);

// 4. Repair Asset
$where4 = "WHERE status = 'Under Repair'";
if ($start_date) {
    $where4 .= " AND DATE(report_date) >= :start_date4";
}
if ($end_date) {
    $where4 .= " AND DATE(report_date) <= :end_date4";
}
$stmt4 = $conn->prepare("SELECT * FROM repair_asset $where4 ORDER BY id DESC");
if ($start_date) {
    $stmt4->bindValue(':start_date4', $start_date);
}
if ($end_date) {
    $stmt4->bindValue(':end_date4', $end_date);
}
$stmt4->execute();
$repairRows = $stmt4->fetchAll(PDO::FETCH_ASSOC);
$repairHeaders = array_keys($repairRows ? $repairRows[0] : ['id'=>'']);
writeSheet($spreadsheet, 3, 'Repair Asset', $repairHeaders, $repairRows);

// Create Excel file
$writer = new Xlsx($spreadsheet);

// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="asset_history.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;