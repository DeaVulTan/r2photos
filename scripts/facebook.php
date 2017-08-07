<?php
$pageId = false;
$pageName = 'PAGE NAME'; //Имя стены
$search = array ("/(\r|\n|\s)+/", // Вырезает пробельные символы
                 "/\&(quot|#34);/i", // Заменяет HTML-сущности
                 "/\&(amp|#38);/i",
                 "/\&(lt|#60);/i",
                 "/\&(gt|#62);/i",
                 "/\&(nbsp|#160);/i",
                 "/\&(iexcl|#161);/i",
                 "/\&(cent|#162);/i",
                 "/\&(pound|#163);/i",
                 "/\&(copy|#169);/i");

$replace = array (" ",
                  "\"",
                  "&",
                  "<",
                  ">",
                  " ",
                  chr(161),
                  chr(162),
                  chr(163),
                  chr(169));

class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) die("<b>MySQL</b>: Unable to connect to database"); if (!mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function Destroy() { if (!@mysql_close($this->dbId)) die("Cann't disconnect from database"); }
}

include_once('../lib/facebook-api/facebook.php');

//start the session if needed
if( session_id() ) {
}
else {
   session_start();
}

$appID = '';
$appSecret = '';
// создадим объект для обращения к Facebook API
$fb = new Facebook(array(
   'appId' => $appID,
   'AppSecret' => $appSecret,
   'cookie' => true
));
$fb->setApiSecret($appSecret);

// получим FB UID пользователя, который авторизирован
$user = $fb->getUser();

// если получен UID пользователя
if ($user) {
    // получаем access token для пользователя
    $access_token = $fb->getAccessToken();
     
    // проверим список разрешений
    $permissions_list = $fb->api(
       '/me/permissions',
       'GET',
       array(
          'access_token' => $access_token
       )
    );
    
    // проверим установлены ли нужные нам разрешения, если нет, то опять перенаправим на необходимую страницу 
    $permissions_needed = array('publish_stream', 'read_stream', 'manage_pages');
    foreach ($permissions_needed as $perm) {
       if (!isset($permissions_list['data'][0][$perm]) || $permissions_list['data'][0][$perm] != 1) {
          $params = array(
             'scope' => 'publish_stream,read_stream,manage_pages',
             'fbconnect' =>  1,
             'display'   =>  "page",
             'next' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
          );
          $url = $fb->getLoginUrl($params);
          header("Location: {$url}");
          exit();
       }
    }
    
    // если пользователь дал нам все нужные разрешения получим инфу о страницах, которыми он управляет
    $accounts = $fb->api(
       '/me/accounts',
       'GET',
       array(
          'access_token' => $access_token
       )
    );
    
    //Находим нужную нам "стену" по имени $pageName
    foreach ($accounts["data"] as $page) {
        if ($page["name"] == $pageName) {
            $pageId = $page["id"];
            $page_access_token = $page["access_token"];
            break;
        }
    }
    
    //Если мы нашли стену, то пытаемся добавить на стену новое сообщение
    if (!empty($page_access_token)) {
        
        //Конект к БД чтобы достать нужную новость
        $absP = dirname(__FILE__).'/../';
        include($absP.'config.php');
        preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
        $db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);
        $row = $db->SelectRow("SELECT * FROM news WHERE id='".trim(strip_tags($_GET['id']))."'");
        
        //Формируем массив для api, для отправки на стену
        $message = mb_convert_encoding(preg_replace($search, $replace, strip_tags($row['announce'])), 'utf8', 'cp1251');
        $description = mb_convert_encoding(preg_replace($search, $replace, strip_tags($row['text'])), 'utf8', 'cp1251');
        $args = array(
            'access_token'  => $page_access_token,
            'message'       => '',
            'picture'       => 'http://'.$_SERVER['HTTP_HOST'].'/image/logo.gif',
            'link'          => 'http://'.$_SERVER['HTTP_HOST'].'/news_'.$row['id'].'.htm',
            'name'          => $message,
            'caption'       => '',
            'description'   => $description
        );
        $post_id = $fb->api("/".$pageId."/feed", "post", $args);
        
        // успешно...
        //Ставим флаг что новость добавлена на facebook
        $db->Query("UPDATE news SET is_facebook='1' WHERE id='".$row['id']."'");
        $db->Destroy();
        //Уходим обратно в админку
        header("Location: http://".$_SERVER['HTTP_HOST']."/admin/news-list.htm");
        die;
    }
    else {
        // если страница не найдена
        echo 'Стена с именем "'.$pageName.'" не найдена. Обратитесь к разработчикам скрипта.';
        die;
    }
}
else {
    // если нет, то перенаправим на страницу, где можно дать нужные разрешения
    // сгенерируем нужный адрес с помощью метода getLoginUrl()
    if ($_GET['second'] == 'yes') {
        echo 'Loop';
        die;
    }
    
    $params = array(
        'scope' => 'publish_stream,read_stream,manage_pages',
        'fbconnect' =>  1,
        'redirect_uri' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?second=yes'
    );
    $url = $fb->getLoginUrl($params);

    // перенаправим на нужную страницу
    header("Location: {$url}");
    exit();
}
?>