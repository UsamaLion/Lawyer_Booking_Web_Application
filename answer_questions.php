<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a lawyer
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "lawyer"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$lawyer_id = null;
$specialization = null;

// Fetch lawyer's ID and specialization
$sql = "SELECT id, specialization FROM Lawyers WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $lawyer_id = $row['id'];
        $specialization = $row['specialization'];
    }
    mysqli_stmt_close($stmt);
}

// Handle answer submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_answer'])) {
    $question_id = intval($_POST['question_id']);
    $answer_text = trim($_POST['answer_text']);
    
    if (!empty($answer_text)) {
        $sql = "INSERT INTO Answers (question_id, lawyer_id, answer_text) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iis", $question_id, $lawyer_id, $answer_text);
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Answer submitted successfully.";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error_message = "Please enter an answer before submitting.";
    }
}

// Fetch unanswered questions in lawyer's specialization
$sql = "SELECT q.*, u.username as asker_name
        FROM Questions q
        JOIN Users u ON q.user_id = u.id
        WHERE q.category = ? AND q.id NOT IN (SELECT question_id FROM Answers)
        ORDER BY q.created_at DESC";

$questions = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $specialization);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $questions = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Answer Questions - Lawyer Booking App</title>
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
                    <a class="nav-link" href="lawyer_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="text-center mb-4">Answer Questions</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($questions)): ?>
        <?php foreach ($questions as $question): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Question by <?php echo htmlspecialchars($question['asker_name']); ?></strong>
                    <span class="float-end"><?php echo date('F j, Y g:i A', strtotime($question['created_at'])); ?></span>
                </div>
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                        <div class="mb-3">
                            <label for="answer_text" class="form-label">Your Answer</label>
                            <textarea name="answer_text" id="answer_text" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" name="submit_answer" class="btn btn-primary">Submit Answer</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">There are no unanswered questions in your specialization at the moment.</p>
    <?php endif; ?>
    
    <div class="text-center mt-4">
        <a href="lawyer_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
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
