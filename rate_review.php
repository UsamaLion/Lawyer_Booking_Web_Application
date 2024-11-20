<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a regular user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "user"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;
$lawyer_id = 0;
$lawyer_name = "";
$appointment_date = "";
$error = "";
$success = "";

// Fetch appointment details
if ($appointment_id > 0) {
    $sql = "SELECT a.lawyer_id, a.appointment_date, u.username AS lawyer_name 
            FROM Appointments a
            JOIN Lawyers l ON a.lawyer_id = l.id
            JOIN Users u ON l.user_id = u.id
            WHERE a.id = ? AND a.user_id = ? AND a.status = 'completed'";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $lawyer_id = $row['lawyer_id'];
                $lawyer_name = $row['lawyer_name'];
                $appointment_date = $row['appointment_date'];
            } else {
                $error = "Invalid appointment or you don't have permission to rate this appointment.";
            }
        } else {
            $error = "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);

    if ($rating < 1 || $rating > 5) {
        $error = "Invalid rating. Please select a rating between 1 and 5.";
    } else {
        $sql = "INSERT INTO RatingsReviews (user_id, lawyer_id, appointment_id, rating, review) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiiis", $user_id, $lawyer_id, $appointment_id, $rating, $review);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Thank you for your rating and review!";
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>Rate and Review Your Appointment</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php else: ?>
        <p>Lawyer: <?php echo htmlspecialchars($lawyer_name); ?></p>
        <p>Appointment Date: <?php echo htmlspecialchars($appointment_date); ?></p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?appointment_id=" . $appointment_id; ?>" method="post">
            <div class="form-group">
                <label>Rating:</label>
                <select name="rating" class="form-control" required>
                    <option value="">Select a rating</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very Good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>
            <div class="form-group">
                <label>Review:</label>
                <textarea name="review" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit Rating and Review">
                <a href="my_appointments.php" class="btn btn-secondary">Back to My Appointments</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
