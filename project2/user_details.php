<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

// Simulated user data; replace with actual user data retrieval
$userData = [
    'username' => $_SESSION['username'],
    'photo' => './default.jpg', // Path to the user photo
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.html'; ?>
    <div class="container">
        <div class="content">
            <h1>User Profile</h1>
            <div class="profile-container">
                <img src="<?= htmlspecialchars($userData['photo']) ?>" alt="Profile Picture" class="profile-photo">
                <a href="./user_details.php" class="profile-name"><?= htmlspecialchars($userData['username']) ?></a>
            </div>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
