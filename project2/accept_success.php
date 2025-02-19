<?php
session_start();

// Check if a success message is set in the session
if (!isset($_SESSION['success_message'])) {
    header('Location: ./accept_tasks.php'); // Redirect if no message
    exit;
}

$successMessage = $_SESSION['success_message'];
unset($_SESSION['success_message']); // Clear the message after displaying it
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Accepted</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>

    <div class="main-container">
        <div class="content">
            <div class="success-message">
                <h1>Task Accepted</h1>
                <p><?= htmlspecialchars($successMessage) ?></p>
                <a href="./accept_tasks.php" class="action-button">Back to Task List</a>
                <a href="./team_dashboard.php" class="action-button">Go to Dashboard</a>
            </div>
        </div>
    </div>

    <?php include './footer.php'; ?>
</body>
</html>
