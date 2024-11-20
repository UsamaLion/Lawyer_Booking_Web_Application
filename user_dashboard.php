<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a regular user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "user"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch user's upcoming appointments
$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, u.username as lawyer_name, l.specialization
        FROM Appointments a
        JOIN Lawyers l ON a.lawyer_id = l.id
        JOIN Users u ON l.user_id = u.id
        WHERE a.user_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5";

$upcoming_appointments = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $upcoming_appointments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Lawyer Booking App</title>
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
    <h1 class="text-center mb-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments</h5>
                    <?php if (!empty($upcoming_appointments)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($appointment['lawyer_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($appointment['specialization']); ?><br>
                                    <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?><br>
                                    Status: <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No upcoming appointments.</p>
                    <?php endif; ?>
                    <a href="view_appointments.php" class="btn btn-primary mt-3">View All Appointments</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-edit me-2"></i>Manage Profile</h5>
                    <p>Update your personal information and preferences.</p>
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-comments me-2"></i>Messages</h5>
                    <p>View and respond to messages from lawyers.</p>
                    <a href="messages.php" class="btn btn-primary">View Messages</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-search me-2"></i>Find a Lawyer</h5>
                    <p>Search for lawyers based on specialization and location.</p>
                    <a href="search_lawyers.php" class="btn btn-primary">Search Lawyers</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-question-circle me-2"></i>Ask a Question</h5>
                    <p>Post a legal question and get answers from experienced lawyers.</p>
                    <a href="ask_question.php" class="btn btn-primary">Ask a Question</a>
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
