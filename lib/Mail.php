<?php
include_once( 'lib/class.phpmailer.php' );
class MailMessage
{
  var $mailText = false;
  var $attributes = array('FROM' => '', 'TO' => '', 'CC' => '', 'BCC' => '', 'SUBJECT' => '', 'CHARSET' => 'UTF-8', "CONTENT-TYPE" => "text/plain", "MULTIPART" => "mixed" /* "alternative" */ , "FILE-DISPOSITION" => "attachment", "IMAGE-DISPOSITION" => "inline");
  var $mimes = array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 'zip' => 'application/zip', 'rar' => 'application/rar', 'doc' => 'application/msword', 'xls' => 'application/msexcel');
  var $attachments = array();
  var $body = '';
  function MailMessage($attributes, $body = '', $attachments = array()) { $this->_setAttributes($attributes); $this->setBody($body); $this->addAttachments($attachments); }
  function set($name, $value) { $this->attributes[strtoupper($name)] = $value; }
  function get($name) { return $this->attributes[strtoupper($name)]; }
  function setTo($to, $cc = '', $bcc = '') { $this->set('TO', $to); if ($cc != '') $this->set('CC', $cc); if ($bcc != '') $this->set('BCC', $bcc); }
  function setFrom($from) { $this->set('FROM', $from); }
  function setBody($text) { $this->body = $text; $this->mailText = ''; }
  function _setAttributes($attributes) { foreach ($attributes as $name => $value) $this->set($name, $value); }
  function addAttachments($files) { if (is_array($files)) foreach ($files as $file) $this->attachments[] = $file; else $this->attachments[] = $files; }
  function clearAttachments() { $this->attachments = array(); }
  function _makeAttachmentPart(&$mail, $file, $isInline = false)
  {
    if( $isInline ) {
        return $mail->AddEmbeddedImage( $file, md5( $file ), basename( $file ) );
    }
    return $mail->AddAttachment( $file );
  }
  function loadTemplate($template, $parms) { $this->body = MailMessage::_HandleTemplate($template, $parms); }
  function &GetFromTemplate($attributes, $template, $parms = array(), $attachments = array()) { return new MailMessage($attributes, MailMessage::_HandleTemplate($template, $parms), $attachments); }
  function _HandleTemplate($template, $parms) { if (!file_exists($template)) trigger_error("Unable to load template ".preg_replace('/^(.+)\/([^\/]+)$/', '\\2', $template), ERROR); ob_start(); include($template); $text = ob_get_contents(); ob_end_clean(); return $text; }
  function IsValidAddress($email) { if (preg_match('/<(.+)>$/', $email, $m)) $email = $m[1]; return preg_match("/^[0-9A-Za-z\._-]+@[0-9A-Za-z\._-]+\.[a-zA-Z]{2,4}/", $email); }
  function encodeString($str) { if (preg_match('/^=\?/',$str)) return $str; return '=?'.$this->get('CHARSET').'?B?'.base64_encode($str).'?='; }
  function encodeEmail($email) { if (preg_match('/([^<>]+)\s+<([^<>]+)>/',$email,$m)) { $name = $this->encodeString($m[1]); $email = $m[2]; return "$name <$email>"; } return $email; }
  function prepare() { if (!$this->mailText) { $this->mailHeaders = 'From: '.$this->encodeEmail($this->get('FROM'))."\n". 'Reply-To: '.$this->encodeEmail($this->get('FROM'))."\n"; if ($this->get('CC') != '') $this->mailHeaders .= 'Cc: '.$this->encodeEmail($this->get('CC'))."\n"; if ($this->get('BCC') != '') $this->mailHeaders .= 'Bcc: '.$this->encodeEmail($this->get('BCC'))."\n"; $this->mailHeaders .= "MIME-Version: 1.0\n"; if (sizeOf($this->attachments) > 0) { $bound = "--------".strtoupper(uniqid('')); $this->mailHeaders .= "Content-Type: multipart/".$this->get('MULTIPART')."; boundary=\"$bound\""; $this->mailText = "--$bound\n". "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n". "Content-Transfer-Encoding: 8bit\n\n". $this->body."\n"; foreach ($this->attachments as $file) $this->mailText .= "--$bound\n".$this->_makeAttachmentPart($file); $this->mailText .= "\n--$bound--\n\n"; } else { $this->mailHeaders .= "Content-Type: ".$this->get('CONTENT-TYPE')."; charset=".$this->get('CHARSET')."\n". "Content-Transfer-Encoding: 8bit"; $this->mailText = $this->body; } } }
  function send($emailTo = '')
  {
    $this->MakeMailImages();
    $emailTo = (!$emailTo ? $this->get('TO') : $emailTo);
    $mail = new PHPMailer();
    $mail->CharSet = $this->get('CHARSET');
    $mail->From = $this->get('FROM');
    $mail->FromName = '';
    foreach( explode( ',', $emailTo ) as $oneEmail ) {
        $oneEmail = trim( $oneEmail );
        if( !empty( $oneEmail ) ) {
            $mail->AddAddress( $oneEmail );
        }
    }
    $mail->Subject = $this->get('SUBJECT');
    $mail->Body = $this->body;
    $mail->IsHTML( true );

    foreach( $this->attachments as $file ) {
        if( preg_match( '/\.(jpg|jpeg|bmp|gif|png|tga)$/i', $file ) ) {
            $this->_makeAttachmentPart($mail, $file, true);
        }
    }

    foreach( $this->attachments as $file ) {
        if( !preg_match( '/\.(jpg|jpeg|bmp|gif|png|tga)$/i', $file ) ) {
            $this->_makeAttachmentPart( $mail, $file );
        }
    }

    $result = $mail->Send();
    return $result;
  }

  function MakeMailImages()
  {
    $files = array();
    $text = $this->body;
    $text = preg_replace('/src=\"http:\/\/([^\/]*)?\//', 'src="', $text);
    $text = preg_replace('/src=\"\//', 'src="', $text);
    preg_match_all('/src=\"([^\"]+)\"/', $text, $m);
    if (sizeOf($m[1]) > 0) foreach ($m[1] as $k => $v) {
    if (preg_match( "#\/#", $v ) && file_exists($v)) {
        $files[] = $v;
        $text = preg_replace('/'.preg_replace('/([\/\.\(\)\#\"\'\[\]\*\^\&\$\!\+\|\?])/', '\\\\\1', $v).'/', "cid:".md5($v), $text);
        }
    }
    $this->setBody( $text );
    $this->addAttachments( $files );
  }
}

?>