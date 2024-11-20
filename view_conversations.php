<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$conversations = [];

// Fetch all conversations for the current user
$sql = "SELECT DISTINCT 
            CASE 
                WHEN m.sender_id = ? THEN m.receiver_id
                ELSE m.sender_id
            END AS other_user_id,
            u.username AS other_username,
            u.role AS other_role
        FROM Messages m
        JOIN Users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END) = u.id
        WHERE m.sender_id = ? OR m.receiver_id = ?
        ORDER BY m.created_at DESC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $conversations[] = $row;
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
    <h2>My Conversations</h2>
    <?php if (!empty($conversations)): ?>
        <ul class="list-group">
            <?php foreach ($conversations as $conversation): ?>
                <li class="list-group-item">
                    <a href="send_message.php?receiver_id=<?php echo $conversation['other_user_id']; ?>">
                        <?php echo htmlspecialchars($conversation['other_username']); ?> 
                        (<?php echo htmlspecialchars($conversation['other_role']); ?>)
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no conversations yet.</p>
    <?php endif; ?>
    <p class="mt-3">
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </p>
</div>

<?php include "includes/footer.php"; ?>
