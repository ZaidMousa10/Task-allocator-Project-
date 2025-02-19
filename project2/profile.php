<?php
session_start();
require './db.inc.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

// Fetch user data from the database
$username = $_SESSION['username'];
$query = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$query->execute(['username' => $username]);
$userData = $query->fetch(PDO::FETCH_ASSOC);

// Redirect if user data is not found
if (!$userData) {
    header('Location: ./logout.php');
    exit;
}

// Determine home URL based on user role
$role = $userData['role'];
$homeUrl = ($role === 'Manager') ? './dashboard.php' :
           (($role === 'Team Leader') ? './team_leader_dashboard.php' : './team_dashboard.php');
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
    <?php include './header.php'; ?>
    <div class="container">
    <div class="side-nav">
        <ul>
            <li><a href="<?= htmlspecialchars($homeUrl) ?>">🏠 Home</a></li>
        </ul>
    </div>
        <div class="content">
            <h1>User Profile</h1>
            <div class="profile-container">
                <img src="<?= htmlspecialchars($userData['photo'] ?? './default.jpg') ?>" 
                     alt="Profile Picture" 
                     class="profile-photo-Details">
                <div class="profile-details">
                    <table>
                        <tr>
                            <th>Full Name:</th>
                            <td><?= htmlspecialchars($userData['full_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td><?= htmlspecialchars($userData['address']) ?></td>
                        </tr>
                        <tr>
                            <th>Date of Birth:</th>
                            <td><?= htmlspecialchars($userData['date_of_birth']) ?></td>
                        </tr>
                        <tr>
                            <th>ID Number:</th>
                            <td><?= htmlspecialchars($userData['id_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($userData['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number:</th>
                            <td><?= htmlspecialchars($userData['phone_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td><?= htmlspecialchars($userData['role']) ?></td>
                        </tr>
                        <tr>
                            <th>Qualification:</th>
                            <td><?= htmlspecialchars($userData['qualification']) ?></td>
                        </tr>
                        <tr>
                            <th>Skills:</th>
                            <td><?= htmlspecialchars($userData['skills']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
