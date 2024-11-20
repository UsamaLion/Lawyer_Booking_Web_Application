<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a regular user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "user"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$question = $category = "";
$question_err = $category_err = "";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate question
    if(empty(trim($_POST["question"]))){
        $question_err = "Please enter your question.";
    } else {
        $question = trim($_POST["question"]);
    }
    
    // Validate category
    if(empty(trim($_POST["category"]))){
        $category_err = "Please select a category.";
    } else {
        $category = trim($_POST["category"]);
    }
    
    // Check input errors before inserting in database
    if(empty($question_err) && empty($category_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO Questions (user_id, category, question_text) VALUES (?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "iss", $param_user_id, $param_category, $param_question);
            
            // Set parameters
            $param_user_id = $user_id;
            $param_category = $category;
            $param_question = $question;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to success page
                header("location: user_dashboard.php?msg=Question submitted successfully");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask a Legal Question - Lawyer Booking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
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
                    <a class="nav-link" href="user_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="search_lawyers.php">Search Lawyers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-4">Ask a Legal Question</h1>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" class="form-select <?php echo (!empty($category_err)) ? 'is-invalid' : ''; ?>">
                                <option value="">Select a category</option>
                                <option value="Family Law">Family Law</option>
                                <option value="Criminal Law">Criminal Law</option>
                                <option value="Civil Law">Civil Law</option>
                                <option value="Corporate Law">Corporate Law</option>
                                <option value="Property Law">Property Law</option>
                                <option value="Immigration Law">Immigration Law</option>
                                <option value="Other">Other</option>
                            </select>
                            <span class="invalid-feedback"><?php echo $category_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label for="question" class="form-label">Your Question</label>
                            <textarea name="question" class="form-control <?php echo (!empty($question_err)) ? 'is-invalid' : ''; ?>" rows="5" placeholder="Type your legal question here"><?php echo $question; ?></textarea>
                            <span class="invalid-feedback"><?php echo $question_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Submit Question</button>
                            <a href="user_dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
