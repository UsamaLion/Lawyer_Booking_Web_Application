<?php
session_start();
require_once "includes/config.php";

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: blog.php");
    exit;
}

$post_id = intval($_GET['id']);

// Fetch blog post
$sql = "SELECT bp.*, u.username as author_name
        FROM BlogPosts bp
        JOIN Users u ON bp.author_id = u.id
        WHERE bp.id = ?";

$post = null;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $post = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if (!$post) {
    header("location: blog.php");
    exit;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Legal Blog</title>
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
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="blog.php">Blog</a>
                </li>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $_SESSION["role"] ?>_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <article>
        <h1 class="mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="text-muted">
            By <?php echo htmlspecialchars($post['author_name']); ?> on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
        </p>
        <div class="mt-4">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>
    </article>

    <?php if(isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
        <div class="mt-4">
            <a href="edit_blog_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Edit Post</a>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="blog.php" class="btn btn-secondary">Back to Blog</a>
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
