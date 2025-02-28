<?php


require 'vendor/autoload.php'; // Ensure PhpSpreadsheet is installed

use PhpOffice\PhpSpreadsheet\IOFactory;

// Load Excel file
$filePath = "uploads/maindata/sample.xlsx"; // Change this to the actual file path
$spreadsheet = IOFactory::load($filePath);
$sheetNames = $spreadsheet->getSheetNames();



$user_email = $_SESSION['user']; // Get logged-in user's email
// Fetch user details
$user_stmt = $conn->prepare("SELECT email, label_name FROM users WHERE email = ?");
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();
$user_name = $user_row['email'] ?? 'User'; // Default to 'User' if name is not found
$label_name = $user_row['label_name'] ?? 'Unknown'; // Default to 'Unknown' if label_name is missing
$currentSheetName = $sheetNames[0]; // Assume first sheet as the current sheet
$currentData = [];
$allOtherData = [];


// Process each sheet
foreach ($sheetNames as $sheetName) {
    $sheet = $spreadsheet->getSheetByName($sheetName);
    $data = $sheet->toArray(null, true, true, true);

    foreach ($data as $row) {
        if (isset($row['A']) && strtolower($row['A']) == strtolower($user_email)) {
            $rowData = [
                'sheet' => $sheetName,
                'revenue' => $row['B'],
                'stream' => $row['C'],
                'revenue_paid' => $row['D'],
               
            ];
            if ($sheetName === $currentSheetName) {
                $currentData = $rowData; // Store current sheet data
            } else {
                $allOtherData[] = $rowData; // Store other sheet data
            }
        }
    }
}



?>
