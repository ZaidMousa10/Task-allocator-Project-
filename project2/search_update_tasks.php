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

// Fetch tasks assigned to this team member
$assignedTasks = $taskManager->getAssignedTasks($teamMemberId);

// Handle search
$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchQuery = $_POST['search_query'];
    $searchResults = $taskManager->searchTasksByMember($teamMemberId, $searchQuery);
}

// Handle task progress and status updates
// Handle task progress and status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    if (empty($_POST['assignment_id'])) {
        die("Error: assignment_id is missing in the request.");
    }

    $assignmentId = $_POST['assignment_id'];
    $progress = isset($_POST['progress']) ? (int)$_POST['progress'] : null;
    $status = $_POST['status'] ?? null;

    // Mandatory fields validation
    if ($progress === null || $status === null) {
        $updateError = "Error: Both Progress and Status are required.";
    } else {
        // Synchronize progress and status
        $validationError = false;

        if ($status === 'Completed' && $progress !== 100) {
            $updateError = "Error: Progress must be 100% to mark the task as Completed.";
            $validationError = true;
        } elseif ($status === 'In Progress' && ($progress <= 0 || $progress >= 100)) {
            $updateError = "Error: Progress must be greater than 0% and less than 100% to mark the task as In Progress.";
            $validationError = true;
        } elseif ($status === 'Pending' && $progress !== 0) {
            $updateError = "Error: Progress must be 0% to mark the task as Pending.";
            $validationError = true;
        }

        if (!$validationError) {
            try {
                $taskManager->updateTaskProgressAndStatus($assignmentId, $progress, $status);
                $updateMessage = "Task updated successfully.";
                $assignedTasks = $taskManager->getAssignedTasks($teamMemberId); // Refresh the assigned tasks
            } catch (Exception $e) {
                $updateError = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search and Update Tasks</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>

    <div class="main-container">
        <aside class="side-nav">
            <ul>
                <li><a href="./team_dashboard.php">🏠 Dashboard</a></li>
                <li><a href="./accept_tasks.php">✅ Accept Task Assignments</a></li>
                <li><a href="./search_update_tasks.php">🔍 Search and Update Tasks</a></li>
                <li><a href="./task_search.php">Task Search</a></li>
                <li><a href="./logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="content">
            <h1>Search and Update Tasks</h1>

            <!-- Search Section -->
            <section>
                <h2>Search Tasks</h2>
                <form method="POST" class="task-form">
                    <fieldset>
                        <legend>Search</legend>
                        <div class="form-group">
                            <label for="search_query">Search by Task Name or ID:</label>
                            <input type="text" id="search_query" name="search_query" placeholder="Enter task name or ID">
                        </div>
                        <button type="submit" name="search">Search</button>
                    </fieldset>
                </form>

                <?php if (!empty($searchResults)): ?>
                    <h3>Search Results</h3>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Task ID</th>
                                <th>Task Name</th>
                                <th>Status</th>
                                <th>Progress</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $task): ?>
                                <tr>
                                    <td><?= htmlspecialchars($task['task_id']) ?></td>
                                    <td><?= htmlspecialchars($task['title']) ?></td>
                                    <td><?= htmlspecialchars($task['status']) ?></td>
                                    <td><?= htmlspecialchars($task['completion_percentage']) ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($_POST['search'])): ?>
                    <p>No tasks found matching your search query.</p>
                <?php endif; ?>
            </section>

            <!-- Assigned Tasks Section -->
            <section>
    <h2>Assigned Tasks</h2>
    <?php if (!empty($assignedTasks)): ?>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Task Name</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignedTasks as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['task_id']) ?></td>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['status']) ?></td>
                        <td><?= htmlspecialchars($task['contribution_percentage']) ?>%</td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($task['assignment_id'] ?? '') ?>">
                                <label for="progress">Progress:</label>
                                <input type="range" name="progress" min="0" max="100" value="<?= htmlspecialchars($task['contribution_percentage']) ?>"oninput="this.nextElementSibling.value = this.value">
                                <output><?= htmlspecialchars($task['contribution_percentage']) ?></output>
                                <label for="status">Status:</label>
                                <select name="status" required>
                                    <option value="Pending" <?= $task['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="In Progress" <?= $task['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="Completed" <?= $task['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                                <button type="submit" name="update_task" class="save-button">Save Changes</button>
                                <button type="button" class="cancel-button" onclick="window.location.href='./team_dashboard.php';">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tasks assigned to you yet.</p>
    <?php endif; ?>

    <?php if (isset($updateMessage)): ?>
        <div class="success-message"><?= htmlspecialchars($updateMessage) ?></div>
    <?php elseif (isset($updateError)): ?>
        <div class="error-message"><?= htmlspecialchars($updateError) ?></div>
    <?php endif; ?>
</section>
        </div>
    </div>

    <?php include './footer.php'; ?>
</body>
</html>
