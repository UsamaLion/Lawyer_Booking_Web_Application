<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}

$lawyer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$username = $email = $specialization = $city = $approval_status = "";
$username_err = $email_err = $specialization_err = $city_err = $approval_status_err = "";

if($lawyer_id > 0){
    // Fetch lawyer details
    $sql = "SELECT u.username, u.email, l.specialization, l.city, l.approval_status 
            FROM Lawyers l
            JOIN Users u ON l.user_id = u.id
            WHERE l.id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $lawyer_id);
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            if($row = mysqli_fetch_assoc($result)){
                $username = $row['username'];
                $email = $row['email'];
                $specialization = $row['specialization'];
                $city = $row['city'];
                $approval_status = $row['approval_status'];
            } else {
                header("location: manage_lawyers.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate and sanitize input
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $specialization = trim($_POST["specialization"]);
    $city = trim($_POST["city"]);
    $approval_status = trim($_POST["approval_status"]);
    
    // Perform validation checks here (e.g., check if fields are empty)
    
    // If no errors, update the database
    if(empty($username_err) && empty($email_err) && empty($specialization_err) && empty($city_err) && empty($approval_status_err)){
        $sql = "UPDATE Lawyers l
                JOIN Users u ON l.user_id = u.id
                SET u.username=?, u.email=?, l.specialization=?, l.city=?, l.approval_status=?
                WHERE l.id=?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssssi", $username, $email, $specialization, $city, $approval_status, $lawyer_id);
            if(mysqli_stmt_execute($stmt)){
                header("location: manage_lawyers.php?msg=Lawyer updated successfully");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
    }
}

mysqli_close($conn);
?>

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>Edit Lawyer</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $lawyer_id; ?>" method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
            <span class="invalid-feedback"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
            <span class="invalid-feedback"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group">
            <label>Specialization</label>
            <input type="text" name="specialization" class="form-control <?php echo (!empty($specialization_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $specialization; ?>">
            <span class="invalid-feedback"><?php echo $specialization_err; ?></span>
        </div>
        <div class="form-group">
            <label>City</label>
            <input type="text" name="city" class="form-control <?php echo (!empty($city_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $city; ?>">
            <span class="invalid-feedback"><?php echo $city_err; ?></span>
        </div>
        <div class="form-group">
            <label>Approval Status</label>
            <select name="approval_status" class="form-control <?php echo (!empty($approval_status_err)) ? 'is-invalid' : ''; ?>">
                <option value="pending" <?php echo ($approval_status == "pending") ? "selected" : ""; ?>>Pending</option>
                <option value="approved" <?php echo ($approval_status == "approved") ? "selected" : ""; ?>>Approved</option>
                <option value="rejected" <?php echo ($approval_status == "rejected") ? "selected" : ""; ?>>Rejected</option>
            </select>
            <span class="invalid-feedback"><?php echo $approval_status_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Update">
            <a href="manage_lawyers.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
