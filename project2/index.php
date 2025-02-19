<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Allocator</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.html'; ?>
    <div class="container">
        <aside class="side-nav">
            <nav>
                <ul>
                    <li><a href="./register_step1.php">Register</a></li>
                    <li><a href="./login.php">Login</a></li>
                </ul>
            </nav>
        </aside>
        <main>
    <h1>Project Team Details</h1>
    <table class="team-details">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Username</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Kareem</td>
                <td>Manager</td>
                <td>kareem@gmail.com</td>
                <td>kareem</td>
                <td>12345678</td>
            </tr>
            <tr>
                <td>Anas</td>
                <td>Team Leader</td>
                <td>anas@gmail.com</td>
                <td>anas123</td>
                <td>12345678</td>
            </tr>
            <tr>
                <td>Nedal</td>
                <td>Team Member</td>
                <td>nedal@example.com</td>
                <td>nedal12345</td>
                <td>12345678</td>
            </tr>
            <tr>
                <td>Hamza</td>
                <td>Team Member</td>
                <td>hamza@example.com</td>
                <td>hamzamousa</td>
                <td>12345678</td>
            </tr>
            <tr>
                <td>Mouath</td>
                <td>Team Member</td>
                <td>mouath@example.com</td>
                <td>mouathmousa</td>
                <td>12345678</td>
            </tr>
        </tbody>
    </table>
</main>

    </div>
    <?php include './footer.php'; ?>
</body>
</html>
