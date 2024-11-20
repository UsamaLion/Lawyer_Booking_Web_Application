<?php
session_start();
require_once "includes/config.php";

$lawyer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lawyer = null;
$reviews = [];

if ($lawyer_id > 0) {
    // Fetch lawyer details
    $sql = "SELECT l.*, u.username, u.email, 
                   (SELECT AVG(rating) FROM RatingsReviews WHERE lawyer_id = l.id) as avg_rating,
                   (SELECT COUNT(*) FROM RatingsReviews WHERE lawyer_id = l.id) as review_count
            FROM Lawyers l
            JOIN Users u ON l.user_id = u.id
            WHERE l.id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $lawyer = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }

    // Fetch reviews
    $sql = "SELECT r.*, u.username as reviewer_name
            FROM RatingsReviews r
            JOIN Users u ON r.user_id = u.id
            WHERE r.lawyer_id = ?
            ORDER BY r.created_at DESC
            LIMIT 5";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lawyer ? htmlspecialchars($lawyer['username']) : 'Lawyer Profile'; ?> - Lawyer Booking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .lawyer-profile {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .review-card {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
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
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if ($lawyer): ?>
        <div class="lawyer-profile p-4">
            <div class="row">
                <div class="col-md-4">
                    <img src="images/lawyer-placeholder.jpg" alt="<?php echo htmlspecialchars($lawyer['username']); ?>" class="img-fluid rounded">
                </div>
                <div class="col-md-8">
                    <h1><?php echo htmlspecialchars($lawyer['username']); ?></h1>
                    <p><i class="fas fa-balance-scale me-2"></i><?php echo htmlspecialchars($lawyer['specialization']); ?></p>
                    <p><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($lawyer['city']); ?></p>
                    <p><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($lawyer['email']); ?></p>
                    <p><i class="fas fa-star me-2"></i>Rating: <?php echo number_format($lawyer['avg_rating'], 1); ?>/5 (<?php echo $lawyer['review_count']; ?> reviews)</p>
                    <a href="#book-appointment" class="btn btn-primary">Book Appointment</a>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h2>Reviews</h2>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card review-card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Rating: <?php echo $review['rating']; ?>/5</h5>
                            <h6 class="card-subtitle mb-2 text-muted">By <?php echo htmlspecialchars($review['reviewer_name']); ?> on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></h6>
                            <p class="card-text"><?php echo htmlspecialchars($review['review']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
        </div>

        <div class="mt-5" id="book-appointment">
            <h2>Book an Appointment</h2>
            <form action="book_appointment.php" method="post">
                <input type="hidden" name="lawyer_id" value="<?php echo $lawyer['id']; ?>">
                <div class="mb-3">
                    <label for="appointment_date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                </div>
                <div class="mb-3">
                    <label for="appointment_time" class="form-label">Time</label>
                    <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Book Appointment</button>
            </form>
        </div>
    <?php else: ?>
        <p class="text-center">Lawyer not found.</p>
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
