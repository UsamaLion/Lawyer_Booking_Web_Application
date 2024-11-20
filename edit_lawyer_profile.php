<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a lawyer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "lawyer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$lawyer_id = null;
$username = $email = $specialization = $city = $bio = "";
$username_err = $email_err = $specialization_err = $city_err = $bio_err = "";

// Fetch lawyer's details
$sql = "SELECT l.*, u.username, u.email 
        FROM Lawyers l
        JOIN Users u ON l.user_id = u.id
        WHERE l.user_id = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $lawyer = mysqli_fetch_assoc($result);
        $lawyer_id = $lawyer['id'];
        $username = $lawyer['username'];
        $email = $lawyer['email'];
        $specialization = $lawyer['specialization'];
        $city = $lawyer['city'];
        $bio = $lawyer['bio'];
    }
    mysqli_stmt_close($stmt);
}

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate specialization
    if(empty(trim($_POST["specialization"]))){
        $specialization_err = "Please enter a specialization.";
    } else {
        $specialization = trim($_POST["specialization"]);
    }
    
    // Validate city
    if(empty(trim($_POST["city"]))){
        $city_err = "Please enter a city.";
    } else {
        $city = trim($_POST["city"]);
    }
    
    // Validate bio
    if(empty(trim($_POST["bio"]))){
        $bio_err = "Please enter a bio.";
    } else {
        $bio = trim($_POST["bio"]);
    }
    
    // Check input errors before updating the database
    if(empty($username_err) && empty($email_err) && empty($specialization_err) && empty($city_err) && empty($bio_err)){
        // Prepare an update statement
        $sql = "UPDATE Users u
                JOIN Lawyers l ON u.id = l.user_id
                SET u.username = ?, u.email = ?, l.specialization = ?, l.city = ?, l.bio = ?
                WHERE u.id = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_username, $param_email, $param_specialization, $param_city, $param_bio, $param_id);
            
            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_specialization = $specialization;
            $param_city = $city;
            $param_bio = $bio;
            $param_id = $user_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to lawyer dashboard
                header("location: lawyer_dashboard.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Lawyer Booking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Lawyer Booking App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="lawyer_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-4">Edit Profile</h1>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="specialization" class="form-label">Specialization</label>
                    <input type="text" name="specialization" class="form-control <?php echo (!empty($specialization_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $specialization; ?>">
                    <span class="invalid-feedback"><?php echo $specialization_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" class="form-control <?php echo (!empty($city_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $city; ?>">
                    <span class="invalid-feedback"><?php echo $city_err; ?></span>
                </div>
                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea name="bio" class="form-control <?php echo (!empty($bio_err)) ? 'is-invalid' : ''; ?>" rows="4"><?php echo $bio; ?></textarea>
                    <span class="invalid-feedback"><?php echo $bio_err; ?></span>
                </div>
                <div class="mb-3">
                    <input type="submit" class="btn btn-primary" value="Update Profile">
                    <a class="btn btn-secondary" href="lawyer_dashboard.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>About Us</h5>
                <p>We connect clients with skilled lawyers across Pakistan.</p>
            </div>
            <div class="col-md-6">
                <h5>Contact</h5>
                <p>Email: info@lawyerbooking.pk</p>
                <p>Phone: +92 300 1234567</p>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; 2023 Lawyer Booking App. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
