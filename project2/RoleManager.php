<?php
class RoleManager {
    private $role;

    public function __construct($role) {
        $this->role = $role;
    }

    public function getMenuItems() {
        $menuItems = [];

        switch ($this->role) {
            case 'Manager':
                $menuItems[] = ['label' => 'Manage Teams', 'link' => './dashboard.php'];
                break;
            case 'Team Leader':
                $menuItems[] = ['label' => 'View Projects', 'link' => './team_leader_dashboard.php'];
                break;
            case 'Team Member':
                $menuItems[] = ['label' => 'My Tasks', 'link' => './tasks.php'];
                break;
            default:
                $menuItems[] = ['label' => 'Home', 'link' => './index.php'];
                break;
        }

        return $menuItems;
    }
}
?>
