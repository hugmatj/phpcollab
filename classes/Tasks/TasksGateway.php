<?php
namespace phpCollab\Tasks;

use phpCollab\Database;

/**
 * Class TasksGateway
 * @package phpCollab\Tasks
 */
class TasksGateway
{
    protected $db;
    protected $initrequest;

    /**
     * TasksGateway constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->initrequest = $GLOBALS['initrequest'];
    }

    /**
     * @param $assignedToId
     * @param null $sorting
     * @return mixed
     */
    public function getMyTasks($assignedToId, $sorting = null)
    {
        $whereStatement = " WHERE tas.assigned_to = :assigned_to";

        $this->db->query($this->initrequest["tasks"] . $whereStatement . $this->orderBy($sorting));

        $this->db->bind(':assigned_to', $assignedToId);

        return $this->db->resultset();
    }

    /**
     * @param $userId
     * @param null $sorting
     * @return mixed
     */
    public function getSubtasksAssignedToMe($userId, $sorting = null)
    {
        $whereStatement = ' WHERE subtas.assigned_to = :user_id';

        $this->db->query($this->initrequest["subtasks"] . $whereStatement . $this->orderBy($sorting));

        $this->db->bind(':user_id', $userId);

        return $this->db->resultset();
    }

    /**
     * @param $taskId
     * @return mixed
     */
    public function getTaskById($taskId)
    {
        $whereStatement = " WHERE tas.id = :task_id";

        $this->db->query($this->initrequest["tasks"] . $whereStatement);

        $this->db->bind(':task_id', $taskId);

        return $this->db->single();
    }

    public function getTasksById($taskIds)
    {
        $taskIds = explode(',', $taskIds);
        $placeholders = str_repeat ('?, ', count($taskIds)-1) . '?';
        $whereStatement = " WHERE tas.id IN($placeholders)";
        $this->db->query($this->initrequest["tasks"] . $whereStatement);
        return $this->db->execute($taskIds);
    }

    /**
     * @param $projectName
     * @return mixed
     */
    public function getTasksByProjectName($projectName)
    {
        $whereStatement = " WHERE tas.project = :project_name";

        $this->db->query($this->initrequest["tasks"] . $whereStatement);

        $this->db->bind(':project_name', $projectName);

        return $this->db->resultset();
    }

    /**
     * @param $projectId
     * @param $phaseId
     * @return mixed
     */
    public function getTasksByProjectIdAndParentPhase($projectId, $phaseId, $sorting = null)
    {
        $whereStatement = " WHERE tas.project = :project_id AND tas.parent_phase = :parent_phase";
        $this->db->query($this->initrequest["tasks"] . $whereStatement . $this->orderBy($sorting));
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':phase_id', $phaseId);
        return $this->db->resultset();
    }

    /**
     * @param $subtaskId
     * @return mixed
     */
    public function getSubTaskById($subtaskId)
    {
        $whereStatement = " WHERE subtas.id = :sub_task_id";

        $this->db->query($this->initrequest["subtasks"] . $whereStatement);

        $this->db->bind(':sub_task_id', $subtaskId);

        return $this->db->single();
    }


    /**
     * @param $parentTaskId
     * @return mixed
     */
    public function getSubtasksByParentTaskId($parentTaskId)
    {
        $whereStatement = " WHERE task = :parent_task_id";

        $this->db->query($this->initrequest["tasks"] . $whereStatement);

        $this->db->bind(':parent_task_id', $parentTaskId);

        return $this->db->single();
    }

    /**
     * @param $ownerId
     * @param $sorting
     * @return mixed
     */
    public function getOpenAndCompletedSubTasksAssignedToMe($ownerId, $sorting = null)
    {
        $tmpquery = " WHERE subtas.assigned_to = :owner_id AND subtas.status IN(0,2,3) AND tas.status IN(0,2,3)";
        $this->db->query($this->initrequest["subtasks"] . $tmpquery . $this->orderBy($sorting));
        $this->db->bind(':owner_id', $ownerId);
        return $this->db->resultset();
    }

    /**
     * @param $taskIds
     * @return mixed
     */
    public function publishTasks($taskIds)
    {
        if ( strpos($taskIds, ',') ) {
            $taskIds = explode(',', $taskIds);
            $placeholders = str_repeat ('?, ', count($taskIds)-1) . '?';
            $sql = "UPDATE {$this->tableCollab['tasks']} SET published = 0 WHERE id IN ($placeholders)";
            $this->db->query($sql);
            return $this->db->execute($taskIds);
        } else {
            $sql = "UPDATE {$this->tableCollab['tasks']} SET published = 0 WHERE id = :topic_ids";
            $this->db->query($sql);
            $this->db->bind(':topic_ids', $taskIds);
            return $this->db->execute();
        }
    }

    /**
     * @param $taskIds
     * @return mixed
     */
    public function unPublishTasks($taskIds)
    {
        if ( strpos($taskIds, ',') ) {
            $taskIds = explode(',', $taskIds);
            $placeholders = str_repeat ('?, ', count($taskIds)-1) . '?';
            $sql = "UPDATE {$this->tableCollab['tasks']} SET published = 1 WHERE id IN ($placeholders)";
            $this->db->query($sql);
            return $this->db->execute($taskIds);
        } else {
            $sql = "UPDATE {$this->tableCollab['tasks']} SET published = 1 WHERE id = :topic_ids";
            $this->db->query($sql);
            $this->db->bind(':topic_ids', $taskIds);
            return $this->db->execute();
        }
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function addToSiteFile($ids)
    {
        $ids = explode(',', $ids);
        $placeholders = str_repeat ('?, ', count($ids)-1) . '?';
        $placeholders2 = str_repeat ('?, ', count($ids)-1) . '?';
        $sql = "UPDATE files SET published=0 WHERE id IN ($placeholders) OR vc_parent IN ($placeholders2)";

        $this->db->query($sql);

        return $this->db->execute([$placeholders, $placeholders2]);
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function removeToSiteFile($ids)
    {
        $ids = explode(',', $ids);
        $placeholders = str_repeat ('?, ', count($ids)-1) . '?';
        $placeholders2 = str_repeat ('?, ', count($ids)-1) . '?';
        $sql = "UPDATE files SET published=0 WHERE id IN ({$placeholders}) OR vc_parent IN ({$placeholders2})";

        $this->db->query($sql);

        return $this->db->execute([$placeholders, $placeholders2]);
    }

    /**
     * @param $taskIds
     * @return mixed
     */
    public function deleteTasks($taskIds)
    {
        $taskIds = explode(',', $taskIds);
        $placeholders = str_repeat ('?, ', count($taskIds)-1) . '?';
        $sql = "DELETE FROM {$this->tableCollab['tasks']} WHERE id IN ($placeholders)";
        $this->db->query($sql);
        return $this->db->execute($taskIds);
    }

    /**
     * @param $subTaskIds
     * @return mixed
     */
    public function deleteSubTasks($subTaskIds)
    {
        $subTaskIds = explode(',', $subTaskIds);
        $placeholders = str_repeat ('?, ', count($subTaskIds)-1) . '?';
        $sql = "DELETE FROM {$this->tableCollab['subtasks']} WHERE task IN ($placeholders)";
        $this->db->query($sql);
        return $this->db->execute($subTaskIds);
    }

    /**
     * @param string $sorting
     * @return string
     */
    private function orderBy($sorting)
    {
        if (!is_null($sorting)) {
            $allowedOrderedBy = ["tas.id","tas.project","tas.priority","tas.status","tas.owner","tas.assigned_to","tas.name","tas.description","tas.start_date","tas.due_date","tas.estimated_time","tas.actual_time","tas.comments","tas.completion","tas.created","tas.modified","tas.assigned","tas.published","tas.parent_phase","tas.complete_date","tas.invoicing","tas.worked_hours","mem.id","mem.name","mem.login","mem.email_work","mem2.id","mem2.name","mem2.login","mem2.email_work","mem.organization","pro.name","org.id", "subtas.id","subtas.task","subtas.priority","subtas.status","subtas.owner","subtas.assigned_to","subtas.name","subtas.description","subtas.start_date","subtas.due_date","subtas.estimated_time","subtas.actual_time","subtas.comments","subtas.completion","subtas.created","subtas.modified","subtas.assigned","subtas.published","subtas.complete_date"];
            $pieces = explode(' ', $sorting);

            if ($pieces) {
                $key = array_search($pieces[0], $allowedOrderedBy);

                if ($key !== false) {
                    $order = $allowedOrderedBy[$key];
                    return " ORDER BY $order $pieces[1]";
                }
            }
        }

        return '';
    }
}