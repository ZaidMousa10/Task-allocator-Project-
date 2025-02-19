<?php
session_start();
require './db.inc.php';
require './TaskManager.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

// Fetch user's role for navigation
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'Guest';
$homePage = ($role === 'Manager') ? './dashboard.php' :
            (($role === 'Team Leader') ? './team_leader_dashboard.php' : './team_dashboard.php');

// Initialize TaskManager
$taskManager = new TaskManager($pdo);

// Fetch sorting parameters
$sortColumn = $_GET['sort'] ?? 'task_id';
$sortOrder = $_GET['order'] ?? 'asc';

// Validate sorting inputs
$allowedColumns = ['task_id', 'title', 'project', 'status', 'priority', 'start_date', 'end_date', 'completion_percentage'];
if (!in_array($sortColumn, $allowedColumns)) {
    $sortColumn = 'task_id';
}
if ($sortOrder !== 'asc' && $sortOrder !== 'desc') {
    $sortOrder = 'asc';
}

// Fetch tasks based on role
$tasks = [];
if ($role === 'Manager') {
    $tasks = $taskManager->fetchTasksSorted($sortColumn, $sortOrder);
} elseif ($role === 'Team Leader') {
    $tasks = $taskManager->fetchTasksForTeamLeader($username, $sortColumn, $sortOrder);
} elseif ($role === 'Team Member') {
    $tasks = $taskManager->fetchTasksForTeamMember($username, $sortColumn, $sortOrder);
}

// Determine the opposite sort order for toggling
$toggleOrder = $sortOrder === 'asc' ? 'desc' : 'asc';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Results</title>
    <link rel="stylesheet" href="./task_results.css">
</head>
<body>
<?php include './header.php'; ?>
    <div class="table-container">
        <h1>Task Results</h1>
        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <a href="<?= htmlspecialchars($homePage) ?>" class="button">Return to Home</a>
            <a href="./task_search.php" class="button">Return to Previous Page</a>
            </div>

        <?php if (!empty($tasks)): ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>
                            Task ID
                            <a href="?sort=task_id&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Title
                            <a href="?sort=title&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Project
                            <a href="?sort=project&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Status
                            <a href="?sort=status&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Priority
                            <a href="?sort=priority&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Start Date
                            <a href="?sort=start_date&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Due Date
                            <a href="?sort=end_date&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                        <th>
                            Completion %
                            <a href="?sort=completion_percentage&order=<?= $toggleOrder ?>"> <?= $sortOrder === 'asc' ? '↓' : '↑' ?></a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><a href="./task_details.php?task_id=<?= htmlspecialchars($task['task_id']) ?>"><?= htmlspecialchars($task['task_id']) ?></a></td>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['project'] ?? 'N/A') ?></td>
                            <td class="<?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><?= htmlspecialchars($task['status']) ?></td>
                            <td class="<?= strtolower($task['priority']) ?>-priority"><?= htmlspecialchars($task['priority']) ?></td>
                            <td><?= htmlspecialchars($task['start_date']) ?></td>
                            <td><?= htmlspecialchars($task['end_date']) ?></td>
                            <td><?= htmlspecialchars($task['completion_percentage']) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tasks found.</p>
        <?php endif; ?>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
