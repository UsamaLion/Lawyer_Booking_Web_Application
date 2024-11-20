<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}

// Handle lawyer approval
if(isset($_GET['approve'])) {
    $lawyer_id = intval($_GET['approve']);
    $sql = "UPDATE Lawyers SET approval_status = 'approved' WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
        if(mysqli_stmt_execute($stmt)){
            header("location: manage_lawyers.php?msg=Lawyer approved successfully");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}

// Handle lawyer rejection
if(isset($_GET['reject'])) {
    $lawyer_id = intval($_GET['reject']);
    $sql = "UPDATE Lawyers SET approval_status = 'rejected' WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
        if(mysqli_stmt_execute($stmt)){
            header("location: manage_lawyers.php?msg=Lawyer rejected successfully");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}

// Fetch all lawyers
$sql = "SELECT l.id, u.username, u.email, l.specialization, l.city, l.approval_status 
        FROM Lawyers l
        JOIN Users u ON l.user_id = u.id
        ORDER BY l.id DESC";
$result = mysqli_query($conn, $sql);
$lawyers = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_close($conn);
?>

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>Manage Lawyers</h2>
    <?php 
    if(isset($_GET['msg'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['msg']) . "</div>";
    }
    ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>City</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($lawyers as $lawyer): ?>
            <tr>
                <td><?php echo $lawyer['id']; ?></td>
                <td><?php echo htmlspecialchars($lawyer['username']); ?></td>
                <td><?php echo htmlspecialchars($lawyer['email']); ?></td>
                <td><?php echo htmlspecialchars($lawyer['specialization']); ?></td>
                <td><?php echo htmlspecialchars($lawyer['city']); ?></td>
                <td><?php echo htmlspecialchars($lawyer['approval_status']); ?></td>
                <td>
                    <?php if($lawyer['approval_status'] == 'pending'): ?>
                        <a href="manage_lawyers.php?approve=<?php echo $lawyer['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                        <a href="manage_lawyers.php?reject=<?php echo $lawyer['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
                    <?php elseif($lawyer['approval_status'] == 'approved'): ?>
                        <a href="manage_lawyers.php?reject=<?php echo $lawyer['id']; ?>" class="btn btn-warning btn-sm">Revoke Approval</a>
                    <?php else: ?>
                        <a href="manage_lawyers.php?approve=<?php echo $lawyer['id']; ?>" class="btn btn-info btn-sm">Reconsider</a>
                    <?php endif; ?>
                    <a href="edit_lawyer.php?id=<?php echo $lawyer['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include "includes/footer.php"; ?>
