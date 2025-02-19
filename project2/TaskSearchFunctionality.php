<?php
session_start();
require './db.inc.php';

// Role-based filtering
$whereClause = "";
if ($_SESSION['role'] === 'Team Leader') {
    $whereClause = "WHERE project_leader_id = :user_id";
} elseif ($_SESSION['role'] === 'Team Member') {
    $whereClause = "WHERE assigned_member_id = :user_id";
}

// Handle form submission
$tasks = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priority = $_POST['priority'] ?? null;
    $status = $_POST['status'] ?? null;
    $startDate = $_POST['start_date'] ?? null;
    $endDate = $_POST['end_date'] ?? null;
    $project = $_POST['project'] ?? null;

    $query = "SELECT * FROM tasks $whereClause";
    $conditions = [];
    $params = [];

    if ($priority) {
        $conditions[] = "priority = :priority";
        $params[':priority'] = $priority;
    }

    if ($status) {
        $conditions[] = "status = :status";
        $params[':status'] = $status;
    }

    if ($startDate && $endDate) {
        $conditions[] = "due_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    if ($project) {
        $conditions[] = "project_name = :project";
        $params[':project'] = $project;
    }

    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    $stmt = $pdo->prepare($query);
    if ($_SESSION['role'] !== 'Manager') {
        $params[':user_id'] = $_SESSION['user_id'];
    }
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Search</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
<?php include './header.php'; ?>
    <div class="container">
        <h1>Search Tasks</h1>
        <form method="POST" class="search-form">
            <label for="priority">Task Priority:</label>
            <select id="priority" name="priority">
                <option value="">Select Priority</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>

            <label for="status">Task Status:</label>
            <select id="status" name="status">
                <option value="">Select Status</option>
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date">

            <label for="project">Project:</label>
            <input type="text" id="project" name="project">

            <button type="submit">Search</button>
        </form>

        <?php if (!empty($tasks)): ?>
            <table class="task-table">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Start Date</th>
                        <th>Due Date</th>
                        <th>Completion %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><a href="./task_details.php?id=<?= htmlspecialchars($task['id']) ?>"><?= htmlspecialchars($task['id']) ?></a></td>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['project_name']) ?></td>
                            <td class="status <?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><?= htmlspecialchars($task['status']) ?></td>
                            <td class="priority <?= strtolower($task['priority']) ?>"><?= htmlspecialchars($task['priority']) ?></td>
                            <td><?= htmlspecialchars($task['start_date']) ?></td>
                            <td><?= htmlspecialchars($task['due_date']) ?></td>
                            <td><?= htmlspecialchars($task['completion_percentage']) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tasks found. Adjust your filters and try again.</p>
        <?php endif; ?>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
