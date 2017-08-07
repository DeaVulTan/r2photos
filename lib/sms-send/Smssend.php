<?php
require_once (Site::GetParms('libPath').'sms-send/sms24x7.php');

class smsSend {
  public $email;
  public $pass;
  public $phones;
  public $sms;

  public function __construct($phones, $sms, $email, $pass) {
    $this->phones = $phones;
    $this->sms = $sms;
    $this->email = $email;
    $this->pass = $pass;
  }
  
  public function Send() {
    try {
        $api = new sms24x7($this->email, $this->pass);
        $ret = $api->call_method('push_msg', array('phones' => json_encode($this->phones), 'text' => $this->sms));
        var_dump($ret);
    }
    catch (Exception $e) {
        print 'Ошибка: '.$e->getMessage()."\n";
    }
  }
}
?>