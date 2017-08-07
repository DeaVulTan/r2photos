<?php
class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database = '') { if (!$this->dbId = @mysql_connect($host, $user, $password, true)) die("<b>MySQL</b>: Unable to connect to database"); @mysql_query('SET NAMES UTF8'); if ($database && !mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function Destroy() { if (!@mysql_close($this->dbId)) die("<b>MySQL</b>: Cann't disconnect from database"); }
}

$json = array();

include(dirname(__FILE__).'/../config.php');

preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
$db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);
$db->Query("SET NAMES UTF8");

$text = htmlspecialchars(trim(preg_replace('{\s+}', ' ', $_GET['text'])));
$search = '';

$tableSph = 'site_search';
$dbSph = new DataBaseMysql('127.0.0.1:9306', '', '');
$countArr = $dbSph->SelectRow("select *, 1 as q from ".$tableSph." WHERE match ('".mysql_real_escape_string($text)."') group by q");
$count = $countArr['@count'];
if ($count > 0) {
    $items = $dbSph->SelectSet("select * from ".$tableSph." WHERE match ('".mysql_real_escape_string($text)."') ORDER BY type_id ASC, @weight DESC, idate DESC LIMIT 10");
    if (sizeOf($items) > 0) foreach ($items as $k => $v) {
        if ($v['type_id'] == 0) {
            $new = $db->SelectRow("SELECT id, name, href FROM main_menu WHERE id='".$v['content_id']."'");
            $json[] = array('id' => $new['id'], 'name' => strip_tags($new['name']), 'href' => $new['href']);
        }
        else if ($v['type_id'] == 1) {
            $new = $db->SelectRow("SELECT id, name FROM news WHERE id='".$v['content_id']."'");
            $json[] = array('id' => $new['id'], 'name' => strip_tags($new['name']), 'href' => 'news_'.$new['id'].'.htm');
        }
    }
}

print (json_encode($json));
?>
