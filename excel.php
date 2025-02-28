<?php
require 'vendor/autoload.php'; // Ensure PhpSpreadsheet is installed via Composer

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = '/uploads/maindata/sample.xlsx';

// Load the Excel file
$spreadsheet = IOFactory::load($filePath);

// Get all sheet names
$sheetNames = $spreadsheet->getSheetNames();

// Iterate through each sheet and fetch cell B3
echo "<pre>";
foreach ($sheetNames as $sheetIndex => $sheetName) {
    $sheet = $spreadsheet->getSheet($sheetIndex);
    $cellValue = $sheet->getCell('B3')->getValue();
    echo "Sheet: $sheetName - B3 Value: $cellValue\n";
}
echo "</pre>";
?>
