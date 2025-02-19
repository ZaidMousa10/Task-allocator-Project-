<?php
require './db.inc.php';
require './TaskManager.php';

$taskManager = new TaskManager($pdo);
$tasks = $taskManager->getTasksWithoutTeamLeader();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
<?php include './header.php'; ?>
    <div class="container">
        <h1>Task List</h1>
        <table class="details-table">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Task Name</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Team Allocation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['id']) ?></td>
                        <td><?= htmlspecialchars($task['name']) ?></td>
                        <td><?= htmlspecialchars($task['start_date']) ?></td>
                        <td><?= htmlspecialchars($task['status']) ?></td>
                        <td><?= htmlspecialchars($task['priority']) ?></td>
                        <td><a href="assign_team_member.php?task_id=<?= $task['id'] ?>">Assign Team Members</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
