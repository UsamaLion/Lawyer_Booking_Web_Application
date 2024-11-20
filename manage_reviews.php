<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}

// Handle review deletion
if(isset($_GET['delete'])) {
    $review_id = intval($_GET['delete']);
    $sql = "DELETE FROM RatingsReviews WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $review_id);
        if(mysqli_stmt_execute($stmt)){
            header("location: manage_reviews.php?msg=Review deleted successfully");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}

// Fetch all reviews
$sql = "SELECT rr.id, rr.rating, rr.review, rr.created_at, 
               u.username as client_name, l.username as lawyer_name
        FROM RatingsReviews rr
        JOIN Users u ON rr.user_id = u.id
        JOIN Lawyers lw ON rr.lawyer_id = lw.id
        JOIN Users l ON lw.user_id = l.id
        ORDER BY rr.created_at DESC";
$result = mysqli_query($conn, $sql);
$reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($conn);
?>

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>Manage Reviews</h2>
    <?php 
    if(isset($_GET['msg'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</div>";
    }
    ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Lawyer</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reviews as $review): ?>
            <tr>
                <td><?php echo $review['id']; ?></td>
                <td><?php echo htmlspecialchars($review['client_name']); ?></td>
                <td><?php echo htmlspecialchars($review['lawyer_name']); ?></td>
                <td><?php echo htmlspecialchars($review['rating']); ?></td>
                <td><?php echo htmlspecialchars($review['review']); ?></td>
                <td><?php echo htmlspecialchars($review['created_at']); ?></td>
                <td>
                    <a href="manage_reviews.php?delete=<?php echo $review['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include "includes/footer.php"; ?>
