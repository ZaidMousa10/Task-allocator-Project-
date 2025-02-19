<?php
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

// Default profile picture if not set
$photoPath = $_SESSION['photo'] ?? './default.jpg';
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Allocator</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <div class="header-container">
        <h1>Task Allocator</h1>
        <nav class="top-nav">
            <ul>
                <li>
                    <a href="./profile.php" class="profile-link">
                        <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile Picture" class="profile-photo">
                        <span>Profile</span>
                    </a>
                </li>
                <li>
                    <a href="./logout.php" class="logout-link">Logout</a>
                </li>
            </ul>
        </nav>
    </div>
    </body>
    </html>
