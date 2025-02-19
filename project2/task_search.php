<?php
session_start();
require './db.inc.php';
require './TaskManager.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect to login if the user is not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ./login.php');
    exit;
}

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

// Role-based filtering
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$tasks = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $priority = $_POST['priority'] ?? null;
    $status = $_POST['status'] ?? null;
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    $projectId = $_POST['project_id'] ?? null;

    if ($role === 'Manager') {
        $tasks = $taskManager->searchTasksForManager($priority, $status, $startDate, $endDate, $projectId, $sortColumn, $sortOrder);
    } elseif ($role === 'Team Leader') {
        $tasks = $taskManager->searchTasksForTeamLeader($username, $priority, $status, $startDate, $endDate, $projectId, $sortColumn, $sortOrder);
    } elseif ($role === 'Team Member') {
        $tasks = $taskManager->searchTasksForTeamMember($username, $priority, $status, $startDate, $endDate, $projectId, $sortColumn, $sortOrder);
    }

    // Redirect to the results page with the filtered data
    $_SESSION['tasks'] = $tasks;
    header('Location: ./task_results.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Search</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="main-container">
    <aside class="side-nav">
        <nav>
            <ul>
                <?php if ($role === 'Manager'): ?>
                    <li><a href="./dashboard.php">Dashboard</a></li>
                    <li><a href="./addproject.php">Add Project</a></li>
                    <li><a href="./unassigned_projects.php">Allocate Team Leader</a></li>
                    <li><a href="./task_search.php">Task Search</a></li>
                    <li><a href="./logout.php">Logout</a></li>
                <?php elseif ($role === 'Team Leader'): ?>
                    <li><a href="./team_leader_dashboard.php">🏠 Dashboard</a></li>
                    <li><a href="./create_task.php">📋 Create Tasks</a></li>
                    <li><a href="./task_search.php">Task Search</a></li>
                    <li><a href="./logout.php">Logout</a></li>
                <?php elseif ($role === 'Team Member'): ?>
                    <li><a href="team_dashboard.php">🏠 Dashboard</a></li>
                    <li 
                        class="highlight"
                        style="<?= $hasNewTasks ? 'background: yellow; font-weight: bold;' : '' ?>">
                        <a href="accept_tasks.php">✅ Accept Task Assignments</a>
                    </li>
                    <li><a href="search_update_tasks.php">🔍 Search and Update Tasks</a></li>
                    <li><a href="./task_search.php">Task Search</a></li>
                    <li><a href="./logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
        <div class="content">
            <h1>Task Search</h1>
            <form method="POST">
                <label for="priority">Priority:</label>
                <select name="priority" id="priority">
                    <option value="">All</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>

                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="">All</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>

                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date">

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date">

                <label for="project_id">Project:</label>
                <select name="project_id" id="project_id">
                    <option value="">All</option>
                    <!-- Dynamically populate project options -->
                </select>

                <button type="submit" name="search">Search</button>
            </form>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
