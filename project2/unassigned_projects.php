<?php
session_start();
require './db.inc.php'; // Include database connection
require './ProjectManager.php'; // Include ProjectManager class

// Restrict access to Managers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    echo "Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined');
    exit;
}

$projectManager = new ProjectManager($pdo);

// Fetch unassigned projects
$projects = $projectManager->getUnassignedProjects();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unassigned Projects</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="main-container">
        <!-- Sidebar Navigation -->
        <aside class="side-nav">
            <ul>
                <li><a href="./dashboard.php">🏠 Home</a></li>
            </ul>
        </aside>

        <!-- Content Area -->
        <div class="content">
            <div class="content-frame">
                <h1 class="page-title">Unassigned Projects</h1>

                <?php if (empty($projects)): ?>
                    <p class="no-projects">No unassigned projects available at the moment.</p>
                <?php else: ?>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Project ID</th>
                                <th>Project Title</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?= htmlspecialchars($project['project_id']) ?></td>
                                    <td><?= htmlspecialchars($project['project_title']) ?></td>
                                    <td><?= htmlspecialchars($project['start_date']) ?></td>
                                    <td><?= htmlspecialchars($project['end_date']) ?></td>
                                    <td>
                                        <form method="POST" action="./allocate_team_leader.php">
                                            <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['project_id']) ?>">
                                            <button type="submit" class="action-button">Allocate</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
