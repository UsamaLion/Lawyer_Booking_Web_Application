<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is an admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: login.php");
    exit;
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$username = $email = $role = "";
$username_err = $email_err = $role_err = "";

if($user_id > 0){
    // Fetch user details
    $sql = "SELECT username, email, role FROM Users WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $username, $email, $role);
                mysqli_stmt_fetch($stmt);
            } else {
                header("location: manage_users.php");
                exit();
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate role
    if(empty(trim($_POST["role"]))){
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }
    
    // Check input errors before updating in database
    if(empty($username_err) && empty($email_err) && empty($role_err)){
        $sql = "UPDATE Users SET username=?, email=?, role=? WHERE id=?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $role, $user_id);
            if(mysqli_stmt_execute($stmt)){
                header("location: manage_users.php?msg=User updated successfully");
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
    <h2>Edit User</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $user_id; ?>" method="post">
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
            <label>Role</label>
            <select name="role" class="form-control <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                <option value="user" <?php echo ($role == "user") ? "selected" : ""; ?>>User</option>
                <option value="lawyer" <?php echo ($role == "lawyer") ? "selected" : ""; ?>>Lawyer</option>
                <option value="admin" <?php echo ($role == "admin") ? "selected" : ""; ?>>Admin</option>
            </select>
            <span class="invalid-feedback"><?php echo $role_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Update">
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
