<?php
session_start();
require_once "includes/config.php";

// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["id"];
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
$messages = [];
$receiver_username = "";

// Fetch receiver's username
if ($receiver_id > 0) {
    $sql = "SELECT username FROM Users WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $receiver_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_bind_result($stmt, $receiver_username);
            mysqli_stmt_fetch($stmt);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch conversation history
$sql = "SELECT m.*, u.username AS sender_username 
        FROM Messages m
        JOIN Users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
    
    mysqli_stmt_close($stmt);
}

// Handle sending new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $sql = "INSERT INTO Messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $receiver_id, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                // Refresh the page to show the new message
                header("Location: " . $_SERVER['PHP_SELF'] . "?receiver_id=" . $receiver_id);
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

<?php include "includes/header.php"; ?>

<div class="wrapper">
    <h2>Conversation with <?php echo htmlspecialchars($receiver_username); ?></h2>
    <div class="messages-container" style="height: 400px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px;">
        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['sender_id'] == $user_id ? 'text-right' : 'text-left'; ?>">
                <strong><?php echo htmlspecialchars($message['sender_username']); ?>:</strong>
                <p><?php echo htmlspecialchars($message['message']); ?></p>
                <small><?php echo htmlspecialchars($message['created_at']); ?></small>
            </div>
            <hr>
        <?php endforeach; ?>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?receiver_id=" . $receiver_id; ?>" method="post">
        <div class="form-group">
            <textarea name="message" class="form-control" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Send Message">
            <a href="view_conversations.php" class="btn btn-secondary">Back to Conversations</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
