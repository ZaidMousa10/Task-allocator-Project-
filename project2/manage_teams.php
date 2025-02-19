<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header('Location: ./unauthorized.php'); // Redirect to an unauthorized access page
    exit;
}

require './db.inc.php'; // Database connection
require './ManageTeams.php'; // Team management class

// Initialize ManageTeams class
$manageTeams = new ManageTeams($pdo);

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $teamName = trim($_POST['team_name']);
        $description = trim($_POST['description']);

        if ($manageTeams->addTeam($teamName, $description)) {
            $message = "Team added successfully!";
        } else {
            $message = "Failed to add team.";
        }
    } elseif (isset($_POST['edit'])) {
        $teamId = (int)$_POST['team_id'];
        $teamName = trim($_POST['team_name']);
        $description = trim($_POST['description']);

        if ($manageTeams->editTeam($teamId, $teamName, $description)) {
            $message = "Team updated successfully!";
        } else {
            $message = "Failed to update team.";
        }
    } elseif (isset($_POST['delete'])) {
        $teamId = (int)$_POST['team_id'];

        if ($manageTeams->deleteTeam($teamId)) {
            $message = "Team deleted successfully!";
        } else {
            $message = "Failed to delete team.";
        }
    }
}

// Fetch all teams
$teams = $manageTeams->viewTeams();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teams</title>
    <link rel="stylesheet" href="./form.css">
</head>
<body>
    <?php include './header.php'; ?>
    <div class="container">
        <h1>Manage Teams</h1>
        <?php if (!empty($message)): ?>
            <div class="message"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <fieldset>
                <legend>Add/Edit Team</legend>
                <input type="hidden" name="team_id" value="">

                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>

                <button type="submit" name="add" class="btn-primary">Add Team</button>
                <button type="submit" name="edit" class="btn-secondary">Edit Team</button>
            </fieldset>
        </form>

        <h2>Existing Teams</h2>
        <table class="details-table">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $team): ?>
                    <tr>
                        <td><?= htmlspecialchars($team['team_name']) ?></td>
                        <td><?= htmlspecialchars($team['description']) ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                <button type="submit" name="delete" class="btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include './footer.php'; ?>
</body>
</html>
