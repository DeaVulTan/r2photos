<?php
function def() {
   echo "User-agent: *\nDisallow: /index.htm\nDisallow: /search.htm\nDisallow: /picture-click\nDisallow: /admin\nDisallow: *post_new\nDisallow: *topic_new\nDisallow: /*?refer\nDisallow: *asc\nDisallow: *desc\nDisallow: *pdf\nDisallow: /*utm_source*\nDisallow: /*openstat*";
   die();
}
header('Content-Type: text/plain');
class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) def(); if (!mysql_select_db($database)) def(); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) def(); return $resourseId; }
   function SelectValue($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row =& mysql_fetch_row($resourseId); @mysql_free_result($resourseId); return $row[0]; }
   function Destroy() { if (!@mysql_close($this->dbId)) def(); }
}

$path = dirname(__FILE__).'/';
include($path.'config.php');

preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
$db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);
$text = $db->SelectValue("SELECT value FROM config WHERE name='robots_txt'");
$db->Destroy();
if ($text) { echo $text; die(); }
else def();
?>