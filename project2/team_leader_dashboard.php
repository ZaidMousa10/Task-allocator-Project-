<?php
session_start();
require './db.inc.php'; // Include database connection
// Restrict access to Team Leaders
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Team Leader') {
    echo "Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined');
    exit;
}

if (!isset($_SESSION['username'])) {
    echo "Access Denied: Username not found in session.";
    exit;
}

$username = $_SESSION['username'];

// Fetch the team_leader_id from the database
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND role = 'Team Leader'");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();
$team_leader = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team_leader) {
    echo "Access Denied: Team Leader not found in database.";
    exit;
}

$team_leader_id = $team_leader['id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Leader Dashboard</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
<?php include './header.php'; ?>
    <!-- Main Content -->
    <div class="main-container">
        <aside class="side-nav">
            <ul>
                <li><a href="./team_leader_dashboard.php">🏠 Dashboard</a></li>
                <li><a href="./create_task.php">📋 Create Tasks</a></li>
                <li><a href="./task_search.php">🔍Task Search</a></li>
                <li><a href="./logout.php">Logout</a></li>
            </ul>
        </aside>

        <div class="content">
            <h2>Assigned Projects and Tasks</h2>

            <?php
            // Fetch projects and their associated tasks for the current Team Leader
            $stmt = $pdo->prepare("
                SELECT p.project_title, p.description AS project_description, p.start_date AS project_start_date, p.end_date AS project_end_date,
                       t.task_id, t.title AS task_title, t.status AS task_status, t.priority AS task_priority, t.start_date AS task_start_date
                FROM projects p
                LEFT JOIN tasks t ON p.id = t.project_id
                WHERE p.team_leader_id = :team_leader_id
                ORDER BY p.project_title, t.start_date ASC
            ");
            $stmt->execute([':team_leader_id' => $team_leader_id]);
            $projectsAndTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (empty($projectsAndTasks)): ?>
                <p class="no-tasks">No projects or tasks assigned to you yet.</p>
            <?php else: ?>
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Task Name</th>
                            <th>Task Start Date</th>
                            <th>Task Status</th>
                            <th>Task Priority</th>
                            <th>Team Allocation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectsAndTasks as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['task_id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['task_title'] ?? 'No Task') ?></td>
                                <td><?= htmlspecialchars($row['task_start_date'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['task_status'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['task_priority'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($row['task_id'])): ?>
                                        <a href="./assign_team_members.php?task_id=<?= htmlspecialchars($row['task_id']) ?>" class="btn-assign">
                                            Assign Team Members
                                        </a>
                                    <?php else: ?>
                                        <span class="not-available">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <?php include './footer.php'; ?>
    </footer>
</body>
</html>
