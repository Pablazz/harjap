<?php
session_start();
include 'db.php';
include 'session.php';





include 'session.php';  /// for dasboard data display
// Define session timeout duration (5 minutes)
$timeout_duration = 60; 

// Check if last activity is set
if (isset($_SESSION['LAST_ACTIVITY'])) {
    $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];
    if ($elapsed_time > $timeout_duration) {
        // Destroy session and redirect to login
        session_unset();
        session_destroy();
        header("Location: Login.php");
        exit();
    }
}

// Update last activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();


// Assuming the user is logged in and email is stored in session
if (!isset($_SESSION['user'])) {
    die("Access denied. Please login first.");
}

// Pagination logic
$limit = 6; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page number
$offset = ($page - 1) * $limit; // Calculate the offset

// Fetch records with limit and offset for the logged-in user
$stmt = $conn->prepare("SELECT * FROM excel_files WHERE user_email = ?  LIMIT ? OFFSET ?");
$stmt->bind_param("sii", $user_email, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();


// Fetch total records count
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM excel_files WHERE user_email = ?");
$count_stmt->bind_param("s", $user_email);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_row = $count_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $limit);



?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DarkPan - Bootstrap 5 Admin Template</title>
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
                <span class="sr-only">Loading...</span>
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
                        <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                    <i><?php echo strtoupper(htmlspecialchars($label_name)); ?></i> 
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="index.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="creditnote.php" class="nav-item nav-link"><i class="fas fa-dollar-sign"></i> Credit Note</a>
                    <a href="Reports.php" class="nav-item nav-link"><i class="fa fa-paperclip"></i> Reports</a>
                    <a href="revenue.php" class="nav-item nav-link"><i class="fa fa-dollar-sign"></i> Revenue</a>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                
            <a href="index.php" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><img src="img/PD.png"  style="width: 65px; height: 60px;"></h2>
                </a>
            <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex"><?php echo htmlspecialchars($user_name); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">My Profile</a>
                            <a href="#" class="dropdown-item">Settings</a>
                            <a href="Login.php" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Sale & Revenue Table Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-line fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Today Revenue</p>
                                <h6 class="mb-0">&#8377; <?php echo htmlspecialchars($currentData['revenue'] ?? 'N/A'); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-bar fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Total Streams</p>
                                <h6 class="mb-0"><?php echo htmlspecialchars($currentData['stream'] ?? 'N/A'); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="bg-secondary rounded d-flex align-items-center justify-content-between p-4">
                            <i class="fa fa-chart-area fa-3x text-primary"></i>
                            <div class="ms-3">
                                <p class="mb-2">Revenue Paid</p>
                                <h6 class="mb-0">&#8377; <?php echo htmlspecialchars($currentData['revenue_paid'] ?? 'N/A'); ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sale & Revenue Table End -->

            <!-- Table Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <h6 class="mb-4">Credit Notes</h6>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">S.No.</th>
                                            <th scope="col">File Name</th>
                                            <th scope="col">Month</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $serial_number = $offset + 1;
                                        while ($row = $result->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo $serial_number++; ?></td>
                                                <td>
                                                    <a href="uploads/excel/<?php echo htmlspecialchars($row['file_name']); ?>" download>
                                                        <?php echo htmlspecialchars($row['file_name']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('M-y', strtotime($row['month_year'])); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                    <!-- Pagination -->
                    <div class="mt-3 text-center">
                    <?php if ($page > 1) { ?>
                        <a class="btn btn-primary" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    <?php } ?>

                    <span class="mx-2">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>

                    <?php if ($page < $total_pages) { ?>
                        <a class="btn btn-primary" href="?page=<?php echo $page + 1; ?>">Next</a>
                    <?php } ?>
                </div>
                </div>
            </div>
            <!-- Table End -->
        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="js/main.js"></script>


  <!-- Footer -->
  <div class="container-fluid pt-4 px-4">
                <div class="row g-5">
                    <div class="col-sm-6 col-xl-3">
                    <div class="bg-secondary rounded d-flex justify-content-between p-4">
                        <footer class="footer">
                        &copy;  <a href="" style='font-size:13px' style="margin-right: 10px;">2025 PixilDigi Group</a>              
                               <!-- Social Media Links -->
                       <a href="https://wa.me/918498000082" style='font-size:13px' target="_blank" class="whatsapp" style="margin-right: 10px;">
                           <i  class="fab fa-whatsapp"></i>
                                </a>
                        <a href="https://www.facebook.com/Pixildigigroup" style='font-size:13px' target="_blank" class="facebook" style="margin-right: 10px;">
                            <i  class="fab fa-facebook"></i>
                            </a>
                            <a href="https://www.instagram.com/pixildigigroup/" target="_blank" class="instagram" style="font-size:13px; margin-right: 10px;">
                            <i class="fab fa-instagram"></i>
                        </a>
                      <a href="" style='font-size:13px' style="margin-right: 10px;">Designed by Hardeep Singh Pabla</a>  
                        </footer>
                    </div>
            
            </div>
       </div>
    </div>
            <!-- Footer End --> 
                    </body>     
</html>



   


<?php $stmt->close(); ?>


