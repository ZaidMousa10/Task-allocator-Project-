<?php
class TeamMemberManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAvailableTeamMembers() {
        $stmt = $this->pdo->query("SELECT id, name FROM team_members WHERE is_active = 1");
        return $stmt->fetchAll();
    }
}
?>
