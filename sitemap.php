<?php
header('Content-Type: text/xml');

class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) die("<b>MySQL</b>: Unable to connect to database"); if (!mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectValue($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row =& mysql_fetch_row($resourseId); @mysql_free_result($resourseId); return $row[0]; }
   function &SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row =& mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function &SelectSet($sqlString, $idTable = '') { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); while ($rowOne =& mysql_fetch_assoc($resourseId)) { if ($idTable) $row[$rowOne[$idTable]] = $rowOne; else $row[] = $rowOne; } @mysql_free_result($resourseId); return $row; }
   function SelectLastInsertId() { return @mysql_insert_id($this->dbId); }
   function SelectAffectedRows() { return @mysql_affected_rows($this->dbId); }
   function Destroy() { if (!@mysql_close($this->dbId)) die("Cann't disconnect from database"); }
}

$path = dirname(__FILE__).'/';
include($path.'config.php');
$site = ucfirst(strtolower($config['projectName']));

preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
$db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);

echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
$items = $db->SelectSet("SELECT id, href FROM main_menu ORDER BY ord", 'id');
if (sizeOf($items) > 0) foreach ($items as $id => $item) echoUrl($item['href']);

$items = $db->SelectSet("SELECT id FROM catalog WHERE is_active='1' ORDER BY ord", 'id');
if (sizeOf($items) > 0) foreach ($items as $id => $item) {
   $url = 'catalog_'.$id.'.htm';
   echoUrl($url);
   $items2 = $db->SelectSet("SELECT id FROM items WHERE is_active='1' AND catalog_id='".$id."'", 'id');
   if (sizeOf($items2) > 0) foreach ($items2 as $idI => $itemI) {
      $url = 'item_'.$idI.'.htm';
      echoUrl($url);
   }
}

echo '</urlset>';
$db->Destroy();
unset($db);

function echoUrl($url) {
global $site;
echo '<url>
 <loc>http://www.'.$site.'.ru/'.$url.'</loc>
 <lastmod>'.date("Y-m-d").'</lastmod>
 <changefreq>daily</changefreq>
 <priority>'.($url ? (preg_match('/_/', $url) ? 0.6 : 0.8) : 1).'</priority>
</url>
';
}
?>