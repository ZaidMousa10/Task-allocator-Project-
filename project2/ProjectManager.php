<?php
class ProjectManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUnassignedProjects() {
        $query = "SELECT project_id, project_title, start_date, end_date FROM projects WHERE team_leader_id IS NULL ORDER BY start_date ASC";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProjectDetails($projectId) {
        $query = "SELECT * FROM projects WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getProjectFiles($projectId) {
        // Ensure the query matches the database schema
        $query = "SELECT file_title, file_path 
                  FROM project_files 
                  WHERE project_id = (SELECT id FROM projects WHERE project_id = :project_id)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function getTeamLeaders() {
        $query = "SELECT id, CONCAT(full_name, ' - ', id) AS display_name FROM users WHERE role = 'Team Leader'";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assignTeamLeader($projectId, $teamLeaderId) {
        $query = "UPDATE projects SET team_leader_id = :team_leader_id WHERE project_id = :project_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':team_leader_id' => $teamLeaderId,
            ':project_id' => $projectId
        ]);
    }
}
?>
