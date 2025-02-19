<?php
session_start();
require './db.inc.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to Team Members
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Team Member') {
    die("Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined'));
}

// Ensure username is in the session
if (!isset($_SESSION['username'])) {
    die("Access Denied: Username not found in session.");
}

// Fetch the current team member's ID
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role = 'Team Member'");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$teamMember = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teamMember) {
    die("Access Denied: Team Member not found in the database.");
}

$teamMemberId = $teamMember['id'];

// Check the number of tasks with `NULL` accept for this team member
$countStmt = $pdo->prepare("
    SELECT COUNT(*) AS task_count 
    FROM task_assignments 
    WHERE user_id = :teamMemberId AND accept IS NULL
");
$countStmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
$countStmt->execute();
$result = $countStmt->fetch(PDO::FETCH_ASSOC);

$hasNewTasks = $result['task_count'] > 0; // True if there are tasks with NULL accept
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Member Dashboard</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>

    <div class="main-container">
        <aside class="side-nav">
            <ul>
                <li><a href="./team_dashboard.php">🏠 Dashboard</a></li>
                <li 
                    class="highlight"
                    style="<?= $hasNewTasks ? 'background: yellow; font-weight: bold;' : '' ?>">
                    <a href="./accept_tasks.php">✅ Accept Task Assignments</a>
                </li>
                <li><a href="./search_update_tasks.php">🔍 Search and Update Tasks</a></li>
                <li><a href="./task_search.php">🔍Task Search</a></li>
                <li><a href="./logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="content">
            <h1>Welcome to Your Dashboard</h1>
            <p>Use the menu on the left to navigate through your options.</p>
            <ul>
                <li>Accept and manage tasks assigned to you.</li>
                <li>Search for tasks and update progress easily.</li>
            </ul>
        </div>
    </div>

    <?php include './footer.php'; ?>
</body>
</html>
