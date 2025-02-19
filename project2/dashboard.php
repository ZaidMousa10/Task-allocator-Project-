<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="container">
        <aside class="side-nav">
            <nav>
                <ul>
                    <li><a href="./addproject.php">Add Project</a></li>
                    <li><a href="./unassigned_projects.php">Allocate Team Leader</a></li>
                    <li><a href="./task_search.php">Task Search</a></li>
                    <li><a href="./logout.php">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <div class="content">
            <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
            <p>This is your dashboard. Use the options on the left to manage projects and team leaders.</p>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
