<?php
class ManageTeams {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // Add a new team
    public function addTeam($teamName, $description) {
        $sql = "INSERT INTO teams (team_name, description) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$teamName, $description]);
    }

    // Edit an existing team
    public function editTeam($teamId, $teamName, $description) {
        $sql = "UPDATE teams SET team_name = ?, description = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$teamName, $description, $teamId]);
    }

    // View all teams
    public function viewTeams() {
        $sql = "SELECT * FROM teams";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete a team
    public function deleteTeam($teamId) {
        $sql = "DELETE FROM teams WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$teamId]);
    }

    // Get team by ID
    public function getTeamById($teamId) {
        $sql = "SELECT * FROM teams WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$teamId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
