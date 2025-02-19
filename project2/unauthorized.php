<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Manager') {
    // If the user is not authorized, redirect or show a message
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <div class="container">
        <div class="content">
            <h1 class="error-title">Access Denied</h1>
            <p class="error-message">You do not have permission to access this page.</p>
            <a href="./index.php" class="home-link">Return to Home</a>
        </div>
    </div>
</body>
</html>';
    exit;
}

// If the user is authorized, proceed with the rest of the page logic (if needed)
?>
