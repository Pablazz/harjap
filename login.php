<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user['email'];

            // Redirect based on user role
            if ($user['email'] === "admin@example.com") {
                header("Location: admin.php"); // Admin goes to admin.php
            } else {
                header("Location: index.php"); // Regular users go to index.php
            }
            exit();
        }
    }
    
    $error = "Invalid credentials.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PixilDigi - Login</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
   <link href="css/bootstrap.min.css" rel="stylesheet">
   
     <!-- Google Web Fonts -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
   
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    

    <!-- Template Stylesheet -->
    <link href="css/styleindex.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
            <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="text-primary" style="display: flex; flex-direction: row;  justify-content: center; text-align: center; style='font-size:30px">
                            <img src="img/PD.png" alt="Logo" style="width: 50px; height: 50px;"> Pixil Digi Group
                        </h3>
                        
                    </div>
                    <div style="display: flex; flex-direction: column;  justify-content: center; text-align: center;">
                        <h3>Login</h3>
</div>
                    <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
                    <form method="post">
                        <div class="form-floating mb-3">
                            <input type="text" name="email" class="form-control" placeholder="name@example.com" required>
                            <label>Email address</label>
                        </div>
                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <label>Password</label>
                        </div>
                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Sign In</button>
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                            <a href= "mailto:hello@pixildigigroup.com" style='font-size:13px'>hello@pixildigigroup.com</a>
                         <a href="tel:+91-84980-00082"  style='font-size:13px'>
        <i class="fas fa-phone-alt" style='font-size:13px';color:'blue';></i>   +91-84980-00082</a>
</div>
                    </form>
                   
                </div>
                
            </div>
        </div>
    </div>
</body>
<!-- Footer Start -->
 

    <!-- Footer -->
    <footer class="footer" style="background: rgba(0, 0, 0, 0.4);">
    <div style="display: flex; flex-direction: row; align-items: center; justify-content: center; text-align: center;"> <a style="margin-right: 10px;">Follow Us On</a>              
    <!-- Social Media Links -->
    <a href="https://wa.me/918498000082"  target="_blank" class="whatsapp" style="margin-right: 10px;" >
                           <i  class="fab fa-whatsapp"></i>
    </a>
    <a href="https://www.facebook.com/Pixildigigroup"  target="_blank" class="facebook" style="margin-right: 10px;">
                            <i  class="fab fa-facebook"></i>
                            </a>
                            <a href="https://www.instagram.com/pixildigigroup/" target="_blank" class="instagram"  style="margin-right: 100px;">
                            <i class="fab fa-instagram"></i>
                        </a>
    <a href="" style="margin-left: 10px; "> Designed by: Hardeep Singh Pabla</a> </div>
</footer>

            <!-- Footer End -->
</html>
