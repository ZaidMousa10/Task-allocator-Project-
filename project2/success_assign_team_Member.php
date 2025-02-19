<?php
class SuccessHandler {
    private $successMessage;
    private $redirectUrl;
    private $addAnotherTeamMemberUrl;

    public function __construct($successMessage = "Operation completed successfully!", $redirectUrl = "./team_leader_dashboard.php", $addAnotherTeamMemberUrl = "./assign_team_members.php") {
        $this->successMessage = $successMessage;
        $this->redirectUrl = $redirectUrl;
        $this->addAnotherTeamMemberUrl = $addAnotherTeamMemberUrl;
    }

    public function display($taskId) {

        // Redirect if success message is not set
        if (!isset($_SESSION['success_message'])) {
            header('Location: ' . $this->redirectUrl);
            exit;
        }

        $message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);

        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Success</title>
            <link rel="stylesheet" href="./styles.css">
        </head>
        <body>
            <?php include './header.php'; ?>
            <div class="container">
                <div class="success-message">
                    <h1>Success!</h1>
                    <p><?= htmlspecialchars($message) ?></p>
                    <!-- Pass the task ID in the "Add Another Team Member" URL -->
                    <a class="home-link" href="<?= htmlspecialchars($this->addAnotherTeamMemberUrl . '?task_id=' . $taskId) ?>">Add Another Team Member</a>
                    <a class="home-link" href="<?= htmlspecialchars($this->redirectUrl) ?>">Finish Allocation</a>
                </div>
            </div>
            <?php include './footer.php'; ?>
        </body>
        </html>
        <?php
    }
}
