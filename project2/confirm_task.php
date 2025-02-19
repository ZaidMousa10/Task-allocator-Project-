<?php
session_start();
require './db.inc.php';
require './TaskManager.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Restrict access to Team Members
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Team Member') {
    die("Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined'));
}

// Get Task ID
if (!isset($_GET['task_id'])) {
    die("Task ID is required.");
}
$taskId = $_GET['task_id'];

// Fetch the team member ID
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role = 'Team Member'");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$teamMember = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teamMember) {
    die("Access Denied: Team Member not found.");
}

$teamMemberId = $teamMember['id'];

// Initialize TaskManager
$taskManager = new TaskManager($pdo);

// Fetch task details
$taskDetails = $taskManager->getTaskDetails($taskId);
if (!$taskDetails) {
    die("Invalid Task ID.");
}

// Handle Accept/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'accept') {
        $taskManager->acceptTask($taskId, $teamMemberId);
        $_SESSION['success_message'] = "Task successfully accepted and activated.";
        header('Location: ./accept_success.php');
        exit;
    } elseif ($action === 'reject') {
        $taskManager->rejectTask($taskId, $teamMemberId);
        $_SESSION['success_message'] = "Task assignment successfully rejected.";
        header('Location: ./reject_success.php');
        exit;
    }
}

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
        <div class="content">
            <h1>Task Details</h1>

            <form method="POST">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Task ID</strong></td>
                            <td><?= htmlspecialchars($taskDetails['task_id']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Task Title</strong></td>
                            <td><?= htmlspecialchars($taskDetails['title']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Description</strong></td>
                            <td><?= htmlspecialchars($taskDetails['description']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Priority</strong></td>
                            <td><?= htmlspecialchars($taskDetails['priority']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td><?= htmlspecialchars($taskDetails['status']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Effort</strong></td>
                            <td><?= htmlspecialchars($taskDetails['effort']) ?> man-months</td>
                        </tr>
                        <tr>
                            <td><strong>Role</strong></td>
                            <td><?= htmlspecialchars($taskDetails['role']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Start Date</strong></td>
                            <td><?= htmlspecialchars($taskDetails['start_date']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>End Date</strong></td>
                            <td><?= htmlspecialchars($taskDetails['end_date']) ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="form-actions">
                    <button type="submit" name="action" value="accept" class="accept-button">Accept Task</button>
                    <button type="submit" name="action" value="reject" class="reject-button">Reject Task</button>
                </div>
            </form>
        </div>
    </div>

    <?php include './footer.php'; ?>
</body>
</html>
