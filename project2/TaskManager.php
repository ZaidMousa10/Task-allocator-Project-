<?php
class TaskManager {
    private $pdo;

    public function __construct($db) {
        $this->pdo = $db;
    }

    // Get Active Projects for a Project Leader
    public function getActiveProjects($projectLeaderId) {
        $query = "SELECT id, project_title, start_date, end_date, team_leader_id 
                  FROM projects 
                  WHERE team_leader_id = :team_leader_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':team_leader_id', $projectLeaderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Validate Task Dates
    public function validateTaskDates($projectId, $startDate, $endDate) {
        $query = "SELECT start_date, end_date FROM projects WHERE id = :project_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        $project = $stmt->fetch();

        if (!$project) {
            return "Invalid project selected.";
        }

        if ($startDate < $project['start_date']) {
            return "Task start date cannot be earlier than the project start date.";
        }

        if ($endDate > $project['end_date']) {
            return "Task end date cannot exceed the project end date.";
        }

        return null;
    }

    // Create Task
    public function createTask($data) {
        $query = "INSERT INTO tasks (task_id, title, description, project_id, start_date, end_date, effort, status, priority) 
                  VALUES (:task_id, :title, :description, :project_id, :start_date, :end_date, :effort, :status, :priority)";
        $stmt = $this->pdo->prepare($query);
    
        if (!$stmt->execute($data)) {
            // Output PDO error info
            print_r($stmt->errorInfo());
            return false;
        }
        return true;
    }

    // Get Tasks Without Team Leader
    public function getTasksWithoutTeamLeader() {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE team_leader_id IS NULL ORDER BY start_date ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Fetch Task Details by ID
    public function getTaskDetails($taskId) {
        $stmt = $this->pdo->prepare("
            SELECT t.task_id, t.title, t.description, t.priority, t.status, t.effort, t.start_date, t.end_date, 
                   a.role
            FROM tasks t
            LEFT JOIN task_assignments a ON t.task_id = a.task_id
            WHERE t.task_id = :taskId
        ");
        $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    // Fetch Task Details by Custom Identifier
    public function getTaskDetailsAssign($identifier, $isTaskId = true) {
        $column = $isTaskId ? 'task_id' : 'id'; // Decide column to filter by
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE $column = :identifier");
        $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get Tasks by Project
    public function getTasksByProject($projectId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE project_id = :projectId");
        $stmt->bindParam(':projectId', $projectId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get Tasks by Leader
    public function getTasksByLeader($teamLeaderId) {
        $stmt = $this->pdo->prepare("
            SELECT t.*
            FROM tasks t
            JOIN projects p ON t.project_id = p.id
            WHERE p.team_leader_id = :teamLeaderId
        ");
        $stmt->bindParam(':teamLeaderId', $teamLeaderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Assign a Team Member to a Task
    public function assignTeamMember($taskId, $teamMemberId, $role, $contribution) {
        $stmt = $this->pdo->prepare("
            INSERT INTO task_assignments (task_id, user_id, role, contribution_percentage)
            VALUES (:taskId, :teamMemberId, :role, :contribution)
        ");
        $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
        $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        $stmt->bindParam(':contribution', $contribution, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Accept a Task (Update accept column to 'true')
public function acceptTask($taskId, $teamMemberId) {
    $stmt = $this->pdo->prepare("
        UPDATE task_assignments 
        SET accept = 'true' 
        WHERE task_id = :taskId AND user_id = :teamMemberId
    ");
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
    $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
    return $stmt->execute();
}

// Reject a Task (Delete the row for the task assignment)
public function rejectTask($taskId, $teamMemberId) {
    $stmt = $this->pdo->prepare("
        DELETE FROM task_assignments 
        WHERE task_id = :taskId AND user_id = :teamMemberId
    ");
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
    $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
    return $stmt->execute();
}

    // Get Available Team Members
    public function getAvailableTeamMembers() {
        $stmt = $this->pdo->query("SELECT id, username AS name FROM users WHERE role = 'Team Member'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Validate Total Contribution for a Task
    public function validateTotalContribution($taskId, $newContribution) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(contribution_percentage) as total_contribution
            FROM task_assignments
            WHERE task_id = :taskId
        ");
        $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalContribution = $result['total_contribution'] ?? 0;

        if (($totalContribution + $newContribution) > 100) {
            return false;
        }
        return true;
    }
    // Get tasks assigned to a team member
    public function getAssignedTasks($teamMemberId) {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.task_id, 
                t.title, 
                ta.status, -- Fetch status from task_assignments
                ta.contribution_percentage AS contribution_percentage,
                ta.id AS assignment_id
            FROM 
                tasks t
            JOIN 
                task_assignments ta ON t.task_id = ta.task_id
            WHERE 
                ta.user_id = :teamMemberId 
                AND ta.accept = 'true'
        ");
        $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update task progress and status
    // Update task progress and status
public function updateTaskProgressAndStatus($assignmentId, $progress, $status) {
    // Ensure the assignment exists
    $stmt = $this->pdo->prepare("
        SELECT task_id 
        FROM task_assignments 
        WHERE id = :assignmentId
    ");
    $stmt->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
    $stmt->execute();
    $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$assignment) {
        throw new Exception("Task assignment not found for the given ID.");
    }

    $taskId = $assignment['task_id'];

    // Update only the specific task assignment
    $stmt = $this->pdo->prepare("
        UPDATE task_assignments
        SET contribution_percentage = :progress, status = :status
        WHERE id = :assignmentId
    ");
    $stmt->bindParam(':progress', $progress, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':assignmentId', $assignmentId, PDO::PARAM_INT);
    $stmt->execute();

    // Optionally reflect changes in the tasks table (if required)
    $stmt = $this->pdo->prepare("
        UPDATE tasks
        SET status = :status
        WHERE task_id = :taskId
    ");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
    $stmt->execute();

    return true;
}

    // Search tasks by team member and query
    public function searchTasksByMember($teamMemberId, $query) {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.task_id, 
                t.title, 
                ta.status, -- Status now fetched from task_assignments
                ta.contribution_percentage AS completion_percentage
            FROM 
                tasks t
            JOIN 
                task_assignments ta ON t.task_id = ta.task_id
            WHERE 
                ta.user_id = :teamMemberId 
                AND (t.task_id LIKE :query OR t.title LIKE :query)
        ");
        $query = '%' . $query . '%';
        $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
        $stmt->bindParam(':query', $query, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

// Update task progress
public function updateTaskProgress($taskId, $teamMemberId, $progress) {
    $stmt = $this->pdo->prepare("
        UPDATE tasks t
        JOIN task_assignments ta ON t.task_id = ta.task_id
        SET t.completion_percentage = :progress
        WHERE t.task_id = :taskId AND ta.user_id = :teamMemberId
    ");
    $stmt->bindParam(':progress', $progress, PDO::PARAM_INT);
    $stmt->bindParam(':taskId', $taskId, PDO::PARAM_STR);
    $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
    return $stmt->execute();
}
public function getNewlyAssignedTasks($teamMemberId) {
    $stmt = $this->pdo->prepare("
        SELECT t.task_id, t.title, t.start_date, t.status, t.priority
        FROM tasks t
        JOIN task_assignments a ON t.task_id = a.task_id
        WHERE a.user_id = :teamMemberId AND (a.accept IS NULL OR a.accept = '')
    ");
    $stmt->bindParam(':teamMemberId', $teamMemberId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function searchTasksForManager($priority, $status, $startDate, $endDate, $projectId) {
    $query = "
        SELECT t.task_id, t.title, t.status, t.priority, t.start_date, t.end_date, 
               t.completion_percentage, p.project_title AS project
        FROM tasks t
        LEFT JOIN projects p ON t.project_id = p.id
        WHERE 1=1
    ";
    $params = [];

    if ($priority) {
        $query .= " AND t.priority = :priority";
        $params[':priority'] = $priority;
    }
    if ($status) {
        $query .= " AND t.status = :status";
        $params[':status'] = $status;
    }
    if ($startDate) {
        $query .= " AND t.start_date >= :start_date";
        $params[':start_date'] = $startDate;
    }
    if ($endDate) {
        $query .= " AND t.end_date <= :end_date";
        $params[':end_date'] = $endDate;
    }
    if ($projectId) {
        $query .= " AND t.project_id = :project_id";
        $params[':project_id'] = $projectId;
    }

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function searchTasksForTeamLeader($username, $priority, $status, $startDate, $endDate, $projectId) {
    $query = "
        SELECT t.* FROM tasks t
        JOIN projects p ON t.project_id = p.id
        JOIN users u ON p.team_leader_id = u.id
        WHERE u.username = :username";
    $params = [':username' => $username];

    if ($priority) {
        $query .= " AND t.priority = :priority";
        $params[':priority'] = $priority;
    }
    if ($status) {
        $query .= " AND t.status = :status";
        $params[':status'] = $status;
    }
    if ($startDate) {
        $query .= " AND t.start_date >= :start_date";
        $params[':start_date'] = $startDate;
    }
    if ($endDate) {
        $query .= " AND t.end_date <= :end_date";
        $params[':end_date'] = $endDate;
    }
    if ($projectId) {
        $query .= " AND t.project_id = :project_id";
        $params[':project_id'] = $projectId;
    }

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function searchTasksForTeamMember($username, $priority, $status, $startDate, $endDate, $projectId) {
    $query = "
        SELECT t.* FROM tasks t
        JOIN task_assignments ta ON t.task_id = ta.task_id
        JOIN users u ON ta.user_id = u.id
        WHERE u.username = :username";
    $params = [':username' => $username];

    if ($priority) {
        $query .= " AND t.priority = :priority";
        $params[':priority'] = $priority;
    }
    if ($status) {
        $query .= " AND t.status = :status";
        $params[':status'] = $status;
    }
    if ($startDate) {
        $query .= " AND t.start_date >= :start_date";
        $params[':start_date'] = $startDate;
    }
    if ($endDate) {
        $query .= " AND t.end_date <= :end_date";
        $params[':end_date'] = $endDate;
    }
    if ($projectId) {
        $query .= " AND t.project_id = :project_id";
        $params[':project_id'] = $projectId;
    }

    $stmt = $this->pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function fetchTasksSorted($column, $order) {
    $query = "SELECT tasks.*, projects.project_title AS project 
              FROM tasks 
              LEFT JOIN projects ON tasks.project_id = projects.id 
              ORDER BY $column $order";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function fetchTasksForTeamLeader($username, $column, $order) {
    $query = "SELECT tasks.*, projects.project_title AS project 
              FROM tasks 
              LEFT JOIN projects ON tasks.project_id = projects.id 
              WHERE projects.team_leader_id = (
                  SELECT id FROM users WHERE username = :username
              )
              ORDER BY $column $order";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute(['username' => $username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function fetchTasksForTeamMember($username, $column, $order) {
    $query = "SELECT tasks.*, projects.project_title AS project 
              FROM tasks 
              LEFT JOIN projects ON tasks.project_id = projects.id 
              LEFT JOIN task_assignments ON tasks.task_id = task_assignments.task_id
              WHERE task_assignments.user_id = (
                  SELECT user_id FROM users WHERE username = :username
              )
              ORDER BY $column $order";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute(['username' => $username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getTeamMembersByTask($taskId) {
    $query = "
        SELECT 
            users.id AS member_id, 
            users.full_name AS name, 
            tasks.start_date, 
            CASE 
                WHEN task_assignments.contribution_percentage > 0 AND task_assignments.contribution_percentage < 100 THEN 'In Progress' 
                ELSE tasks.end_date 
            END AS end_date, 
            task_assignments.role, 
            task_assignments.contribution_percentage AS effort_allocated, 
            CASE 
                WHEN task_assignments.contribution_percentage > 0 AND task_assignments.contribution_percentage < 100 THEN 'In Progress' 
                ELSE task_assignments.accept 
            END AS status
        FROM 
            task_assignments
        INNER JOIN 
            users ON task_assignments.user_id = users.id
        INNER JOIN 
            tasks ON task_assignments.task_id = tasks.task_id
        WHERE 
            task_assignments.task_id = :task_id
            AND task_assignments.accept IS NOT NULL
    ";
    
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':task_id', $taskId, PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getTaskDetailsWithProjectName($taskId) {
    $query = "
        SELECT 
            tasks.task_id, 
            tasks.title, 
            tasks.description, 
            projects.project_title AS project_name, 
            tasks.start_date, 
            tasks.end_date, 
            tasks.completion_percentage, 
            tasks.status, 
            tasks.priority 
        FROM tasks
        LEFT JOIN projects ON tasks.project_id = projects.id
        WHERE tasks.task_id = :task_id
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute([':task_id' => $taskId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
public function getAllProjects() {
    $stmt = $this->pdo->prepare("SELECT project_id, project_title FROM projects");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getProjectsLedBy($username) {
    $stmt = $this->pdo->prepare("
        SELECT p.project_id, p.project_title
        FROM projects p
        JOIN users u ON p.project_leader = u.username
        WHERE u.username = :username AND u.role = 'Team Leader'
    ");
    $stmt->execute(['username' => $username]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getProjectsForMember($userId) {
    $stmt = $this->pdo->prepare("
        SELECT DISTINCT p.project_id, p.project_title
        FROM projects p
        JOIN tasks t ON p.project_id = t.project_id
        JOIN task_assignments ta ON t.task_id = ta.task_id
        WHERE ta.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Fetch tasks for Manager: All tasks from all projects
public function fetchTasksForManager($sortColumn, $sortOrder) {
    $stmt = $this->pdo->prepare("
        SELECT t.task_id, t.title, p.project_title AS project, t.status, t.priority, 
               t.start_date, t.end_date, t.completion_percentage
        FROM tasks t
        JOIN projects p ON t.project_id = p.project_id
        ORDER BY $sortColumn $sortOrder
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
