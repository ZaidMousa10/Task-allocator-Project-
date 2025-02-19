<?php
session_start();
require './db.inc.php';
require './TaskManager.php';

// Enable error reporting for debugging
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

// Fetch the team_leader_id from the database
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role = 'Team Leader'");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$team_leader = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team_leader) {
    die("Access Denied: Team Leader not found in the database.");
}

$team_leader_id = $team_leader['id'];

// Fetch a task ID associated with this team leader
if (!isset($_GET['task_id'])) {
    // Retrieve the first task ID associated with the team leader via the projects table
    $stmt = $pdo->prepare("
        SELECT t.task_id
        FROM tasks t
        JOIN projects p ON t.project_id = p.id
        WHERE p.team_leader_id = :team_leader_id
        LIMIT 1
    ");
    $stmt->bindParam(':team_leader_id', $team_leader_id, PDO::PARAM_INT);
    $stmt->execute();
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($task) {
        $taskId = $task['task_id'];
    } else {
        die("No tasks found for this team leader.");
    }
} else {
    $taskId = $_GET['task_id'];
}

// Initialize TaskManager and fetch task details
$taskManager = new TaskManager($pdo);
$taskDetails = $taskManager->getTaskDetailsAssign($taskId, true);

if (!$taskDetails) {
    die("Invalid Task ID.");
}

// Fetch available team members
$teamMembers = $taskManager->getAvailableTeamMembers();

if (!$teamMembers) {
    die("No team members available for assignment.");
}

$message = null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamMemberId = $_POST['team_member'] ?? null;
    $role = $_POST['role'] ?? null;
    $contribution = $_POST['contribution'] ?? null;

    // Validate input
    if (!$teamMemberId || !$role || !$contribution) {
        $error = "All fields are required.";
    } elseif ($contribution < 1 || $contribution > 100) {
        $error = "Contribution percentage must be between 1 and 100.";
    } elseif (!$taskManager->validateTotalContribution($taskId, $contribution)) {
        $error = "Total contribution percentage exceeds 100%.";
    } else {
        // Attempt to assign the team member
        if ($taskManager->assignTeamMember($taskId, $teamMemberId, $role, $contribution)) {
            // Store the success message in the session
            $_SESSION['success_message'] = "Team member successfully assigned to Task '{$taskDetails['title']}' as {$role}.";

            // Redirect to the SuccessHandler with the task ID
            require './success_assign_team_member.php';
            $successHandler = new SuccessHandler();
            $successHandler->display($taskId); // Pass the task ID here
            exit;
        } else {
            $error = "Failed to assign team member.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Team Member</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>
        <form method="POST">
    <div class="main-container">
    <aside class="side-nav">
            <ul>
                <li><a href="./team_leader_dashboard.php">🏠 Dashboard</a></li>
                <li><a href="./create_task.php">📋 Create Tasks</a></li>
                <li><a href="./task_search.php"> 🔍Task Search</a></li>
            </ul>
        </aside>
    <div class="content">
    <h1>Assign Team Member</h1>
    <form method="POST" class="task-form">
        <fieldset>
            <legend>Task Details</legend>
            <div class="form-group">
                <label for="task_id">Task ID:</label>
                <span><?= htmlspecialchars($taskDetails['task_id']) ?></span>
            </div>
            <div class="form-group">
                <label for="task_name">Task Name:</label>
                <span><?= htmlspecialchars($taskDetails['title']) ?></span>
            </div>
        </fieldset>

        <fieldset>
            <legend>Assign Team Member</legend>
            <div class="form-group">
                <label for="team_member">Team Member:</label>
                <select name="team_member" id="team_member" required>
                    <option value="">Select Team Member</option>
                    <?php foreach ($teamMembers as $member): ?>
                        <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="">Select Role</option>
                    <option value="Developer">Developer</option>
                    <option value="Designer">Designer</option>
                    <option value="Tester">Tester</option>
                    <option value="Analyst">Analyst</option>
                    <option value="Support">Support</option>
                </select>
            </div>

            <div class="form-group">
                <label for="contribution">Contribution (%):</label>
                <input type="number" name="contribution" id="contribution" min="1" max="100" required>
                
                <?php if ($message): ?>
                <div class="success-message"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
            </div>
        </fieldset>

        <button type="submit" class="create-task-button">Assign Team Member</button>
    </form>
    </div>
</div>
<?php include './footer.php'; ?>
</body>
</html>
