<?php
preg_match('/((.*)?admin\/)(.*)?/', $_SERVER['REQUEST_URI'], $m);
include($_SERVER['DOCUMENT_ROOT'].$m[2].'config.php');
$configSite = $config;
unset($config);
$nameHash = mb_strtolower($configSite['projectName'], 'utf-8').'_admin_hash-'.md5(date("Y").'-'.$configSite['randomHash']);
preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $configSite['database'], $arDatabase);
if(!($db = mysql_connect($arDatabase[3], $arDatabase[1], $arDatabase[2], true))) die("Cant connect");
if(!mysql_select_db($arDatabase[4])) die("Cant select db");
$res = mysql_query("SELECT * FROM admins WHERE MD5(CONCAT(login,':',password))='".$_COOKIE[$nameHash]."'");
$row = mysql_fetch_assoc($res);
mysql_free_result($res);
mysql_close($db);
if (!$row['id']) die("NOT AUTHORIZE");
?>
