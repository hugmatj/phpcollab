<?php
#Application name: PhpCollab
#Status page: 0
#Path by root: ../services/editservice.php

use phpCollab\Services\Services;
use phpCollab\Util;

$checkSession = "true";
include_once '../includes/library.php';

if ($profilSession != "0") {
    phpCollab\Util::headerFunction('../general/permissiondenied.php');
}
$services = new Services();


//case update user
$id = $_GET['id'];

$action = $_GET['action'];

$name = '';
$namePrinted = '';
$hourlyRate = '';

if (!empty($id)) {

//case update user
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($_GET["action"] == "update") {
            $name = Util::convertData($_POST['name']);
            $namePrinted = Util::convertData($_POST['name_printed']);
            $hourlyRate = $_POST["hourly_rate"];
            try {
                $services->updateService($id, $name, $namePrinted, $hourlyRate);
            } catch (Exception $e) {
            }

            phpCollab\Util::headerFunction("../services/listservices.php?msg=update");
        }
    }
    $detailService = $services->getService($id);

    //set values in form
    $name = $detailService["serv_name"];
    $namePrinted = $detailService["serv_name_print"];
    $hourlyRate = $detailService["serv_hourly_rate"];
}

//case add user
if (empty($id) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == "add") {
        //replace quotes by html code in name and address
        $name = phpCollab\Util::convertData($_POST['name']);
        $namePrinted = phpCollab\Util::convertData($_POST['name_printed']);
        $hourlyRate = $_POST["hourly_rate"];

        try {
            $services->addService($name, $namePrinted, $hourlyRate);

            phpCollab\Util::headerFunction("../services/listservices.php?msg=add");
        } catch (Exception $e) {

        }
    }
}

/* Titles */
if ($id == '') {
    $setTitle .= " : Add Service";
} else {
    $setTitle .= " : Edit Service (" . $detailService["serv_name"] . ")";
}

$bodyCommand = "onLoad=\"document.serv_editForm.n.focus();\"";

include APP_ROOT . '/themes/' . THEME . '/header.php';

$blockPage = new phpCollab\Block();
$blockPage->openBreadcrumbs();
$blockPage->itemBreadcrumbs($blockPage->buildLink("../administration/admin.php?", $strings["administration"], "in"));
$blockPage->itemBreadcrumbs($blockPage->buildLink("../services/listservices.php?", $strings["service_management"], "in"));

if ($id == "") {
    $blockPage->itemBreadcrumbs($strings["add_service"]);
}
if ($id != "") {
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../services/viewservice.php?id=$id", $detailService["serv_name"], "in"));
    $blockPage->itemBreadcrumbs($strings["edit_service"]);
}
$blockPage->closeBreadcrumbs();

if ($msg != "") {
    include '../includes/messages.php';
    $blockPage->messageBox($msgLabel);
}

$block1 = new phpCollab\Block();

if ($id == "") {
    $block1->form = "serv_edit";
    $block1->openForm("../services/editservice.php?id=$id&action=add&#" . $block1->form . "Anchor");
}
if ($id != "") {
    $block1->form = "serv_edit";
    $block1->openForm("../services/editservice.php?id=$id&action=update&#" . $block1->form . "Anchor");
}

if (!empty($error)) {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
}

if (empty($id)) {
    $block1->heading($strings["add_service"]);
}
if (!empty($id)) {
    $block1->heading($strings["edit_service"] . " : " . $detailService["serv_name"]);
}

$block1->openContent();

if ($id == "") {
    $block1->contentTitle($strings["details"]);
}
if ($id != "") {
    $block1->contentTitle($strings["details"]);
}

echo <<<TR
<tr class="odd">
    <td class="leftvalue">{$strings["name"]} :</td>
    <td><input size="24" style="width: 250px;" type="text" name="name" value="{$name}"></td>
</tr>
<tr class="odd">
    <td class="leftvalue">{$strings["name_print"]} :</td>
    <td><input size="24" style="width: 250px;" type="text" name="name_printed" value="{$namePrinted}"></td>
</tr>
<tr class="odd">
    <td class="leftvalue">{$strings["hourly_rate"]} :</td>
    <td><input size="24" style="width: 250px;" type="text" name="hourly_rate" value="{$hourlyRate}"></td>
</tr>
<tr class="odd">
    <td class="leftvalue">&nbsp;</td>
    <td><input type="submit" name="Save" value="{$strings["save"]}"></td>
</tr>
TR;

$block1->closeContent();
$block1->closeForm();

include APP_ROOT . '/themes/' . THEME . '/footer.php';
