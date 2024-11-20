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

// Fetch lawyer's ID
$sql = "SELECT id FROM Lawyers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $lawyer_id = $row['id'];
    }
    mysqli_stmt_close($stmt);
}

// Fetch analytics data
$total_appointments = 0;
$completed_appointments = 0;
$cancelled_appointments = 0;
$average_rating = 0;
$total_reviews = 0;

// Total appointments
$sql = "SELECT COUNT(*) as total FROM Appointments WHERE lawyer_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $total_appointments = $row['total'];
    }
    mysqli_stmt_close($stmt);
}

// Completed appointments
$sql = "SELECT COUNT(*) as completed FROM Appointments WHERE lawyer_id = ? AND status = 'completed'";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $completed_appointments = $row['completed'];
    }
    mysqli_stmt_close($stmt);
}

// Cancelled appointments
$sql = "SELECT COUNT(*) as cancelled FROM Appointments WHERE lawyer_id = ? AND status = 'cancelled'";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $cancelled_appointments = $row['cancelled'];
    }
    mysqli_stmt_close($stmt);
}

// Average rating and total reviews
$sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM RatingsReviews WHERE lawyer_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $average_rating = number_format($row['avg_rating'], 1);
        $total_reviews = $row['total_reviews'];
    }
    mysqli_stmt_close($stmt);
}

// Fetch recent reviews
$sql = "SELECT r.*, u.username as client_name
        FROM RatingsReviews r
        JOIN Users u ON r.user_id = u.id
        WHERE r.lawyer_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5";

$recent_reviews = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $recent_reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
    <title>Lawyer Analytics - Lawyer Booking App</title>
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
    <h1 class="text-center mb-4">Lawyer Analytics</h1>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Appointments</h5>
                    <p class="card-text display-4"><?php echo $total_appointments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Completed Appointments</h5>
                    <p class="card-text display-4"><?php echo $completed_appointments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Cancelled Appointments</h5>
                    <p class="card-text display-4"><?php echo $cancelled_appointments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Average Rating</h5>
                    <p class="card-text display-4"><?php echo $average_rating; ?> <small class="text-muted">(<?php echo $total_reviews; ?> reviews)</small></p>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mt-5 mb-4">Recent Reviews</h2>
    <?php if (!empty($recent_reviews)): ?>
        <?php foreach ($recent_reviews as $review): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Rating: <?php echo $review['rating']; ?>/5</h5>
                    <h6 class="card-subtitle mb-2 text-muted">By <?php echo htmlspecialchars($review['client_name']); ?> on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></h6>
                    <p class="card-text"><?php echo htmlspecialchars($review['review']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>
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
