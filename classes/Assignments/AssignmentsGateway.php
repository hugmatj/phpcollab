<?php


namespace phpCollab\Assignments;

use phpCollab\Database;

/**
 * Class AssignmentsGateway
 * @package phpCollab\Assignments
 */
class AssignmentsGateway
{
    protected $db;
    protected $initrequest;

    /**
     * AssignmentsGateway constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->initrequest = $GLOBALS['initrequest'];
    }

    public function deleteAssignments($assignmentIds)
    {
        $assignmentIds = explode(',', $assignmentIds);
        $placeholders = str_repeat ('?, ', count($assignmentIds)-1) . '?';
        $sql = "DELETE FROM {$this->tableCollab['assignments']} WHERE task IN ($placeholders)";
        $this->db->query($sql);
        return $this->db->execute($assignmentIds);
    }


}
