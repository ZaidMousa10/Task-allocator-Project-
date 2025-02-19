<?php
session_start();
require './db.inc.php'; // Database connection

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Team Member') {
    header('Location: ./unauthorized.php');
    exit;
}

$tasks = [];
$message = "";

// Handle search request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = $_POST['filter'] ?? '';
    $search = $_POST['search'] ?? '';

    if ($filter && $search) {
        $query = "SELECT * FROM tasks WHERE $filter LIKE :search";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['search' => "%$search%"]);
        $tasks = $stmt->fetchAll();
    } else {
        $message = "Please select a filter and enter a search term.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Tasks</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="container">
        <h1>Search Tasks</h1>
        <?php if ($message): ?>
            <div class="error-message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="filter">Filter By:</label>
            <select id="filter" name="filter" required>
                <option value="task_id">Task ID</option>
                <option value="task_name">Task Name</option>
                <option value="project_name">Project Name</option>
            </select>
            <label for="search">Search Term:</label>
            <input type="text" id="search" name="search" required>
            <button type="submit">Search</button>
        </form>

        <?php if ($tasks): ?>
            <table class="details-table">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Task Name</th>
                        <th>Project Name</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['task_id']) ?></td>
                            <td><?= htmlspecialchars($task['task_name']) ?></td>
                            <td><?= htmlspecialchars($task['project_name']) ?></td>
                            <td><?= htmlspecialchars($task['progress']) ?>%</td>
                            <td><?= htmlspecialchars($task['status']) ?></td>
                            <td>
                                <a href="./update_task.php?task_id=<?= urlencode($task['task_id']) ?>">Update</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
