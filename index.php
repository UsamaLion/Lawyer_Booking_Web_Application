<?php
session_start();
require_once "includes/config.php";

// Redirect logged-in users to their respective dashboards
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    switch ($_SESSION["role"]) {
        case "admin":
            header("location: admin_dashboard.php");
            exit;
        case "lawyer":
            header("location: lawyer_dashboard.php");
            exit;
        case "user":
            header("location: user_dashboard.php");
            exit;
    }
}

// Fetch featured lawyers
$sql = "SELECT l.id, u.username, l.specialization, l.city, 
               (SELECT AVG(rating) FROM RatingsReviews WHERE lawyer_id = l.id) as avg_rating
        FROM Lawyers l
        JOIN Users u ON l.user_id = u.id
        WHERE l.approval_status = 'approved'
        ORDER BY avg_rating DESC
        LIMIT 4";
$result = mysqli_query($conn, $sql);
$featured_lawyers = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($conn);
?>

<?php include "includes/header.php"; ?>

<div class="hero-section" style="background-image: url('images/legal-background.jpg'); background-size: cover; padding: 100px 0; color: white;">
    <div class="container">
        <h1 class="text-center mb-4">Find the Right Lawyer for Your Case</h1>
        <form action="search_lawyers.php" method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search for lawyers...">
                <select name="specialization" class="form-control">
                    <option value="">All Specializations</option>
                    <option value="Family Law">Family Law</option>
                    <option value="Criminal Defense">Criminal Defense</option>
                    <option value="Personal Injury">Personal Injury</option>
                    <option value="Corporate Law">Corporate Law</option>
                </select>
                <input type="text" name="city" class="form-control" placeholder="City">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit">Find a Lawyer</button>
                </div>
            </div>
        </form>
        <div class="text-center">
            <a href="ask_question.php" class="btn btn-secondary">Ask a Question</a>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">Featured Categories</h2>
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Family Law</h5>
                    <p class="card-text">Divorce, child custody, and more.</p>
                    <a href="search_lawyers.php?specialization=Family Law" class="btn btn-primary">Find Lawyers</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Criminal Defense</h5>
                    <p class="card-text">Protect your rights in criminal cases.</p>
                    <a href="search_lawyers.php?specialization=Criminal Defense" class="btn btn-primary">Find Lawyers</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Personal Injury</h5>
                    <p class="card-text">Get compensation for your injuries.</p>
                    <a href="search_lawyers.php?specialization=Personal Injury" class="btn btn-primary">Find Lawyers</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Corporate Law</h5>
                    <p class="card-text">Legal support for your business.</p>
                    <a href="search_lawyers.php?specialization=Corporate Law" class="btn btn-primary">Find Lawyers</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">Featured Lawyers</h2>
    <div class="row">
        <?php foreach ($featured_lawyers as $lawyer): ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <img src="images/lawyer-placeholder.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($lawyer['username']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lawyer['username']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($lawyer['specialization']); ?></p>
                        <p class="card-text"><?php echo htmlspecialchars($lawyer['city']); ?></p>
                        <p class="card-text">Rating: <?php echo number_format($lawyer['avg_rating'], 1); ?>/5</p>
                        <a href="lawyer_profile.php?id=<?php echo $lawyer['id']; ?>" class="btn btn-primary">View Profile</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">What Our Users Say</h2>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <p class="card-text">"I found the perfect lawyer for my case within minutes!"</p>
                    <footer class="blockquote-footer">John D., New York</footer>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <p class="card-text">"The platform made it easy to connect with a skilled attorney."</p>
                    <footer class="blockquote-footer">Sarah M., Los Angeles</footer>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <p class="card-text">"Excellent service! Highly recommend for anyone seeking legal help."</p>
                    <footer class="blockquote-footer">Robert L., Chicago</footer>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">Frequently Asked Questions</h2>
    <div class="row">
        <div class="col-md-6">
            <h5>How do I book a lawyer?</h5>
            <p>Search for a lawyer, view their profile, and click the "Book Appointment" button to schedule a consultation.</p>
        </div>
        <div class="col-md-6">
            <h5>What's the cost of legal consultations?</h5>
            <p>Consultation fees vary by lawyer. Many offer free initial consultations. Check each lawyer's profile for details.</p>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
