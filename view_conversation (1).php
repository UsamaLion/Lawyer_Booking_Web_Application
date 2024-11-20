<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in and is a regular user
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "user"){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$other_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($other_user_id === 0) {
    header("location: messages.php");
    exit;
}

// Fetch other user's details
$sql = "SELECT username, role FROM Users WHERE id = ?";
$other_user = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $other_user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $other_user = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if (!$other_user) {
    header("location: messages.php");
    exit;
}

// Fetch conversation messages
$sql = "SELECT m.*, u.username AS sender_name
        FROM Messages m
        JOIN Users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC";

$messages = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "iiii", $user_id, $other_user_id, $other_user_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

// Handle new message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $new_message = trim($_POST['message']);
    
    if (!empty($new_message)) {
        $sql = "INSERT INTO Messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $other_user_id, $new_message);
            
            if (mysqli_stmt_execute($stmt)) {
                header("location: view_conversation.php?id=" . $other_user_id);
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation with <?php echo htmlspecialchars($other_user['username']); ?> - Lawyer Booking App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        .message-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 15px;
        }
        .message-sender {
            font-weight: bold;
        }
        .message-time {
            font-size: 0.8em;
            color: #6c757d;
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
    <h1 class="text-center mb-4">Conversation with <?php echo htmlspecialchars($other_user['username']); ?></h1>
    
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <div class="message-list">
                        <?php foreach ($messages as $message): ?>
                            <div class="message">
                                <div class="message-sender">
                                    <?php echo htmlspecialchars($message['sender_name']); ?>
                                    <span class="message-time"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
                                </div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $other_user_id; ?>" method="post" class="mt-3">
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="3" placeholder="Type your message here" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
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
