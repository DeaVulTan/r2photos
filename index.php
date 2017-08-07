<?php
include_once("lib/Site.php");
include_once("lib/JS.php");
include_once("lib/Utils.php");

$configArr = array('location' => '', 'offset' => '', 'url' => $_SERVER['REQUEST_URI'], 'urlAdd' => '');
$site = new Site($configArr);

if (defined("LANG")) Utils::initLangs();
Utils::CheckCityByIP();
$site->Go();
$site->Stop();
?>
