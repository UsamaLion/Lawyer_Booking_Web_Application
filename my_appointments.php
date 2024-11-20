<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if the user role is 'user'
if($_SESSION["role"] !== "user"){
    header("location: index.php");
    exit;
}

// Fetch user's appointments
$user_id = $_SESSION["id"];
$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.status, u.username as lawyer_name, l.specialization
        FROM Appointments a
        JOIN Lawyers l ON a.lawyer_id = l.id
        JOIN Users u ON l.user_id = u.id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$appointments = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $appointments[] = $row;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>My Appointments</h2>
    <?php if (!empty($appointments)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Lawyer</th>
                    <th>Specialization</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['lawyer_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['specialization']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                        <td>
                            <?php if ($appointment['status'] == 'completed'): ?>
                                <a href="rate_review.php?appointment_id=<?php echo $appointment['id']; ?>" class="btn btn-primary btn-sm">Rate & Review</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no appointments scheduled.</p>
    <?php endif; ?>
    <p>
        <a href="search_lawyers.php" class="btn btn-primary">Book New Appointment</a>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </p>
</div>

<?php include "includes/footer.php"; ?>
