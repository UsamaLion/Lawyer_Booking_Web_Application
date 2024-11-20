<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}

// Fetch some basic statistics
$stats = array();

// Total number of users
$sql = "SELECT COUNT(*) as total_users FROM Users WHERE role = 'user'";
$result = mysqli_query($conn, $sql);
$stats['total_users'] = mysqli_fetch_assoc($result)['total_users'];

// Total number of lawyers
$sql = "SELECT COUNT(*) as total_lawyers FROM Users WHERE role = 'lawyer'";
$result = mysqli_query($conn, $sql);
$stats['total_lawyers'] = mysqli_fetch_assoc($result)['total_lawyers'];

// Total number of appointments
$sql = "SELECT COUNT(*) as total_appointments FROM Appointments";
$result = mysqli_query($conn, $sql);
$stats['total_appointments'] = mysqli_fetch_assoc($result)['total_appointments'];

// Total number of blog posts
$sql = "SELECT COUNT(*) as total_blog_posts FROM BlogPosts";
$result = mysqli_query($conn, $sql);
$stats['total_blog_posts'] = mysqli_fetch_assoc($result)['total_blog_posts'];

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lawyer Booking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .dashboard-card {
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
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
                    <a class="nav-link" href="blog.php">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-4">Admin Dashboard</h1>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo $stats['total_users']; ?></p>
                    <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Lawyers</h5>
                    <p class="card-text display-4"><?php echo $stats['total_lawyers']; ?></p>
                    <a href="manage_lawyers.php" class="btn btn-primary">Manage Lawyers</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Appointments</h5>
                    <p class="card-text display-4"><?php echo $stats['total_appointments']; ?></p>
                    <a href="manage_appointments.php" class="btn btn-primary">Manage Appointments</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Total Blog Posts</h5>
                    <p class="card-text display-4"><?php echo $stats['total_blog_posts']; ?></p>
                    <a href="blog.php" class="btn btn-primary">Manage Blog</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><a href="add_blog_post.php">Add New Blog Post</a></li>
                        <li class="list-group-item"><a href="manage_reviews.php">Manage Reviews</a></li>
                        <li class="list-group-item"><a href="manage_questions.php">Manage Legal Questions</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title">System Settings</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><a href="general_settings.php">General Settings</a></li>
                        <li class="list-group-item"><a href="email_settings.php">Email Settings</a></li>
                        <li class="list-group-item"><a href="payment_settings.php">Payment Settings</a></li>
                    </ul>
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
