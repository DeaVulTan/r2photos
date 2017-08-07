<?php

class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) die("<b>MySQL</b>: Unable to connect to database"); if (!mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString) { if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectValue($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_row($resourseId); @mysql_free_result($resourseId); return $row[0]; }
   function SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row = mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function SelectSet($sqlString, $idTable = '') { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); while ($rowOne = mysql_fetch_assoc($resourseId)) { if ($idTable) $row[$rowOne[$idTable]] = $rowOne; else $row[] = $rowOne; } @mysql_free_result($resourseId); return $row; }
   function SelectLastInsertId() { return @mysql_insert_id($this->dbId); }
   function Destroy() { if (!@mysql_close($this->dbId)) die("Cann't disconnect from database"); }
}

class MailMessage { var $mailText = false; var $attributes = array('FROM' => '', 'TO' => '', 'CC' => '', 'BCC' => '', 'SUBJECT' => '', 'CHARSET' => 'utf-8', "CONTENT-TYPE" => "text/plain", "MULTIPART" => "mixed", "FILE-DISPOSITION" => "attachment"); var $mimes = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'zip' => 'application/zip', 'rar' => 'application/rar', 'doc' => 'application/msword', 'xls' => 'application/msexcel'); var $attachments = array(); var $body = ''; function MailMessage($attributes, $body = '', $attachments = array()) { $this->_setAttributes($attributes); $this->setBody($body); $this->addAttachments($attachments); } function set($name, $value) { $this->attributes[strtoupper($name)] = $value; } function get($name) { return $this->attributes[strtoupper($name)]; } function setTo($to, $cc = '', $bcc = '') { $this->set('TO', $to); if ($cc != '') $this->set('CC', $cc); if ($bcc != '') $this->set('BCC', $bcc); } function setBody($text) { $this->body = $text; $this->mailText = ''; } function _setAttributes($attributes) { foreach ($attributes as $name => $value) $this->set($name, $value); } function addAttachments($files) { if (is_array($files)) foreach ($files as $file) $this->attachments[] = $file; else $this->attachments[] = $files; } function clearAttachments() { $this->attachments = array(); }
   function _makeAttachmentPart($file) { if (is_array($file)) { $name = $file["NAME"]; preg_match("/([^.]+)$/",$file["NAME"],$matches); $extension = $matches[0]; $type = (isset($this->mimes[$extension]) ? $this->mimes[$extension] : 'application/octet-stream'); $dataLen = mb_strlen($data = base64_encode($file["CONTENT"])); } else { if (!file_exists($file)) trigger_error("Unable to open file ".preg_replace('/^(.+)\/([^\/]+)$/', '\\2', $file), ERROR); $name = basename($file); $info = pathinfo($file); $type = (isset($this->mimes[$info['extension']]) ? $this->mimes[$info['extension']] : 'application/octet-stream'); $fid = fopen($file, 'r'); $dataLen = mb_strlen($data = base64_encode(fread($fid, filesize($file)))); fclose($fid); } for ($text = '', $posted = 0, $cutLen = 76; $posted < $dataLen; $posted += $cutLen) { if ($posted + $cutLen > $dataLen) $cutLen = $dataLen - $posted; $text .= mb_substr($data, $posted, $cutLen)."\n"; } $text = "Content-Type: $type; name=\"".$this->encodeString($name)."\"\nContent-ID: <".md5($file).">\nContent-Transfer-Encoding: base64\nContent-Disposition: ".$this->get('FILE-DISPOSITION')."; filename=\"".$this->encodeString($name)."\"\n\n$text"; return $text; } function loadTemplate($template, $parms) { $this->body = MailMessage::_HandleTemplate($template, $parms); } function &GetFromTemplate($attributes, $template, $parms = array(), $attachments = array()) { return new MailMessage($attributes, MailMessage::_HandleTemplate($template, $parms), $attachments); } function _HandleTemplate($template, $parms) { if (!file_exists($template)) trigger_error("Unable to load template ".preg_replace('/^(.+)\/([^\/]+)$/', '\\2', $template), ERROR); ob_start(); include($template); $text = ob_get_contents(); ob_end_clean(); return $text; }
   function IsValidAddress($email) { if (preg_match('/<(.+)>$/', $email, $m)) $email = $m[1]; return preg_match("/^[0-9A-Za-z\._-]+@[0-9A-Za-z\._-]+\.[a-z]{2,4}/", $email); } function encodeString($str) { if (preg_match('/^=\?/',$str)) return $str; return '=?'.$this->get('CHARSET').'?B?'.base64_encode($str).'?='; } function encodeEmail($email) { if (preg_match('/([^<>]+)\s+<([^<>]+)>/',$email,$m)) { $name = $this->encodeString($m[1]); $email = $m[2]; return "$name <$email>"; } return $email; } function prepare() { if (!$this->mailText) { $this->mailHeaders = 'From: '.$this->encodeEmail($this->get('FROM'))."\n". 'Reply-To: '.$this->encodeEmail($this->get('FROM'))."\n"; if ($this->get('CC') != '') $this->mailHeaders .= 'Cc: '.$this->encodeEmail($this->get('CC'))."\n"; if ($this->get('BCC') != '') $this->mailHeaders .= 'Bcc: '.$this->encodeEmail($this->get('BCC'))."\n"; $this->mailHeaders .= "Mime-Version: 1.0\n"; $this->mailHeaders .= "X-Mailer: PHP\n"; if (sizeOf($this->attachments) > 0) { $bound = "--------".strtoupper(uniqid('')); $this->mailHeaders .= "Content-Type: multipart/".$this->get('MULTIPART')."; boundary=\"$bound\""; $this->mailText = "--$bound\n". "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n". "Content-Transfer-Encoding: 8bit\n\n". $this->body."\n"; foreach ($this->attachments as $file) $this->mailText .= "--$bound\n".$this->_makeAttachmentPart($file); $this->mailText .= "\n--$bound--\n\n"; } else { $this->mailHeaders .= "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n". "Content-Transfer-Encoding: 8bit"; $this->mailText = $this->body; } } }
   function send($emailTo = '') {
   $emailTo = (!$emailTo ? $this->get('TO') : $emailTo);
   if (!$this->mailText) {
    if (!MailMessage::IsValidAddress($emailTo) || ($this->get('CC') && !MailMessage::IsValidAddress($this->get('CC'))) || ($this->get('BCC') && !MailMessage::IsValidAddress($this->get('BCC')))) trigger_error('E-Mail address not valid!', ERROR);
    $this->mailHeaders = 'From: '.$this->encodeEmail($this->get('FROM'))."\n". 'Reply-To: '.$this->encodeEmail($this->get('FROM'))."\n";
    if ($this->get('CC') != '') $this->mailHeaders .= 'Cc: '.$this->encodeEmail($this->get('CC'))."\n";
    if ($this->get('BCC') != '') $this->mailHeaders .= 'Bcc: '.$this->encodeEmail($this->get('BCC'))."\n";
    $this->mailHeaders .= "MIME-Version: 1.0\n";
    $this->mailHeaders .= "X-Mailer: PHP\n";
    if (sizeOf($this->attachments) > 0)
      {
        $bound = "--".strtoupper(uniqid(''));
        $related = "--".strtoupper(uniqid(''));

        $this->mailHeaders .= "Content-Type: multipart/".$this->get('MULTIPART').";\n boundary=\"{$bound}\"\n\n--{$bound}\n"; //mixed

        //обёртывание в related
        $this->mailHeaders .= "Content-Type: multipart/related;\n boundary=\"{$related}\"\n\n\n--{$related}\n".
          "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n".
          "Content-Transfer-Encoding: 8bit\n\n".$this->body."\n\n"  ; //related

        //Картинки inline images
        foreach ($this->attachments as $file)
            if(preg_match('/\.(jpg|jpeg|bmp|gif|png|tga)$/i', $file))
                $this->mailText .= "\n--{$related}\n".$this->_makeAttachmentPart($file, 'inline');
        $this->mailText .= "\n--{$related}--\n\n"; //end of related
        //Приаттаченные файлы
        foreach ($this->attachments as $file)
            if(!preg_match('/\.(jpg|jpeg|bmp|gif|png|tga)$/i', $file))
                $this->mailText .= "\n--{$bound}\n".$this->_makeAttachmentPart($file);
        $this->mailText .= "\n--{$bound}--\n\n"; //end of bound
    }
    /*if (sizeOf($this->attachments) > 0) {
        $bound = "--------".strtoupper(uniqid(''));
        $this->mailHeaders .= "Content-Type: multipart/".$this->get('MULTIPART')."; boundary=\"$bound\"";
        $this->mailText = "--$bound\n". "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n". "Content-Transfer-Encoding: 8bit\n\n". $this->body."\n";
        foreach ($this->attachments as $file) $this->mailText .= "--$bound\n".$this->_makeAttachmentPart($file);
        $this->mailText .= "\n--$bound--\n\n";
    }*/
    else {
        $this->mailHeaders .= "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n". "Content-Transfer-Encoding: 8bit";
        $this->mailText = $this->body;
    }
   }
   return mail($this->encodeEmail($emailTo), $this->encodeString($this->get('SUBJECT')), $this->mailText, $this->mailHeaders);
   }
}

class standaloneHelper {
  public function getFloatFromStr($str) {
    return (float) preg_replace('/,/', '.', preg_replace('/[^0-9.,]+/', '', $str));
    }
  }

abstract class standaloneApplication {

  protected $config;
  public function __construct($config) {
    $this->initialize();
    if (!$this->lock()) $this->termination('Locked');
    $this->config = $config;
    set_time_limit((int)$this->config['timeLimit'] > 0 ? (int)$this->config['timeLimit'] : 30);
    }

  private function lock() {
    if (is_array($GLOBALS['argv'])) {
      return (boolean) flock(
          $this->lock = fopen(
              $GLOBALS['argv'][0], 'r'),
              LOCK_EX | LOCK_NB);
      }
    return true;
    }

  private function unlock() {
    if ($this->lock) {
      fclose($this->lock);
      }
    }


  private static $configFileValues, $initialized = false;
  public function initialize() {
    if (self::$initialized) return;
    self::$initialized = true;
    setlocale(LC_ALL, 'ru_RU.UTF-8');

    if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');
    header('Content-Type: text/html; charset=utf-8');
    require(ABS_PATH.'config.php');
    self::$configFileValues = $config;
    }

  public function log($info) {
    return;
    $db = $this->getDBO();
    $status = addslashes($info['status']);
    switch (true) {
      case is_array($info['data']):
      case is_object($info['data']):
        ob_start();
        var_dump($info['data']);
        $data = addslashes(ob_get_contents());
        ob_end_clean();
        break;
      default:
        $data = addslashes($info['data']);
        }
    $query = "INSERT INTO `` (``, ``) VALUES "
      ."(NOW(), '$status', '$data')";
    $db->Query($query);
    }


  public function termination($msg = null) {
    if (!empty($msg)) echo $msg;
    $this->unlock();
    $this->__destruct();
    die();
    }

  public function __destruct(){
    $this->unlock();
    }

  private static $dbo;
  public function getDBO() {
    if (is_object(self::$dbo)) return self::$dbo;
    preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', self::$configFileValues['database'], $arDatabase);
    self::$dbo = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);
    self::$dbo->Query("SET NAMES UTF8");
    return self::$dbo;
    }


  }


?>