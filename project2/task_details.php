<?php
session_start();
require './db.inc.php';
require './TaskManager.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure a task ID is provided
if (!isset($_GET['task_id'])) {
    die("Task ID is required.");
}

$taskId = $_GET['task_id'];
$taskManager = new TaskManager($pdo);

// Fetch task details, including the project name
$taskDetails = $taskManager->getTaskDetailsWithProjectName($taskId);
if (!$taskDetails) {
    die("Task not found.");
}


// Fetch team members for the task
$teamMembers = $taskManager->getTeamMembersByTask($taskId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
<?php include './header.php'; ?>
<div class="navigation-buttons">
            <a href="./task_results.php" class="button">Return to Previous Page</a><br>
        </div>
    <div class="task-container">
        <!-- Task Details Section -->
        <div class="task-details">
            <h2>Task Details</h2>
            <p><strong>Task ID:</strong> <?= htmlspecialchars($taskDetails['task_id'] ?? 'N/A'); ?></p>
            <p><strong>Task Name:</strong> <?= htmlspecialchars($taskDetails['title'] ?? 'N/A'); ?></p>
            <p><strong>Description:</strong> <?= htmlspecialchars($taskDetails['description'] ?? 'N/A'); ?></p>
            <p><strong>Project:</strong> <?= htmlspecialchars($taskDetails['project_name'] ?? 'N/A'); ?></p>
            <p><strong>Start Date:</strong> <?= htmlspecialchars($taskDetails['start_date'] ?? 'N/A'); ?></p>
            <p><strong>End Date:</strong> <?= htmlspecialchars($taskDetails['end_date'] ?? 'N/A'); ?></p>
            <p><strong>Completion Percentage:</strong> <?= htmlspecialchars($taskDetails['completion_percentage'] ?? '0'); ?>%</p>
            <p><strong>Status:</strong> <?= htmlspecialchars($taskDetails['status'] ?? 'N/A'); ?></p>
            <p><strong>Priority:</strong> <?= htmlspecialchars($taskDetails['priority'] ?? 'N/A'); ?></p>
        </div>

        <!-- Team Members Section -->
        <div class="team-members">
            <h2>Team Members</h2>
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Effort Allocated (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teamMembers)): ?>
                        <?php foreach ($teamMembers as $member): ?>
                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($member['photo'] ?? './default.jpg') ?>" 
                                         alt="Member Photo" 
                                         class="team-member-photo">
                                </td>
                                <td><?= htmlspecialchars($member['member_id'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($member['name'] ?? 'N/A'); ?></td>
                                <td><?= htmlspecialchars($member['start_date'] ?? 'N/A'); ?></td>
                                <td class="<?= ($member['end_date'] === null) ? 'in-progress' : ''; ?>">
                                    <?= htmlspecialchars($member['end_date'] ?? 'In Progress'); ?>
                                </td>
                                <td><?= htmlspecialchars($member['effort_allocated'] ?? '0'); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No team members assigned to this task.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
