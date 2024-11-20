<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a lawyer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "lawyer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch lawyer's details
$sql = "SELECT l.*, u.username, u.email 
        FROM Lawyers l
        JOIN Users u ON l.user_id = u.id
        WHERE l.user_id = ?";

$lawyer = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $lawyer = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

// Fetch upcoming appointments
$sql = "SELECT a.*, u.username as client_name
        FROM Appointments a
        JOIN Users u ON a.user_id = u.id
        WHERE a.lawyer_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5";

$upcoming_appointments = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $lawyer['id']);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $upcoming_appointments = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt);
}

// Fetch recent questions in lawyer's specialization
$sql = "SELECT q.*, u.username as asker_name
        FROM Questions q
        JOIN Users u ON q.user_id = u.id
        WHERE q.category = ? AND q.id NOT IN (SELECT question_id FROM Answers)
        ORDER BY q.created_at DESC
        LIMIT 5";

$recent_questions = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $lawyer['specialization']);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $recent_questions = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
    <title>Lawyer Dashboard - Lawyer Booking App</title>
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
                    <a class="nav-link" href="lawyer_profile.php?id=<?php echo $lawyer['id']; ?>">My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-4">Welcome, <?php echo htmlspecialchars($lawyer['username']); ?>!</h1>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-alt me-2"></i>Upcoming Appointments</h5>
                    <?php if (!empty($upcoming_appointments)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($upcoming_appointments as $appointment): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($appointment['client_name']); ?></strong><br>
                                    <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?> at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No upcoming appointments.</p>
                    <?php endif; ?>
                    <a href="manage_appointments.php" class="btn btn-primary mt-3">Manage Appointments</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-question-circle me-2"></i>Recent Questions</h5>
                    <?php if (!empty($recent_questions)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_questions as $question): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars(substr($question['question_text'], 0, 50)) . '...'; ?></strong><br>
                                    Asked by: <?php echo htmlspecialchars($question['asker_name']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No recent questions in your specialization.</p>
                    <?php endif; ?>
                    <a href="answer_questions.php" class="btn btn-primary mt-3">Answer Questions</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-user-edit me-2"></i>Profile Management</h5>
                    <p>Update your profile information and availability.</p>
                    <a href="edit_lawyer_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-comments me-2"></i>Messages</h5>
                    <p>View and respond to messages from clients.</p>
                    <a href="lawyer_messages.php" class="btn btn-primary">View Messages</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Performance Analytics</h5>
                    <p>View your performance statistics and client feedback.</p>
                    <a href="lawyer_analytics.php" class="btn btn-primary">View Analytics</a>
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
