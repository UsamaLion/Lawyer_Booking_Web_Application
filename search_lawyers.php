<?php
session_start();
require_once "includes/config.php";

// Initialize search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$specialization = isset($_GET['specialization']) ? trim($_GET['specialization']) : '';
$city = isset($_GET['city']) ? trim($_GET['city']) : '';

// Prepare the SQL query
$sql = "SELECT l.id, u.username, l.specialization, l.city, 
               (SELECT AVG(rating) FROM RatingsReviews WHERE lawyer_id = l.id) as avg_rating,
               (SELECT COUNT(*) FROM RatingsReviews WHERE lawyer_id = l.id) as review_count
        FROM Lawyers l
        JOIN Users u ON l.user_id = u.id
        WHERE l.approval_status = 'approved'";

if (!empty($search)) {
    $sql .= " AND (u.username LIKE ? OR l.specialization LIKE ? OR l.city LIKE ?)";
}
if (!empty($specialization)) {
    $sql .= " AND l.specialization = ?";
}
if (!empty($city)) {
    $sql .= " AND l.city = ?";
}

$sql .= " ORDER BY avg_rating DESC";

// Prepare and execute the statement
if ($stmt = mysqli_prepare($conn, $sql)) {
    $param_types = "";
    $param_values = array();

    if (!empty($search)) {
        $param_types .= "sss";
        $search_param = "%$search%";
        $param_values[] = &$search_param;
        $param_values[] = &$search_param;
        $param_values[] = &$search_param;
    }
    if (!empty($specialization)) {
        $param_types .= "s";
        $param_values[] = &$specialization;
    }
    if (!empty($city)) {
        $param_types .= "s";
        $param_values[] = &$city;
    }

    if (!empty($param_types)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$param_values);
    }

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $lawyers = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "Oops! Something went wrong. Please try again later.";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Lawyers - Lawyer Booking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .lawyer-card {
            transition: transform 0.3s;
        }
        .lawyer-card:hover {
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
    <h1 class="text-center mb-4">Search Lawyers</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-4">
        <div class="row g-3">
            <div class="col-md">
                <input type="text" name="search" class="form-control" placeholder="Search for lawyers..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md">
                <select name="specialization" class="form-select">
                    <option value="">All Specializations</option>
                    <option value="Family Law" <?php echo ($specialization == "Family Law") ? "selected" : ""; ?>>Family Law</option>
                    <option value="Criminal Defense" <?php echo ($specialization == "Criminal Defense") ? "selected" : ""; ?>>Criminal Defense</option>
                    <option value="Personal Injury" <?php echo ($specialization == "Personal Injury") ? "selected" : ""; ?>>Personal Injury</option>
                    <option value="Corporate Law" <?php echo ($specialization == "Corporate Law") ? "selected" : ""; ?>>Corporate Law</option>
                </select>
            </div>
            <div class="col-md">
                <input type="text" name="city" class="form-control" placeholder="City" value="<?php echo htmlspecialchars($city); ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <?php if (!empty($lawyers)): ?>
        <div class="row">
            <?php foreach ($lawyers as $lawyer): ?>
                <div class="col-md-4 mb-4">
                    <div class="card lawyer-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($lawyer['username']); ?></h5>
                            <p class="card-text"><i class="fas fa-balance-scale me-2"></i><?php echo htmlspecialchars($lawyer['specialization']); ?></p>
                            <p class="card-text"><i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($lawyer['city']); ?></p>
                            <p class="card-text">
                                <i class="fas fa-star me-2"></i>Rating: <?php echo number_format($lawyer['avg_rating'], 1); ?>/5
                                (<?php echo $lawyer['review_count']; ?> reviews)
                            </p>
                            <a href="lawyer_profile.php?id=<?php echo $lawyer['id']; ?>" class="btn btn-primary">View Profile</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No lawyers found matching your criteria.</p>
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
