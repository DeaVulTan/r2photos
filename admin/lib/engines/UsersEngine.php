<?php
class UsersEngine extends AdminEngine {
   function UsersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Users::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['like'] = Site::GetSession($this->name."-like");
      return Users::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         if ($_POST['fromFF']) {
            Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         }
         return Site::CreateUrl($this->name."-list");
      }
      if ($this->parms['action'] == 'send') {
         $user =& Users::Get($this->values[0]);
         $form_data =& $user->GetData();
         $passMail = Utils::RandString(8);
         $upd[$this->values[0]]['password'] = md5($form_data['email'].":".$passMail);
         Users::Update($upd);
         $form_data['password'] = $passMail;
         $form_data['subject'] = Utils::GetValue('subject_mail_after_do_registration');
         $mailFiles = array(
            'logo' => Site::GetParms('absolutePath').'image/logo.png',
            'pixel' => Site::GetParms('absolutePath').'image/0.gif'
         );
         foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
         $form_data['iwh'] = getimagesize($mailFiles['logo']);
         $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
         $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
         include_once(Site::GetParms('libPath').'Mail.php');
         $message =& MailMessage::GetFromTemplate(
                        array(
                           'FROM'    => Utils::GetValue('mailer_email'),
                           'TO'      => $form_data['email'],
                           'CONTENT-TYPE' => 'text/html',
                           'SUBJECT' => $form_data['subject']
                        ),
                        Site::GetTemplate('layout', 'mail-common'),
                        $form_data,
                        array_values($mailFiles)
                     );
         $message->send();
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function getSendStr($name, $item) {
      echo '<a href="'.Site::CreateUrl($this->name.'-send', array($item['id'])).'" title="Сменить пароль и выслать юзеру">Сменить пароль и выслать юзеру</a>';
   }

   function doBeforeRun() {
      if (IS_USERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'idate');
         Site::SetSession($this->name."-order", 'desc');
      }
   }
}
?>