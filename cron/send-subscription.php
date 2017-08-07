<?php

// Рассылка


require('helpers/standalone.php');

class subscriptionSender extends standaloneApplication {
  private $mailerEmail, $subscriptions;
  public function __construct($config) {
    parent::__construct($config);
    $db = $this->getDBO();
    $this->mailerEmail = $db->SelectValue("SELECT value FROM config WHERE name='mailer_email'");
  }

  public function resetLocks() {
    $db = $this->getDBO();
    $db->Query("UPDATE subscribers_main SET `is_send_now` = 0");
  }

  public function loadSubscription() {
    $db = $this->getDBO();
    $db->Query("LOCK TABLES `subscribers_main` WRITE");
    $conditions = "WHERE `is_send`=1 AND `is_send_now`=0";
    $this->subscriptions = $db->SelectSet("SELECT `id`, `theme`, `text` FROM `subscribers_main` ".$conditions);
    $db->Query("UPDATE `subscribers_main` SET is_send_now=1 ".$conditions);
    $db->Query("UNLOCK TABLES");
    return count($this->subscriptions);
  }

  public function loadEmails() {
    $db = $this->getDBO();
    $query = "SELECT id, email, is_news FROM `subscribers` WHERE `is_active` = 1";
    $this->targetEmails = $db->SelectSet($query);
    return count($this->targetEmails);
  }

  public function sendMails() {
    $db = $this->getDBO();
    $countMails = 0;
    foreach($this->subscriptions as $subscription) {
      $theme = $subscription['theme'];
      list($text, $files) = $this->attachFiles($subscription['text']);
      foreach($this->targetEmails as $targetEmail) {
        $mailBody = preg_replace('/\{CODE\}/', md5($targetEmail['email'].":".$targetEmail['id']), $text);

        if($targetEmail['is_news'] == 0) {
          $mailBody = preg_replace('~<!-- NEWS -->.*?<!-- /NEWS -->~s', '', $mailBody);
        }

        $message = new MailMessage(array('FROM' => $this->mailerEmail,
        'TO' => $targetEmail['email'],
        'CONTENT-TYPE' => 'text/html',
        'SUBJECT' => $theme),
        $mailBody, $files);
        $message->send();
        $countMails++;
      }
      $db->Query("UPDATE `subscribers_main` SET `idate_send`='".time()."', `is_send`=0, `is_send_now`=0 "
      ."WHERE id=".(int)$subscription['id']);
    }
    return $countMails;
  }

  private function attachFiles($text) {
    $text = preg_replace('/src=\"http:\/\/([^\/]*)?\//', 'src="', $text);
    $text = preg_replace('/src=\"\//', 'src="', $text);
    $text = preg_replace('/data\/image\//', '', $text);
    preg_match_all('/src=\"([^\"]+)\"/', $text, $m);
    $files = array();
    $imagesPath = 'data/image/';
    $imagesAbsPath = ABS_PATH.$imagesPath;
    if (count($m[1]) > 0) foreach ($m[1] as $fileName) {
      if (!empty($fileName) && file_exists($imagesAbsPath.$fileName)) {
        $files[] = $imagesAbsPath.$fileName;
        $text = preg_replace('/'.preg_replace('/([\/\.\(\)\#\"\'\[\]\*\^\&\$\!\+\|\?])/', '\\\\\1', $fileName).'/', "cid:".md5($imagesAbsPath.$fileName), $text);
        }
      }
    return array($text, $files);
  }
}

define('ABS_PATH', preg_replace('/cron\/?(.*)?$/U', '', dirname(__FILE__)));

$ssConfig = array('name' => 'invitesConfig', 'timeLimit' => 180);

$subscriptionSender = new subscriptionSender($ssConfig);
// $subscriptionSender->resetLocks();
if ($subscriptionSender->loadSubscription() > 0) {
  if ($subscriptionSender->loadEmails() > 0) {
    $countMails = $subscriptionSender->sendMails();
    echo 'Mails sended: '.(int)$countMails."\n";
  }
}