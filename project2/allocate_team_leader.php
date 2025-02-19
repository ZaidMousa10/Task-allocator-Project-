<?php
session_start();
require './db.inc.php';
require './ProjectManager.php';

// Restrict access to Managers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    echo "Access Denied: Your role is " . ($_SESSION['role'] ?? 'undefined');
    exit;
}

$projectManager = new ProjectManager($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'];
    $projectDetails = $projectManager->getProjectDetails($projectId);
    $projectFiles = $projectManager->getProjectFiles($projectId);
    $teamLeaders = $projectManager->getTeamLeaders();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocate Team Leader</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="container">
        <aside class="side-nav">
            <nav>
                <ul>
                    <li><a href="./dashboard.php">Home</a></li>
                    <li><a href="./unassigned_projects.php">Back</a></li>
                </ul>
            </nav>
        </aside>
        <form method="POST" action="./confirm_allocation.php">
            <h1>Assign Team Leader</h1>
            <fieldset>
                <legend>Project Details</legend>
                <label for="project_id">Project ID:</label>
                <input type="text" id="project_id" name="project_id" value="<?= htmlspecialchars($projectDetails['project_id']) ?>" readonly>
                
                <label for="project_title">Project Title:</label>
                <input type="text" id="project_title" value="<?= htmlspecialchars($projectDetails['project_title']) ?>" readonly>
                
                <label for="description">Description:</label>
                <textarea id="description" readonly><?= htmlspecialchars($projectDetails['description']) ?></textarea>
                
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" value="<?= htmlspecialchars($projectDetails['customer_name']) ?>" readonly>
                
                <label for="total_budget">Total Budget:</label>
                <input type="text" id="total_budget" value="<?= htmlspecialchars($projectDetails['total_budget']) ?>" readonly>
                
                <label for="start_date">Start Date:</label>
                <input type="text" id="start_date" value="<?= htmlspecialchars($projectDetails['start_date']) ?>" readonly>
                
                <label for="end_date">End Date:</label>
                <input type="text" id="end_date" value="<?= htmlspecialchars($projectDetails['end_date']) ?>" readonly>
            </fieldset>
            
            <?php if (!empty($projectFiles)): ?>
                <fieldset>
                    <legend>Supporting Documents</legend>
                    <ul class="document-list">
                        <?php foreach ($projectFiles as $file): ?>
                            <li>
                                <a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank" class="document-link">
                                    <?= htmlspecialchars($file['file_title']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </fieldset>
            <?php endif; ?>
            
            <fieldset>
                <legend>Select Team Leader</legend>
                <label for="team_leader_id">Team Leader:</label>
                <select id="team_leader_id" name="team_leader_id" required>
                    <option value="">Select a Team Leader</option>
                    <?php foreach ($teamLeaders as $leader): ?>
                        <option value="<?= htmlspecialchars($leader['id']) ?>"><?= htmlspecialchars($leader['display_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </fieldset>
            <button type="submit">Confirm Allocation</button>
        </form>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
