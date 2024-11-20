<?php
session_start();

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
?>

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>Appointment Booked Successfully</h2>
    <p>Your appointment has been booked successfully. You will receive a confirmation email shortly.</p>
    <p>
        <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        <a href="search_lawyers.php" class="btn btn-secondary">Search More Lawyers</a>
    </p>
</div>

<?php include "includes/footer.php"; ?>
