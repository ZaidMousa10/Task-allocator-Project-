<?php
session_start();

if (!isset($_SESSION['success_message'])) {
    header('Location: ./addproject.php');
    exit;
}

$successMessage = $_SESSION['success_message'];
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="container">
        <div class="success-message">
            <h1>Success</h1>
            <p><?= htmlspecialchars($successMessage) ?></p>
            <a href="./addproject.php" class="home-link">Back to Task Creation</a>
        </div>
    </div>

    <?php include './footer.php'; ?>
</body>
</html>
