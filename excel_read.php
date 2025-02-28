<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Path to your Excel file
$excelFilePath = "uploads/data.xlsx";

try {
    // Load the Excel file
    $spreadsheet = IOFactory::load($excelFilePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Get the highest row number
    $highestRow = $sheet->getHighestRow();

    // Read data from specific columns (e.g., A and B)
    echo "<table border='1'>";
    echo "<tr><th>Column A</th><th>Column B</th></tr>";

    for ($row = 2; $row <= $highestRow; $row++) { // Skip header (row 1)
        $colA = $sheet->getCell('A' . $row)->getValue();
        $colB = $sheet->getCell('B' . $row)->getValue();

        echo "<tr><td>$colA</td><td>$colB</td></tr>";
    }

    echo "</table>";
} catch (Exception $e) {
    die('Error loading file: ' . $e->getMessage());
}
?>
