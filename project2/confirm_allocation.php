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
    $teamLeaderId = $_POST['team_leader_id'];

    if ($projectManager->assignTeamLeader($projectId, $teamLeaderId)) {
        $_SESSION['success_message'] = "Team Leader successfully allocated to Project $projectId.";
        header('Location: ./success_allocation.php');
        exit;
    } else {
        echo "Error: Failed to allocate team leader.";
    }
}
