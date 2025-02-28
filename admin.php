<?php
session_start();
include 'db.php';


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

/// to fetch users name
$user_query = "SELECT email FROM users";
$user_result = $conn->query($user_query);


// Check if admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user'] !== "admin@example.com") {
    die("Access denied. Only admin can upload or delete files.");
}

// Define separate directories for Excel and PDF uploads
$excel_upload_dir = "uploads/excel/";
$pdf_upload_dir = "uploads/pdf/";

// Ensure that these directories exist before proceeding
if (!is_dir($excel_upload_dir)) {
    die("Error: Excel upload directory does not exist.");
}

if (!is_dir($pdf_upload_dir)) {
    die("Error: PDF upload directory does not exist.");
}

// Function to check if user exists
function userExists($conn, $user_email) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0; // Returns true if user exists
}


// Handle Excel file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_excel'])) {
    if (!empty($_FILES["excel_file"]["name"])) {
        $file_name = basename($_FILES["excel_file"]["name"]);
        $target_file = $excel_upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $user_email = $_POST['user_email'];
        $month_year = $_POST['month_year'];

        // Check if user exists
        if (!userExists($conn, $user_email)) {
            $_SESSION['success_message'] = "Error: User does not exist.";
        } else {
            // Check if file already exists for the user and month
            $check_stmt = $conn->prepare("SELECT id FROM excel_files WHERE user_email = ? AND file_name = ? AND month_year = ?");
            $check_stmt->bind_param("sss", $user_email, $file_name, $month_year);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $_SESSION['success_message'] = "Duplicate entry! The Excel file already exists for this user and month.";
            } else {
                $allowed_types = array("xls", "xlsx", "csv");
                if (!in_array($file_type, $allowed_types)) {
                    $_SESSION['success_message'] = "Only Excel files (xls, xlsx, csv) are allowed.";
                } elseif (move_uploaded_file($_FILES["excel_file"]["tmp_name"], $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO excel_files (user_email, file_name, month_year) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $user_email, $file_name, $month_year);

                    if ($stmt->execute()) {
                        $_SESSION['uploaded_user'] = $user_email; // Store username
                        $_SESSION['success_message'] = "Excel file uploaded successfully for <strong>$user_email</strong>!";
                    } else {
                        $_SESSION['success_message'] = "Database error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $_SESSION['success_message'] = "Error uploading Excel file.";
                }
            }
            $check_stmt->close();
        }
    } else {
        $_SESSION['success_message'] = "Please select an Excel file.";
    }
    header("Location: admin.php");
    exit();
}

// Handle PDF file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_pdf'])) {
    if (!empty($_FILES["pdf_file"]["name"])) {
        $file_name = basename($_FILES["pdf_file"]["name"]);
        $target_file = $pdf_upload_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $user_email = $_POST['user_email_pdf'];
        $month_year_pdf = $_POST['month_year_pdf'];

        // Check if user exists
        if (!userExists($conn, $user_email)) {
            $_SESSION['success_message'] = "Error: User does not exist.";
        } else {
            // Check if file already exists for the user and month
            $check_stmt = $conn->prepare("SELECT id FROM pdf_files WHERE user_email = ? AND file_name = ? AND month_year = ?");
            $check_stmt->bind_param("sss", $user_email, $file_name, $month_year_pdf);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $_SESSION['success_message'] = "Duplicate entry! The PDF file already exists for this user and month.";
            } else {
                if ($file_type !== "pdf") {
                    $_SESSION['success_message'] = "Only PDF files are allowed.";
                } elseif (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO pdf_files (user_email, file_name, month_year) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $user_email, $file_name, $month_year_pdf);

                    if ($stmt->execute()) {
                        $_SESSION['uploaded_user'] = $user_email; // Store username
                        $_SESSION['success_message'] = "PDF file uploaded successfully for <strong>$user_email</strong>!";
                    } else {
                        $_SESSION['success_message'] = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $_SESSION['success_message'] = "Error uploading PDF file.";
                }
            }
            $check_stmt->close();
        }
    } else {
        $_SESSION['success_message'] = "Please select a PDF file.";
    }
    header("Location: admin.php");
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
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a  class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"> <img src="img/PD.png"  style="width: 65px; height: 60px;"> PixilDigi </h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0">Gurjot Singh</h6>
                        <span>Admin</span>
                    </div>
                </div>
                
                <div class="navbar-nav w-100">
                    <a href="admin.php" class="nav-item nav-link"><i class="fa fa-cloud-upload-alt"></i>UPLOAD FILES</a>
                    <a href="add_user.php" class="nav-item nav-link" > <i class="fas fa-user-plus"></i> ADD USER</a>
                    <a href="delete_excel.php" class="nav-item nav-link" > <i class="fas fa-trash-alt"></i> Delete Excel DATA</a>
                    <a href="delete_credit.php" class="nav-item nav-link" > <i class="fas fa-trash-alt"></i> Delete Credit Note</a>
                    
                   
                </div> 
                
            </nav>
        </div>
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><img src="img/PD.png"  style="width: 65px; height: 60px;"></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
               <!-- <form class="d-none d-md-flex ms-4">
                    <input class="form-control bg-dark border-0" type="search" placeholder="Search">
                </form> -->
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                       
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
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


           <!-- Table Start -->
        <div class="container-fluid pt-4 px-4">
           <div class="row g-4">
            <div class="col-12">
                <div class="bg-secondary rounded h-100 p-4">
                    <h6 class="mb-4">Upload Excel Data Of Users</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">User Name</th>
                                    <th scope="col">Month</th>
                                    <th scope="col">Excel File</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <th scope="row">1</th>
                                        <td>  <select name="user_email" class="form-select" required>
                                            <option value="">Select User</option>
                                            <?php while ($row = $user_result->fetch_assoc()) { ?>
                                                <option value="<?php echo htmlspecialchars($row['email']); ?>">
                                                    <?php echo htmlspecialchars(explode('@', $row['email'])[0]); ?>
                                              </option>
                                            <?php } ?>
                                        </select></td>
                                        <td><input type="month" name="month_year" required></td>
                                        <td><input type="file" name="excel_file"   required></td>
                                        <td><button type="submit" class="btn btn-primary py-2 w-100 mb-4" name="upload_excel">Upload Excel</button></td>
                                    </form>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PDF Upload Table -->
            <div class="col-12">
                <div class="bg-secondary rounded h-100 p-4">
                    <h6 class="mb-4">Upload Credit Note Of Users</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">User Name</th>
                                    <th scope="col">Month</th>
                                    <th scope="col">PDF File</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <form action="" method="post" enctype="multipart/form-data">
                                        <th scope="row">1</th>
                                        <td><select name="user_email_pdf" class="form-select" required>
                                            <option value="">Select User</option>
                                            <?php
                                            // Reset and re-run query for the PDF section
                                            $user_result = $conn->query($user_query);
                                            while ($row = $user_result->fetch_assoc()) { ?>
                                                <option value="<?php echo htmlspecialchars($row['email']); ?>">
                                                   <?php echo htmlspecialchars(explode('@', $row['email'])[0]); ?>
                                                </option>
                                            <?php } ?>
                                        </select></td>
                                        <td><input type="month" name="month_year_pdf" required></td>
                                        <td> <input type="file" name="pdf_file"  required></td>
                                        <td><button type="submit" class="btn btn-primary py-2 w-100 mb-4" name="upload_pdf">Upload PDF</button></td>
                                    </form>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
            
           
   

    
    <?php if (isset($_SESSION['success_message']) && isset($_SESSION['user'])): ?>
    <div class="container-fluid px-4">
        <div id="success-alert" class="alert alert-success text-center">
            <?php 
                echo $_SESSION['success_message'] ;
                unset($_SESSION['success_message']); // Clear message after displaying it once
                unset($_SESSION['uploaded_user']); // Clear username session variable
            ?>
        </div>
    </div>

    <!-- JavaScript to Auto-hide the Message -->
    <script>
        setTimeout(function() {
            var alertBox = document.getElementById("success-alert");
            if (alertBox) {
                alertBox.style.transition = "opacity 0.5s ease";
                alertBox.style.opacity = "0";
                setTimeout(function() {
                    alertBox.style.display = "none";
                }, 500); // Extra delay for smooth transition
            }
        }, 3500); // Hide after 3.5 seconds
    </script>
<?php endif; ?>


    

          
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="#">PixilDigi Group</a>, All Right Reserved. 
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            <!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
                            Designed By <a >Hardeep Singh Pabla</a>
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
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>






