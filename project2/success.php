<?php
session_start();

// Redirect to the registration page if no success message is set
if (!isset($_SESSION['success_message'])) {
    header('Location: ./register_step1.php');
    exit;
}

// Retrieve and unset the success message
$successMessage = $_SESSION['success_message'];
unset($_SESSION['success_message']);

// Generate a unique 10-digit user ID
$userID = str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.html'; ?>
    <div class="container">
        <div class="content success-message">
            <h1>Registration Successful!</h1>
            <p><?= htmlspecialchars($successMessage) ?></p>
            <p><strong>Your User ID:</strong> <?= htmlspecialchars($userID) ?></p>
            <a href="./login.php" class="home-link">Go to Login Page</a>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
