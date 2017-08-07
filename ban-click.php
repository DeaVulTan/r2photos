<?php
class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) die("<b>MySQL</b>: Unable to connect to database"); if (!mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectValue($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row =& mysql_fetch_row($resourseId); @mysql_free_result($resourseId); return $row[0]; }
   function Destroy() { if (!@mysql_close($this->dbId)) die("Cann't disconnect from database"); }
}

$path = dirname(__FILE__).'/';
include($path.'config.php');

preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
if (preg_match('/click\/([1-9][0-9]*)/', $_SERVER['REQUEST_URI'], $m)) {
   $db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);
   $url = $db->SelectValue("SELECT url FROM banners WHERE id=".$m[1]);
   if ($url) {
      $location = (preg_match('/^http:\/\//', $url) ? $url : 'http://'.$_SERVER['HTTP_HOST'].'/'.$url);
      $db->Query("UPDATE banners SET all_clicks=1+all_clicks WHERE id='".$m[1]."'");
      if ($idS = $db->SelectValue("SELECT id FROM banner_stat WHERE sdate='".date('Ymd')."' AND banner_id='".$m[1]."'")) $db->Query("UPDATE banner_stat SET clicks=1+clicks WHERE id='".$idS."'");
      else $db->Query("INSERT INTO banner_stat SET banner_id='".$m[1]."', sdate='".date('Ymd')."', shows='1', clicks='1'");
      $db->Destroy();
      header("Location: ".$location);
      die();
   }
   $db->Destroy();
}
?>