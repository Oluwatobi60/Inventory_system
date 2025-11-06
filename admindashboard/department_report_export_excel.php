<?php
require "include/config.php";
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Handle search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // Fetch all department/floor asset allocation data
    $sql = "SELECT department, floor, asset_name, quantity, category 
            FROM staff_table 
            WHERE (:search = '' OR department LIKE :search OR floor LIKE :search OR asset_name LIKE :search)
            ORDER BY department, floor, asset_name";
    
    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create new spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Department Floor Report');

    // Set header style
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 12
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1E40AF']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];

    // Set title
    $sheet->setCellValue('A1', 'Department/Floor Asset Allocation Report');
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => [
            'bold' => true,
            'size' => 16,
            'color' => ['rgb' => '1E40AF']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    // Add generation info
    $sheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));
    if (!empty($search)) {
        $sheet->setCellValue('A3', 'Search Filter: ' . $search);
        $headerRow = 5;
    } else {
        $headerRow = 4;
    }

    // Set headers
    $headers = ['S/N', 'Department', 'Floor', 'Asset Name', 'Category', 'Quantity'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $headerRow, $header);
        $col++;
    }
    
    // Apply header style
    $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray($headerStyle);

    // Add data rows
    $rowNum = $headerRow + 1;
    $sn = 1;
    
    foreach ($rows as $row) {
        $sheet->setCellValue('A' . $rowNum, $sn++);
        $sheet->setCellValue('B' . $rowNum, $row['department']);
        $sheet->setCellValue('C' . $rowNum, $row['floor']);
        $sheet->setCellValue('D' . $rowNum, $row['asset_name']);
        $sheet->setCellValue('E' . $rowNum, $row['category']);
        $sheet->setCellValue('F' . $rowNum, $row['quantity']);
        $rowNum++;
    }

    // Add summary statistics
    $summaryRow = $rowNum + 2;
    $sheet->setCellValue('A' . $summaryRow, 'Summary Statistics:');
    $sheet->getStyle('A' . $summaryRow)->applyFromArray([
        'font' => ['bold' => true, 'size' => 12]
    ]);

    $summaryRow++;
    $sheet->setCellValue('A' . $summaryRow, 'Total Records: ' . count($rows));
    
    $summaryRow++;
    $sheet->setCellValue('A' . $summaryRow, 'Total Assets: ' . array_sum(array_column($rows, 'quantity')));

    // Count unique departments and floors
    $unique_departments = array_unique(array_column($rows, 'department'));
    $unique_floors = array_unique(array_column($rows, 'floor'));
    
    $summaryRow++;
    $sheet->setCellValue('A' . $summaryRow, 'Unique Departments: ' . count($unique_departments));
    
    $summaryRow++;
    $sheet->setCellValue('A' . $summaryRow, 'Unique Floors: ' . count($unique_floors));

    // Add category-based summary without altering existing code
    $summaryRow += 2;
    $sheet->setCellValue('A' . $summaryRow, 'Assets by Category:');
    $sheet->getStyle('A' . $summaryRow)->applyFromArray([
        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E40AF']]
    ]);

    // Group assets by category and calculate totals
    $categoryTotals = [];
    foreach ($rows as $row) {
        $category = $row['category'] ?? 'Uncategorized';
        if (!isset($categoryTotals[$category])) {
            $categoryTotals[$category] = 0;
        }
        $categoryTotals[$category] += (int)$row['quantity'];
    }

    // Sort categories alphabetically
    ksort($categoryTotals);

    // Display category totals
    foreach ($categoryTotals as $category => $total) {
        $summaryRow++;
        $sheet->setCellValue('A' . $summaryRow, '• ' . $category . ':');
        $sheet->setCellValue('B' . $summaryRow, $total . ' assets');
        $sheet->getStyle('A' . $summaryRow)->applyFromArray([
            'font' => ['size' => 11]
        ]);
        $sheet->getStyle('B' . $summaryRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11]
        ]);
    }

    // Auto-size columns
    foreach(range('A', 'F') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Set row height for header
    $sheet->getRowDimension($headerRow)->setRowHeight(25);

    // Add borders to data area
    if (count($rows) > 0) {
        $lastRow = $headerRow + count($rows);
        $sheet->getStyle('A' . $headerRow . ':F' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
    }

    // Generate filename with current date and search filter
    $filename = 'Department_Floor_Report_' . date('Y-m-d_H-i-s');
    if (!empty($search)) {
        $filename .= '_filtered';
    }
    $filename .= '.xlsx';

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Save to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Clean up
    $spreadsheet->disconnectWorksheets();

} catch (Exception $e) {
    // Handle errors
    error_log("Export error: " . $e->getMessage());
    
    // Redirect back with error message
    $error_msg = urlencode("Export failed: " . $e->getMessage());
    header("Location: department_reporttable.php?search=" . urlencode($search) . "&error=" . $error_msg);
    exit;
}
?>