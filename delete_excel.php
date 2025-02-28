<?php
session_start();
include 'db.php';

// Session Timeout (5 minutes)
$timeout_duration = 300;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: Login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user'] !== "admin@example.com") {
    die("Access denied. Only admin can manage files.");
}

// Preserve selected user across pagination
if (isset($_POST['selected_user'])) {
    $_SESSION['selected_user'] = $_POST['selected_user'];
}
$selected_user = isset($_SESSION['selected_user']) ? $_SESSION['selected_user'] : "";

// Pagination
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = 1;
$files = [];

if ($selected_user) {
    // Fetch total number of files
    $total_query = $conn->prepare("SELECT COUNT(*) as total FROM excel_files WHERE user_email = ?");
    $total_query->bind_param("s", $selected_user);
    $total_query->execute();
    $total_result = $total_query->get_result()->fetch_assoc();
    $total_files = $total_result['total'];
    $total_pages = ceil($total_files / $limit);
    $total_query->close();

    // Fetch Excel files
    $file_query = $conn->prepare("SELECT id, file_name FROM excel_files WHERE user_email = ? LIMIT ?, ?");
    $file_query->bind_param("sii", $selected_user, $offset, $limit);
    $file_query->execute();
    $result = $file_query->get_result();
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
    $file_query->close();
}

// Handle file deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_file'])) {
    $file_id = $_POST['file_id'];
    $file_name = $_POST['file_name'];

    // Delete from DB
    $delete_stmt = $conn->prepare("DELETE FROM excel_files WHERE id = ?");
    $delete_stmt->bind_param("i", $file_id);
    
    if ($delete_stmt->execute()) {
        unlink("uploads/excel/" . $file_name); // Delete file from server
        $_SESSION['success_message'] = "Excel file deleted successfully!";
    } else {
        $_SESSION['success_message'] = "Error deleting file.";
    }
    
    $delete_stmt->close();
    header("Location: delete_excel.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>PIXILDIGI -AdminLogin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.minimum.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/styleindex.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary">
                        <img src="img/PD.png" style="width: 65px; height: 60px;"> PixilDigi
                    </h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="img/user.jpg" alt="User" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">Gurjot Singh</h6>
                        <span>Admin</span>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="admin.php" class="nav-item nav-link"><i class="fa fa-cloud-upload-alt"></i> UPLOAD FILES</a>
                    <a href="add_user.php" class="nav-item nav-link"><i class="fas fa-user-plus"></i> ADD USER</a>
                    <a href="delete_excel.php" class="nav-item nav-link"><i class="fas fa-trash-alt"></i> Delete Excel DATA</a>
                    <a href="delete_credit.php" class="nav-item nav-link"><i class="fas fa-trash-alt"></i> Delete Credit Note</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0">
                        <img src="img/PD.png" style="width: 65px; height: 60px;">
                    </h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/user.jpg" alt="User" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex">Gurjot Singh</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">My Profile</a>
                            <a href="#" class="dropdown-item">Settings</a>
                            <a href="login.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Delete Data Section -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                        <h3 class="text-center text-primary">Delete Excel Data</h3>
                            <?php if (isset($_SESSION['success_message'])): ?>
                                <div class="alert alert-success">
                                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                                </div>
                            <?php endif; ?>
                            <!-- User Selection -->
                            <form method="post" class="mb-3">
                                <label>Select User:</label>
                                <select name="selected_user" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Choose User --</option>
                                    <?php
                                    $user_query = "SELECT email FROM users";
                                    $user_result = $conn->query($user_query);
                                    while ($row = $user_result->fetch_assoc()) { ?>
                                        <option value="<?php echo htmlspecialchars($row['email']); ?>" <?php echo ($selected_user == $row['email']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(explode('@', $row['email'])[0]); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </form>
                            <!-- Excel Files Table -->
                            <?php if ($selected_user && count($files) > 0): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Excel File</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($files as $index => $file): ?>
                                            <tr>
                                                <td><?php echo $index + 1 + $offset; ?></td>
                                                <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                                                <td>
                                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                        <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                                        <input type="hidden" name="file_name" value="<?php echo htmlspecialchars($file['file_name']); ?>">
                                                        <button type="submit" name="delete_file" class="btn btn-danger btn-sm">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php elseif ($selected_user): ?>
                                <p class="text-warning">No Excel files found for this user.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">PixilDigi Group</a>, All Rights Reserved.
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            Designed By <a>Hardeep Singh Pabla</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    </div>
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
</body>


</html>





