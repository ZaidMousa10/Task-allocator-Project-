<?php
session_start();
require './db.inc.php'; 
require './TaskManager.php'; 

// Display errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to Team Leaders
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Team Leader') {
    die("Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined'));
}

// Ensure username is in the session
if (!isset($_SESSION['username'])) {
    die("Access Denied: Username not found in session.");
}

$username = $_SESSION['username'];

// Fetch the team_leader_id using the username
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role = 'Team Leader'");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$team_leader = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team_leader) {
    die("Access Denied: Team Leader not found in the database.");
}

$team_leader_id = $team_leader['id'];

$taskManager = new TaskManager($pdo);

// Fetch active projects for the team leader
$projects = $taskManager->getActiveProjects($team_leader_id);

$errorMessage = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $requiredFields = ['title', 'description', 'project_id', 'start_date', 'end_date', 'effort', 'status', 'priority'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errorMessage = "Please fill in all required fields.";
            break;
        }
    }

    // Validate date logic
    if ($_POST['start_date'] > $_POST['end_date']) {
        $errorMessage = "End date cannot be earlier than the start date.";
    }

    // Process the task creation if there are no validation errors
    if (!$errorMessage) {
        // Validate task_id or auto-generate it
        $task_id = !empty($_POST['task_id']) ? $_POST['task_id'] : uniqid('task_');
    
        // Check if the task_id already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE task_id = :task_id");
        $stmt->bindParam(':task_id', $task_id, PDO::PARAM_STR);
        $stmt->execute();
        $taskExists = $stmt->fetchColumn() > 0;
    
        // Auto-generate a new unique task_id if it already exists
        while ($taskExists) {
            $task_id = uniqid('task_');
            $stmt->bindParam(':task_id', $task_id, PDO::PARAM_STR);
            $stmt->execute();
            $taskExists = $stmt->fetchColumn() > 0;
        }
    
        // Prepare task data
        $taskData = [
            ':task_id' => $task_id,
            ':title' => $_POST['title'],
            ':description' => $_POST['description'],
            ':project_id' => $_POST['project_id'],
            ':start_date' => $_POST['start_date'],
            ':end_date' => $_POST['end_date'],
            ':effort' => $_POST['effort'],
            ':status' => $_POST['status'],
            ':priority' => $_POST['priority']
        ];
    
        // Attempt to create the task
        $result = $taskManager->createTask($taskData);
    
        if ($result === true) {
            // Set success message in session
            $_SESSION['success_message'] = "Task '{$task_id}' created successfully!";
            header('Location: ./SuccessCreate.php');
            exit;
        } else {
            $errorMessage = "Failed to create task. Please try again.";
        }
    }    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>

    <div class="main-container">
        <aside class="side-nav">
            <ul>
                <li><a href="./team_leader_dashboard.php">🏠 Dashboard</a></li>
            </ul>
        </aside>

        <div class="content">
            <h1>Create Task</h1>

            <?php if ($errorMessage): ?>
                <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <?php if (empty($projects)): ?>
                <p>No active projects found for you.</p>
            <?php else: ?>
                <form method="POST" class="task-form">
                    <fieldset>
                        <legend>Task Details</legend>

                        <div class="form-group">
                            <label for="task_id">Task ID (Optional - Auto-generated if empty or duplicate):</label>
                            <input type="text" id="task_id" name="task_id">
                        </div>


                        <div class="form-group">
                            <label for="title">Task Title:</label>
                            <input type="text" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="project_id">Project:</label>
                            <select id="project_id" name="project_id" required>
                                <option value="">Select a project</option>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= htmlspecialchars($project['id']) ?>">
                                        <?= htmlspecialchars($project['project_title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" required>
                        </div>

                        <div class="form-group">
                            <label for="effort">Effort (man-months):</label>
                            <input type="number" id="effort" name="effort" min="0.1" step="0.1" required>
                        </div>

                        <div class="form-group">
                            <label for="status">Status:</label>
                            <select id="status" name="status">
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="priority">Priority:</label>
                            <select id="priority" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </fieldset>

                    <button type="submit" class="create-task-button">Create Task</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
