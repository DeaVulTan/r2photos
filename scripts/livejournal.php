<?php
$absP = dirname(__FILE__).'/../';

include($absP."lib/xmlrpc.inc");

setlocale(LC_ALL, 'ru_RU.UTF-8');
$xmlrpc_internalencoding = 'UTF-8'; 

$login = 'LOGIN';
$password = 'PASSWORD';

class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) die("<b>MySQL</b>: Unable to connect to database"); if (!mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function Destroy() { if (!@mysql_close($this->dbId)) die("Cann't disconnect from database"); }
}

include($absP.'config.php');
preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
$db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);
$row = $db->SelectRow("SELECT id, idate, name, description FROM news WHERE id='".trim(strip_tags($_GET['id']))."'");
$db->Query("SET NAMES UTF8;");

if ($row['id'] > 0) {
    $post = array(
        "username" => new xmlrpcval($login, "string"),
        "password" => new xmlrpcval($password, "string"),
        "event" => new xmlrpcval(mb_convert_encoding(preg_replace(array('/(\n|\r)/', '/\s+/'), array(' ', ' '), $row['description']), 'utf8', 'cp1251'), "string"),
        "subject" => new xmlrpcval(mb_convert_encoding($row['name'], 'utf8', 'cp1251'), "string"),
        "lineendings" => new xmlrpcval("unix", "string"),
        "year" => new xmlrpcval(date("Y", $row['news_idate']), "int"),
        "mon" => new xmlrpcval(date("n", $row['news_idate']), "int"),
        "day" => new xmlrpcval(date("j", $row['news_idate']), "int"),
        "hour" => new xmlrpcval(date("G", $row['news_idate']), "int"),
        "min" => new xmlrpcval(date("i", $row['news_idate']), "int"),
        "ver" => new xmlrpcval(2, "int")
    );
    $post2 = array(
        new xmlrpcval($post, "struct")
    );
     
    // создаем XML сообщение для сервера
    $f = new xmlrpcmsg('LJ.XMLRPC.postevent', $post2);

    // описываем сервер
    $c = new xmlrpc_client("/interface/xmlrpc", "www.livejournal.com", 80);
    $c->request_charset_encoding = "UTF-8";
     
    // отправляем XML сообщение на сервер
    $r = $c->send($f);

    $db->Query("UPDATE news SET is_livejournal='1' WHERE id='".(int)$row['id']."'");
    
    //Уходим обратно в админку
    header("Location: http://".$_SERVER['HTTP_HOST']."/admin/news-list.htm");
    die;
}
$db->Destroy();
unset($db);
?>