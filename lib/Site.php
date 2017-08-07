<?php
class Site {
   var $enginesObj = array();

   function Site($parms = array()) {
      if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');
      if (!isset($_COOKIE[md5('areYouRobot'.$_SERVER['HTTP_HOST'])])) setcookie(md5('areYouRobot'.$_SERVER['HTTP_HOST']), 1);
      define("IS_LOCAL", (getenv('HIVE_ENV') == 'DEVEL' ? true : false));

//      define("IS_OFFICE", (IS_LOCAL === true || in_array(getenv("REMOTE_ADDR"), array('87.244.36.213')) ? true : false));
define("IS_OFFICE", false);

      Site::SetErrorHandler();
      $locationPath = ($parms['location'] ? $parms['location'] : '/'); $offsetPath = ($parms['offset'] ? $parms['offset'] : ''); $absolutePath = (preg_replace('/\/$/', '', $_SERVER['DOCUMENT_ROOT']).'/'.preg_replace('/^\//', '', $_SERVER['SCRIPT_NAME']) == $_SERVER['SCRIPT_FILENAME'] ? preg_replace('/\/$/', '', $_SERVER['DOCUMENT_ROOT']).$locationPath : ($offsetPath ? preg_replace('/[^\/]+/', '..', $offsetPath) : './')); $configFile = $absolutePath.'config.php'; if (file_exists($configFile)) { include($configFile); define('PROJECT_NAME', ($config['projectName'] ? $config['projectName'] : 'PROJECT_SITE')); $GLOBALS[PROJECT_NAME] = array(); if (sizeof($config) > 0) foreach ($config as $key => $value) Site::SetParms($key, trim($value)); } else trigger_error("Config file <b>config.php</b> not found!", FATAL);
      Site::SetParms('locationPath', $locationPath); Site::SetParms('offsetPath', $offsetPath); Site::SetParms('siteRef', 'http://'.$_SERVER['HTTP_HOST'].trim($parms['urlAdd']).$locationPath); Site::SetParms('absolutePath', $absolutePath); Site::SetParms('absoluteOffsetPath', $absolutePath.$offsetPath); Site::SetParms('libPath', Site::GetParms('absolutePath').'lib/'); Site::SetParms('requestUriFull', $parms['url']); if (!Site::GetParms('urlDelimeter')) Site::SetParms('urlDelimeter', '_'); if (trim(Site::GetParms('lang'))) Site::SetLang(); else define('URL_ADD', ''); Site::_SetLocale();
      Site::SetParms( 'isAjax', ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) );
      $requestUri = preg_replace(array('{^'.Site::GetParms('locationPath').Site::GetParms('offsetPath').'}', '{\?.*$}'), array('', ''), Site::GetParms('requestUriFull')); if (defined("LANG")) $requestUri = preg_replace('/'.LANG.'\//', '', $requestUri); Site::SetParms('requestUri', $requestUri);
      $dbAccess = Site::GetParms('database'); if ($dbAccess <> '' && $dbAccess <> false) { preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $dbAccess, $arDatabase); $dbObject = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]); Site::SetParms('db', $dbObject); Site::SetParms('tablesPath', Site::GetParms('absolutePath').'lib/db_tables/'); }
      $isSession = Site::GetParms('session'); if ($isSession <> '' && $isSession <> false) { session_start(); Site::SetParms('sessionId', session_id()); }
      if (defined("LANG")) $parms['url'] = preg_replace('/'.LANG.'\//', '', $parms['url']);
      $uriArr = array(); $uriArr = parse_url(trim($parms['url']));
      $pathArr = array(); $pathArr = pathinfo($uriArr['path']);
      $dirName = trim($pathArr['dirname']);
      $baseName = trim($pathArr['basename']);
      if (defined('IS_ADMIN') && IS_ADMIN == 1) $scriptName = ($baseName && preg_match("/htm(l)?$/", $baseName) ? $baseName : 'index.htm');
      else $scriptName = $dirName.($dirName <> '/' ? '/' : '').($baseName ? $baseName : 'index');
      Site::SetParms('scriptName', $scriptName);
      if (defined('IS_ADMIN') && IS_ADMIN == 1) { $tempAr = array(); preg_match('/^([^'.Site::GetParms('urlDelimeter').'\.\?\&]+)/', $scriptName, $tempAr); $pageId = ($tempAr[1] ? $tempAr[1] : 'index'); }
      else $pageId = ($baseName ? $baseName : 'index');
      Site::SetParms('pageId', $pageId);
      Site::Headers();
   }

   function Go() {
      $_rulesF = Site::GetParms('absoluteOffsetPath').'rules.php'; if (file_exists($_rulesF)) include_once($_rulesF); else trigger_error("Rules file <b>rules.php</b> not found!", FATAL);
      $enginesThisSite = array();
      foreach ($rules as $regexp => $engines) {
         if (preg_match('/^'.$regexp.'$/', Site::GetParms('requestUri'), $uriParms)) { if (sizeOf($engines) > 0) { foreach ($engines as $nE => $parms) if (sizeOf($parms) > 0) foreach ($parms as $k => $v) $engines[$nE][$k] = preg_replace('/^\{([1-9][0-9]*)\}$/e', '(isset($uriParms[\\1]) && $uriParms[\\1] ? $uriParms[\\1] : 0)', $v); $enginesThisSite = $engines; } break; }
         else if (preg_match('/^ADMIN/', $regexp) && defined('IS_ADMIN') && IS_ADMIN == 1) { preg_match('/^([a-z]+)\-(.+)$/', Site::GetParms('requestUri'), $m); if (preg_match('/^'.preg_replace('/ADMIN/', $m[1], $regexp).'$/', Site::GetParms('requestUri'), $uriParms))	{ if (sizeOf($engines) > 0) foreach ($engines as $nE => $parms) { if ($nE == 'ADMIN') $nE = ucfirst($m[1]);
            if (sizeOf($parms) > 0) foreach ($parms as $k => $v) $enginesThisSite[$nE][$k] = preg_replace('/^\{([1-9][0-9]*)\}$/e', '(isset($uriParms[\\1]) && $uriParms[\\1] ? $uriParms[\\1] : 0)', $v);
            else $enginesThisSite[$nE] = array(); } break; } }
      }
      if (sizeOf($enginesThisSite) == 0) trigger_error("Engines not found!", FATAL); foreach ($enginesThisSite as $nameEngine => $parmsEngine) { $nameClassEngine = $nameEngine.'Engine'; $this->enginesObj[$nameClassEngine] = Site::CreateEngine($nameEngine, $parmsEngine); }
      if (sizeOf($this->enginesObj) > 0) { if (is_object($this->enginesObj['LayoutEngine'])) ob_start(); foreach ($this->enginesObj as $nameEngine => $objEngine) { $ret = $objEngine->run(); if (!isset($ret) || ($ret == false)) Site::Send404(); else if (is_string($ret)) Site::Redirect($ret); } } else trigger_error("Engines classes not found!", FATAL);
      if (!defined("NOT_PAGES") && !defined("IS_ADMIN")) { $sitepages = Site::CreateEngine('Sitepages'); $sitepages->run(); unset($sitepages); }
   }

   static function SetParms($name, $value) { if (!empty($name)) $GLOBALS[PROJECT_NAME][$name] = $value; }
   static function GetParms($name) { return (isset($GLOBALS[PROJECT_NAME][$name]) ? $GLOBALS[PROJECT_NAME][$name] : ''); }
   static function SetSession($name, $value) { $_SESSION[$name] = $value; }
   static function GetSession($name) { return (isset($_SESSION[$name]) ? $_SESSION[$name] : ''); }
   static function GetDB() { return (is_object(Site::GetParms('db')) ? Site::GetParms('db') : trigger_error("Database is inaccessible on this site. Check your config.ini file (variable: database)!", FATAL)); }
   static function CreateUrl($base, $parms = array()) {
    $deliver = (defined("IS_ADMIN") && IS_ADMIN ? '_' : '/');
    $suffix = (defined("IS_ADMIN") && IS_ADMIN ? ".htm" : '');
    if (sizeOf($parms) == 0) return (defined("LANG") ? URL_ADD : '').$base.$suffix;
    else {
        $temp = array();
        foreach ($parms as $k => $v) if (is_array($v) && sizeOf($v) > 0) foreach ($v as $k1 => $v1) $temp[] = trim($v1); else if (isset($v)) $temp[] = trim($v);
        return (defined("LANG") ? URL_ADD : '').$base.$deliver.implode($deliver, $temp).$suffix;
    }
   }
   static function GetTemplate($path, $fileName) { $file = Site::GetParms('absoluteOffsetPath').'templates/'.$path."/".$fileName.".phtml"; if (file_exists($file)) return $file; else trigger_error("File <b>".$path."/".$fileName.".phtml</b> not found!", ERROR); }
   function GetBaseRef() { return Site::GetParms('siteRef').Site::GetParms('offsetPath'); }
   function Headers() { header("Last-Modified: ".gmdate("D, d M Y H:i:s", (time() - 7200))." GMT"); }
   function Redirect($uri) { if (preg_match("/^(\/(.*))?(http(s)?:\/\/.+)/", $uri, $ar)) header("Location: ".$ar[3]); else {
        if (defined("IS_ADMIN") && IS_ADMIN) $redirect = Site::GetBaseRef().$uri;
        else $redirect = Site::GetBaseRef().preg_replace('/^\//', '', $uri);
        header("Location: ".$redirect);
    }
    die();
   }
   function Send404() { header("HTTP/1.0 404 Not Found", true); $file404 = Site::GetParms('absoluteOffsetPath').'pages/404.htm'; $fileLayout = Site::GetParms('absoluteOffsetPath').'templates/layout/work.phtml'; if (file_exists($file404)) { ob_start(); include($file404); $content = ob_get_contents(); ob_end_clean(); if (file_exists($fileLayout)) { include($fileLayout); die(); } trigger_error($content, ERROR); } else trigger_error("Page not found!", ERROR); }
   function SetLang() { Site::SetParms('LANGS', array('ru' => 'Русский', 'en' => 'Английский', 'fr' => 'Французский', 'ge' => 'Немецкий', 'it' => 'Итальянский', 'es' => 'Испанский')); $langs = explode(",", trim(Site::GetParms('lang'))); $lang = (preg_match('{('.implode("|", $langs).')/(.*)}', Site::GetParms('requestUriFull'), $m) ? $m[1] : 'ru'); define('LANG', $lang); define('URL_ADD', (LANG <> 'ru' ? LANG.'/' : '')); }
   function GetLangs() { $langs = Site::GetParms('LANGS'); $piece = explode(",", trim(Site::GetParms('lang'))); foreach ($piece as $k => $v) $ret[trim($v)] = $langs[trim($v)]; return $ret; }
   function GetLang($lang) { $langs = Site::GetParms('LANGS'); return $langs[$lang]; }
   function _SetLocale() {
     $lang = (defined("LANG") ? (LANG == 'ru' ? 'ru_RU' : 'en_US') : 'ru_RU');
     foreach (array('UTF-8', 'utf8') as $enc)
       if (setlocale(LC_ALL, "$lang.$enc") == "$lang.$enc") break;
   }
   function GetPageNavigator($page, $numPages, $url) {
    $defaultRegexp = '/\/page\/\%/';
    $isPageSl = preg_match($defaultRegexp, $url);
    $ret = '';
    if ($page > $numPages) $page = $numPages;
    $startPage = $page - 4;
    if ($startPage < 2) $startPage = 2;
    $endPage = $page + 4;
    if ($endPage > ($numPages - 1)) $endPage = ($numPages - 1);
    //Предыдущая страница
    if ($page > 2) $ret .= '<li><a href="'.($isPageSl ? preg_replace($defaultRegexp, '', $url) : preg_replace('/\%/', 1, $url)).'">&lt;&lt;</a></li>';
    if ($page <> 1) $ret .= '<li><a href="'.($isPageSl && (($page - 1) == 1) ? preg_replace($defaultRegexp, '', $url) : preg_replace('/\%/', ($page - 1), $url)).'" title="Предыдущая страница">&lt;</a></li>';
    //-----//
    //Первая страница
    if ($page == 1) $ret .= '<li>1</li>';
    else $ret .= '<li><a href="'.($isPageSl ? preg_replace($defaultRegexp, '', $url) : preg_replace('/\%/', 1, $url)).'">1</a></li>';
    //-----//
    //Разрыв от 1-й страницы до цикла
    if ($startPage <> 2) $ret .= '<li>...</li>';
    //-----//
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i > $numPages) break;
        if ($i == $page) $ret .= '<li>'.$page.'</li>';
        else $ret .= '<li><a href="'.($isPageSl && ($i == 1) ? preg_replace($defaultRegexp, '', $url) : preg_replace('/\%/', $i, $url)).'">'.$i.'</a></li>';
    }
    //Разрыв от цикла до последней страницы
    if ($endPage <> ($numPages - 1)) $ret .= '<li>...</li>';
    //-----//
    //Последняя страница
    if ($page == $numPages) $ret .= '<li>'.$numPages.'</li>';
    else $ret .= '<li><a href="'.preg_replace('/\%/', $numPages, $url).'">'.$numPages.'</a></li>';
    //-----//
    //Следующая страница
    if ($page <> $numPages) $ret .= '<li><a href="'.preg_replace('/\%/', ($page + 1), $url).'" title="Следующая страница">&gt;</a></li>';
    if ($page < ($numPages - 1)) $ret .= '<li><a href="'.preg_replace('/\%/', $numPages, $url).'">&gt;&gt;</a></li>';
    //-----//
    return '<ul class="app-page-nav">'.(defined("IS_ADMIN") && IS_ADMIN ? '<li>'.(defined('LANG') && LANG ? Utils::GetLang('pages') : 'Страницы: ').'</li>' : '').$ret.'</ul>';
   }
   function CreateEngine($name, $parms = array()) { $nameClassEngine = ucfirst($name).'Engine'; if (!class_exists($nameClassEngine)) { if (file_exists(Site::GetParms('absoluteOffsetPath').'lib/engines/'.$nameClassEngine.".php")) include_once(Site::GetParms('absoluteOffsetPath').'lib/engines/'.$nameClassEngine.".php"); else trigger_error("Engine ".$nameClassEngine." (in file ".$nameClassEngine.".php) not found!", FATAL); } if (defined("IS_ADMIN") && IS_ADMIN == 1) { $tableObj = Site::GetTable($name); if (is_object($tableObj)) $parms['tableObject'] = $tableObj; } else { $nameClass = ucfirst($name); if (file_exists(Site::GetParms('tablesPath').$nameClass.".php")) include_once(Site::GetParms('tablesPath').$nameClass.".php"); } $newObject = new $nameClassEngine(mb_strtolower($name, 'utf-8'), $parms); $classMethods = get_class_methods($nameClassEngine); if (sizeOf($classMethods) > 0) { if (!in_array('run', $classMethods)) trigger_error("Bad engine ".$nameClassEngine.": function 'run' is not declarated!", FATAL); } else trigger_error("Bad engine ".$nameClassEngine.": function 'run' is not declarated!", FATAL); return $newObject; }
   function GetEngine($name) { $nameClassEngine = ucfirst($name).'Engine'; if (isset($this) && is_object($this) && mb_strtolower(get_class($this), 'utf-8') == 'site' && is_object($this->enginesObj[$nameClassEngine])) return $this->enginesObj[$nameClassEngine]; else return Site::CreateEngine($name); }
   function GetTable($name) { $nameClass = ucfirst($name); if (file_exists(Site::GetParms('tablesPath').$nameClass.".php")) { include_once(Site::GetParms('tablesPath').$nameClass.".php"); return new $nameClass(); } else return false; }
   function SetErrorHandler () { define("FATAL", E_USER_ERROR); define("ERROR", E_USER_WARNING); error_reporting(FATAL | ERROR); $old_error_engine = set_error_handler(array($this, 'Error')); }
   function Error($errno, $errstr, $errfile = __FILE__, $errline = __LINE__) { $errorFile = Site::GetParms('absoluteOffsetPath').'templates/error/error.phtml'; if (file_exists($errorFile) && ($errno == FATAL || $errno == ERROR)) { $color = ($errno == FATAL ? '#ffccff' : '#ccffff'); $colorBorder = ($errno == FATAL ? '#ff00ff' : '#00ffff'); $title = ($errno == FATAL ? 'Critical error' : 'Error'); include($errorFile); exit(1); } else error_reporting(7); }
   function Stop() { unset($this->enginesObj); if (Site::GetParms('database')) { $dbObject = Site::GetParms('db'); $dbObject->Destroy(); } if (Site::GetParms('sessionId')) session_write_close(); unset($GLOBALS[PROJECT_NAME]); }
   function sanitize(&$source, $inputs = null) {
      static $_inputs;
      $filtered = 0;
      if ($inputs !== null) $_inputs = $inputs;
      foreach (array_keys($source) as $k) {
        if (isset($_inputs[$k])) {
            $source[$k] = is_array($_inputs[$k]) ?
              filter_var($source[$k], $_inputs[$k][0], $_inputs[$k][1]) :
              filter_var($source[$k], $_inputs[$k]);
            $filtered++;
        }
      }
      return $filtered;
   }
}

class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) trigger_error("<b>MySQL</b>: Unable to connect to database", ERROR); @mysql_query('SET NAMES UTF8'); if (!mysql_select_db($database)) trigger_error("<b>MySQL</b>: Unable to select database <b>".$database."</b>", ERROR); }
   function Query($sqlString) { /*echo $sqlString."<br />";*/ if (!$resourseId =@mysql_query($sqlString, $this->dbId)) trigger_error("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error(), ERROR); return $resourseId; }
   function SelectValue($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_row($resourseId); @mysql_free_result($resourseId); return $row[0]; }
   function SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function SelectSet($sqlString, $idTable = '') { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); while ($rowOne = mysql_fetch_assoc($resourseId)) { if ($idTable) $row[$rowOne[$idTable]] = $rowOne; else $row[] = $rowOne; } @mysql_free_result($resourseId); return $row; }
   function SelectLastInsertId() { return @mysql_insert_id($this->dbId); }
   function SelectAffectedRows() { return @mysql_affected_rows($this->dbId); }
   function Destroy() { if (!@mysql_close($this->dbId)) trigger_error("<b>MySQL</b>: Cann't disconnect from database", ERROR); }
}

class Table {
   var $name; var $tableName;
   var $key; var $data = array();
   var $fields = array(); var $columns = array(); var $autoUpdate = array();
   function Table($array, $id = '') {
      if (!empty($array['table'])) $this->name = $array['table'];
      else trigger_error("Table name is not defined!", ERROR);
      $this->tableName = $array['tableName'];
      $this->GetRealFields();
      $ind = 0;
      if (sizeOf($this->data) > 0) foreach ($this->data as $k => $v) {
         if ($ind == 0) $this->key = $k;
         break;
      }
      else trigger_error("Fields of table '<b>".$this->name."</b>' are not defined!", ERROR);
      if ($array['form']) $this->TableForm($array['form']);
      if ($array['columns']) $this->TableColumns($array['columns']);
      if ($id > 0) $this->SetObjectData($id);
   }
   function TableForm($array) {
      if (is_array($array) && sizeOf($array) > 0) foreach ($array as $nameField => $parmsField) {
         if (array_key_exists($nameField, $this->data)) $this->data[$nameField] = $parmsField['default'];
         list($keyF, $parmsF) = each($parmsField['form']);
         $this->fields[$nameField]['name'] = $keyF;
         $this->fields[$nameField]['parms'] = $parmsF;
      }
   }
   function TableColumns($array) {
      if (is_array($array) && sizeOf($array) > 0) foreach ($array as $nameField => $parmsField) {
         $this->columns[$nameField] = $parmsField;
         if (isset($parmsField['autoUpdate']) && $parmsField['autoUpdate']) $this->autoUpdate[] = $nameField;
      }
   }
   function GetRealFields() {
      if ($this->name) {
         $db = Site::GetDB();
         $listFields = $db->SelectSet("SHOW FIELDS FROM ".$this->name);
         if (sizeOf($listFields) > 0) foreach ($listFields as $k => $v) $this->data[$v['Field']] = '';
      }
      else trigger_error("Table ".$this->name." don't have fields", ERROR);
   }
   function SetData($row) { foreach ($row as $key => $value) if (array_key_exists($key, $this->data)) $this->data[$key] = $value; return true; }
   function GetData() { return $this->data; }
   function SetValue($name, $value) { if (array_key_exists($name, $this->data)) $this->data[$name] = addslashes(stripslashes($value)); else trigger_error("Field '<b>".$name."</b>' not exists in table '<b>".$this->name."</b>'", ERROR); return true; }
   function GetValue($name) { if (array_key_exists($name, $this->data)) return $this->data[$name]; else trigger_error("Field '<b>".$name."</b>' not exists in table '<b>".$this->name."</b>'", ERROR); }
   function SetObjectData($id) { $db = Site::GetDB(); $isTrue = $db->SelectRow("SELECT * FROM ".$this->name." WHERE ".$this->key."='".$id."'"); if ($isTrue[$this->key] > 0) $this->SetData($isTrue); else trigger_error("Record with <b>".$this->key."='".$id."'</b> not exists in table '<b>".$this->name."</b>'", ERROR); }

   function GetForm() { if (sizeOf($this->fields) > 0) return $this->fields; else trigger_error("Fields of table '<b>".$this->name."</b>' are not defined!", ERROR); }
   function GetFormObject() { include_once(Site::GetParms('libPath').'Form.php'); $fields = $this->GetForm(); $isEnctype = false; $fieldsArr = array(); foreach ($fields as $nameField => $parmsField) { $fieldsArr[] = new $parmsField['name']($nameField, $parmsField['parms']); if ($parmsField['name'] == 'Upload' || $parmsField['name'] == 'FCKeditor') $isEnctype = true; } $ret = new Form($fieldsArr, array('name' => $this->name.'Form')); if ($isEnctype) $ret->setParm('enctype', 'multipart/form-data'); return $ret; }

   function UpdateRows($rows) { $db = Site::GetDB(); if (is_array($rows)) { foreach ($rows as $id => $row) { if (sizeOf($row) > 0) { $setStr = array(); foreach ($row as $name => $value) { if ($name == $this->key) continue; $setStr[] = "`".$name."`='".addslashes(stripslashes($value))."'"; } $db->Query("UPDATE ".$this->name." SET ".implode(", ", $setStr)." WHERE ".$this->key."=".$id); } else trigger_error("Update rows in table '<b>".$this->name."</b>': Fields and values not defined for id='<b>".$id."</b>'", ERROR); } } else trigger_error("Update table '<b>".$this->name."</b>': Fields and values not defined for id='<b>".$id."</b>'", ERROR); return true; }
   function SaveRows($rows) { $res = 0; $db = Site::GetDB(); if (is_array($rows)) { foreach ($rows as $row) { $fieldStr = array(); $valStr = array(); if (sizeOf($row) > 0) foreach ($row as $name => $value) { $fieldStr[] = $name; $valStr[] = "'".addslashes(stripslashes($value))."'"; } foreach ($this->data as $fieldName => $fieldValue) { if ($fieldName == $this->key) continue; if (!in_array($fieldName, $fieldStr)) { $fieldStr[] = $fieldName; $valStr[] = "'".addslashes(stripslashes($fieldValue))."'"; } } if (sizeOf($fieldStr) > 0) foreach ($fieldStr as $k => $v) $fieldStr[$k] = "`".$v."`"; $db->Query("INSERT INTO ".$this->name." (".implode(", ", $fieldStr).") VALUES (".implode(", ", $valStr).")"); $res = $db->SelectLastInsertId(); } } else trigger_error("Save rows in table '<b>".$this->name."</b>': Fields and values not defined", ERROR); return $res; }
   function DeleteRows($rows) { $db = Site::GetDB(); if (is_array($rows)) { foreach ($rows as $k => $id) $db->Query("DELETE FROM ".$this->name." WHERE ".$this->key."='".$id."'"); } else trigger_error("Delete rows from table '<b>".$this->name."</b>': Id's not defined", ERROR); return true; }
   function StoreRow() { $db = Site::GetDB(); if ($this->data[$this->key] > 0) { $updateArr[$this->data[$this->key]] = $this->data; return $this->UpdateRows($updateArr); } else return $this->SaveRows(array($this->data)); }
}
?>
