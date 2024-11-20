<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lawyer_id = isset($_POST['lawyer_id']) ? intval($_POST['lawyer_id']) : 0;
    $user_id = $_SESSION['id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // Validate input
    if ($lawyer_id > 0 && !empty($appointment_date) && !empty($appointment_time)) {
        // Insert appointment into database
        $sql = "INSERT INTO Appointments (user_id, lawyer_id, appointment_date, appointment_time, notes, status) 
                VALUES (?, ?, ?, ?, ?, 'scheduled')";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iisss", $user_id, $lawyer_id, $appointment_date, $appointment_time, $notes);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Appointment booked successfully!";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Invalid input. Please check your details and try again.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Lawyer Booking App</title>
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
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="search_lawyers.php">Search Lawyers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-4">Book Appointment</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <p class="text-center">Your appointment has been booked successfully.</p>
                        <div class="text-center mt-3">
                            <a href="index.php" class="btn btn-primary">Back to Home</a>
                        </div>
                    <?php else: ?>
                        <p class="text-center">There was an error booking your appointment. Please try again or contact support.</p>
                        <div class="text-center mt-3">
                            <a href="javascript:history.back()" class="btn btn-primary">Go Back</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
