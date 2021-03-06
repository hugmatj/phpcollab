<?php
#Application name: PhpCollab
#Status page: 0

use phpCollab\Files\Files;

$checkSession = "true";
include '../includes/library.php';

$files = new Files();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST["action"] == "update") {
        $commentField = phpCollab\Util::convertData($_POST["commentField"]);

        try {
            $files->updateApprovalTracking($idSession, $commentField, $id, $_POST["statusField"]);
            $msg = "updateFile";

            phpCollab\Util::headerFunction("doclists.php?msg=$msg");
        }
        catch (Exception $e) {
            echo "Error approving file";
        }
    }
}

$fileDetail = $files->getFileById($_GET["id"]);

if ($fileDetail["fil_published"] == "1" || $fileDetail["fil_project"] != $projectSession) {
    phpCollab\Util::headerFunction("index.php");
}

$bouton[4] = "over";
$titlePage = $strings["approval_tracking"];
include 'include_header.php';

echo <<<FORM
<form method="post" action="../projects_site/docitemapproval.php?action=update" name="documentitemapproval">
    <table style="width: 90%" class="nonStriped">
        <tr>
            <th colspan="2">{$strings["approval_tracking"]} :</th>
        </tr>
        <tr>
            <th>{$strings["document"]} :</th>
            <td><a href="clientfiledetail.php?id={$fileDetail["fil_id"]}">{$fileDetail["fil_name"]}</a></td>
        </tr>
        <tr>
            <th>{$strings["status"]} :</th>
            <td><select name="statusField">
FORM;
$comptSta = count($statusFile);

for ($i = 0; $i < $comptSta; $i++) {
    if ($fileDetail["fil_status"] == $i) {
        echo <<<OPTION
                <option value="$i" selected>{$statusFile[$i]}</option>
OPTION;
    } else {
        echo <<<OPTION
                <option value="$i">{$statusFile[$i]}</option>
OPTION;
    }
}
echo <<<CLOSE_FORM
                </select></td>
        </tr>
        <tr>
            <th>{$strings["comments"]} :</th>
            <td><textarea rows="3" name="commentField" cols="43">{$fileDetail["fil_comments_approval"]}</textarea></td>
        </tr>
        <tr>
            <th>&nbsp;</th>
            <td><input name="submit" type="submit" value="{$strings["save"]}"></td>
        </tr>
</table>
<input name="id" type="hidden" value="{$id}">
<input name="action" type="hidden" value="update">
</form>
CLOSE_FORM;

include("include_footer.php");
