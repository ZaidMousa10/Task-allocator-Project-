<?php
class TaskSearchManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function searchTasks($filters, $role, $userId) {
        $query = "
            SELECT t.task_id, t.title, p.project_title AS project, t.status, t.priority, 
                   t.start_date, t.end_date, t.completion_percentage
            FROM tasks t
            JOIN projects p ON t.project_id = p.id
        ";

        // Add role-based filtering
        if ($role === 'Team Leader') {
            $query .= " WHERE p.team_leader_id = :userId";
        } elseif ($role === 'Team Member') {
            $query .= " WHERE t.task_id IN (
                SELECT ta.task_id FROM task_assignments ta WHERE ta.user_id = :userId
            )";
        } else {
            $query .= " WHERE 1=1"; // Manager can view all tasks
        }

        // Apply filters
        if (!empty($filters['priority'])) {
            $query .= " AND t.priority = :priority";
        }
        if (!empty($filters['status'])) {
            $query .= " AND t.status = :status";
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query .= " AND t.end_date BETWEEN :start_date AND :end_date";
        }
        if (!empty($filters['project_id'])) {
            $query .= " AND t.project_id = :project_id";
        }

        // Add sorting
        $query .= " ORDER BY t.task_id ASC";

        $stmt = $this->pdo->prepare($query);

        // Bind parameters
        if ($role !== 'Manager') {
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        }
        if (!empty($filters['priority'])) {
            $stmt->bindParam(':priority', $filters['priority'], PDO::PARAM_STR);
        }
        if (!empty($filters['status'])) {
            $stmt->bindParam(':status', $filters['status'], PDO::PARAM_STR);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $stmt->bindParam(':start_date', $filters['start_date'], PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $filters['end_date'], PDO::PARAM_STR);
        }
        if (!empty($filters['project_id'])) {
            $stmt->bindParam(':project_id', $filters['project_id'], PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
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
            <!-- Populate projects dynamically -->
        </select>
        
        <button type="submit" name="search">Search</button>
    </form>

    <?php if (!empty($tasks)): ?>
        <table>
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
                        <td><a href="./task_details.php?task_id=<?= htmlspecialchars($task['task_id']) ?>"><?= htmlspecialchars($task['task_id']) ?></a></td>
                        <td><?= htmlspecialchars($task['title']) ?></td>
                        <td><?= htmlspecialchars($task['project']) ?></td>
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
    <?php include './footer.php'; ?>
</body>
</html>

