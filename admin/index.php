<?php
define("IS_ADMIN", 1);
include_once("../lib/Site.php");
include_once("../lib/JS.php");
include_once("../lib/Utils.php");

JS::load('admin');
JS::enable('admin/common');

$configArr = array('location' => '', 'offset' => 'admin/', 'url' => $_SERVER['REQUEST_URI'], 'urlAdd' => '');
$site = new Site($configArr);
if (defined("LANG")) Utils::initLangs();
include_once("../lib/Admin.php");
$site->Go();
$site->Stop();
?>
