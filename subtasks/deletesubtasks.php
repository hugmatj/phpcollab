<?php

use phpCollab\Assignments\Assignments;
use phpCollab\Phases\Phases;
use phpCollab\Projects\Projects;
use phpCollab\Tasks\Tasks;

$checkSession = "true";
include_once '../includes/library.php';

$tasks = new Tasks();
$assignments = new Assignments();
$phases = new Phases();
$projects = new Projects();

$id = $request->query->get("id");
$task = $request->query->get("task");

// Global variables
$strings = $GLOBALS["strings"];
$msgLabel = $GLOBALS["msgLabel"];
$tableCollab = $GLOBALS["tableCollab"];
$targetPhase = $GLOBALS["targetPhase"];

if ($request->query->get("action") == "delete") {
    $id = str_replace("**", ",", $id);

    //find parent task
    $listSubtasks = $tasks->getSubTaskByIdIn($id);

    $tasks->deleteSubTasksById($id);
    $assignments->deleteAssignmentsBySubtasks($id);
    $tasks->setCompletion($task, $tasks->recalculateSubtaskAverages($task));

    if ($task != "") {
        phpCollab\Util::headerFunction("../tasks/viewtask.php?id={$task}&msg=delete");
    } else {
        phpCollab\Util::headerFunction("../general/home.php?msg=delete");
    }
}


$taskDetail = $tasks->getTaskById($task);

$projectDetail = $projects->getProjectById($taskDetail["tas_project"]);

if ($projectDetail["pro_enable_phase"] != "0") {
    $tPhase = $taskDetail["tas_parent_phase"];
    if (!$tPhase) {
        $tPhase = '0';
    }
    $targetPhase = $phases->getPhasesByProjectIdAndPhaseOrderNum($taskDetail["tas_project"], $tPhase);
}

include APP_ROOT . '/themes/' . THEME . '/header.php';

$blockPage = new phpCollab\Block();
$blockPage->openBreadcrumbs();
if ($task != "") {
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../projects/listprojects.php?", $strings["projects"], "in"));
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../projects/viewproject.php?id=" . $projectDetail["pro_id"], $projectDetail["pro_name"], "in"));

    if ($projectDetail["pro_phase_set"] != "0") {
        $blockPage->itemBreadcrumbs($blockPage->buildLink("../phases/listphases.php?id=" . $projectDetail["pro_id"], $strings["phases"], "in"));
        $blockPage->itemBreadcrumbs($blockPage->buildLink("../phases/viewphase.php?id=" . $targetPhase["pha_id"], $targetPhase["pha_name"], "in"));
    }

    $blockPage->itemBreadcrumbs($blockPage->buildLink("../tasks/listtasks.php?project=" . $projectDetail["pro_id"], $strings["tasks"], "in"));
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../tasks/viewtask.php?id=" . $taskDetail["tas_id"], $taskDetail["tas_name"], "in"));
    $blockPage->itemBreadcrumbs($strings["delete_subtasks"]);
} else {
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../general/home.php?", $strings["home"], "in"));
    $blockPage->itemBreadcrumbs($strings["my_tasks"]);
}
$blockPage->closeBreadcrumbs();

if ($msg != "") {
    include '../includes/messages.php';
    $blockPage->messageBox($msgLabel);
}

$block1 = new phpCollab\Block();

$block1->form = "saP";
$block1->openForm("../subtasks/deletesubtasks.php?task=$task&action=delete&id=$id");

$block1->heading($strings["delete_subtasks"]);

$block1->openContent();
$block1->contentTitle($strings["delete_following"]);

$id = str_replace("**", ",", $id);
$listSubtasks = $tasks->getSubTaskByIdIn($id);

foreach ($listSubtasks as $subtask) {
    echo <<< HTML
        <tr class="odd">
            <td class="leftvalue">#{$subtask["subtas_id"]}</td><td>{$subtask["subtas_name"]}</td>
        </tr>
HTML;
}

echo <<< HTML
<tr class="odd">
    <td class="leftvalue">&nbsp;</td>
    <td>
        <input type="submit" name="delete" value="{$strings["delete"]}"> 
        <input type="button" name="cancel" value="{$strings["cancel"]}" onClick="history.back();">
    </td>
</tr>
HTML;


$block1->closeContent();
$block1->closeForm();

include APP_ROOT . '/themes/' . THEME . '/footer.php';
