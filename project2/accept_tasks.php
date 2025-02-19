<?php
session_start();
require './db.inc.php';
require './TaskManager.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to Team Members
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Team Member') {
    die("Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined'));
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

// Initialize TaskManager
$taskManager = new TaskManager($pdo);

// Fetch newly assigned tasks for this team member
$newTasks = $taskManager->getNewlyAssignedTasks($teamMemberId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Confirmation</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>

    <div class="main-container">
        <!-- Side Navigation -->
        <aside class="side-nav">
            <ul>
                <li><a href="./team_dashboard.php">🏠 Dashboard</a></li>
                <li 
                    class="highlight"
                    style="<?= !empty($newTasks) ? 'background: yellow; font-weight: bold;' : '' ?>">
                    <a href="./accept_tasks.php">✅ Accept Task Assignments</a>
                </li>
                <li><a href="./search_update_tasks.php">🔍 Search and Update Tasks</a></li>
                <li><a href="./task_search.php">🔍Task Search</a></li>
                <li><a href="./logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="content">
            <h1>Task Confirmation</h1>

            <!-- Task List Table -->
            <?php if (!empty($newTasks)): ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Task Name</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($newTasks as $task): ?>
                            <tr>
                                <td><?= htmlspecialchars($task['task_id']) ?></td>
                                <td><?= htmlspecialchars($task['title']) ?></td>
                                <td><?= htmlspecialchars($task['start_date']) ?></td>
                                <td><?= htmlspecialchars($task['status']) ?></td>
                                <td><?= htmlspecialchars($task['priority']) ?></td>
                                <td>
                                    <a href="./confirm_task.php?task_id=<?= htmlspecialchars($task['task_id']) ?>" class="confirm-button">Confirm</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-tasks">No new task assignments at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include './footer.php'; ?>
</body>
</html>
