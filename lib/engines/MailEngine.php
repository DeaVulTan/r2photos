<?php
include_once(Site::GetParms('tablesPath')."Mainmenu.php");
class MailEngine {
   var $name; var $parms;
   function MailEngine($name, $parms) {
        $this->name = $name;
        $this->parms = $parms;
        $parmsT = array('href' => 'mail');
        if (defined("LANG")) $parmsT['lang'] = LANG;
        $menu = Mainmenu::GetRow($parmsT);
        $this->menu = $menu;
        $this->menuPath = (defined('LANG') && LANG <> 'ru' ? '/'.LANG : '').($this->menu['path'] ? $this->menu['path'] : '/');
        define("MENU_ID", ($this->menu['id'] ? $this->menu['id'] : 0));
        define("MENU_NAME", ($this->menu['name'] ? $this->menu['name'] : 'Обратная связь'));
        define("MENU_PATH", $this->menuPath);
        unset($menu);
   }

   function run() {
      if (!MENU_ID) Site::SetParms('bread-crumbs', array(MENU_NAME));
      if ($this->parms['action'] == 'send') return $this->doSend();
      else if ($this->parms['action'] == 'ok') return $this->viewOk();
      else return $this->viewForm();
   }

   function viewForm() {
      $form = $this->createForm();
      include(Site::GetTemplate($this->name, 'form'));
      return true;
   }

   function doSend() {
      if( !Utils::CheckMouseMove() ) {
          return false;
      }
      $form = $this->createForm();
      $form->processIfSubmitted();
      if (preg_match("/".mb_strtolower(PROJECT_NAME, 'utf-8')."/", $_SERVER['HTTP_REFERER'])) {
         $form_data = $form->get();
         foreach ($form_data as $k => $v) $form_data[$k] = strip_tags($v);
        
        if(!preg_match('/[0-9]{3,}/', $form_data['phone'])){
         $alert = 'Укажите номер телефона!';
         unset($form_data['phone'], $form_data['code']);
         $form->assign($form_data);
         include(Site::GetTemplate($this->name, 'form'));
         if( Site::GetParms( 'isAjax' ) ) {
            die();
         }
         return true;
        }
        
        if(!preg_match('/^[0-9A-Za-z._-]+@([0-9a-z_-]+\.)+[a-z]{2,4}$/', $form_data['email'])){
         $alert = 'Укажите правильный e-mail!';
         unset($form_data['email'], $form_data['code']);
         $form->assign($form_data);
         include(Site::GetTemplate($this->name, 'form'));
         if( Site::GetParms( 'isAjax' ) ) {
            die();
         }
         return true;
        }
         
         include_once(Site::GetParms('libPath').'Mail.php');
         $form_data['subject'] = Utils::GetValue('subject_mail_after_send_mail');
         $mailFiles = array(
            'logo' => Site::GetParms('absolutePath').'image/logo.png',
            'pixel' => Site::GetParms('absolutePath').'image/0.gif'
         );

         $newMail = Mail::Get( 0, true );
         $newMail->SetValue( 'idate', time() );
         $newMail->SetData( $form_data );
         $newMail->StoreRow();

         foreach ($mailFiles as $k => $v) $form_data[$k] = "cid:".md5($v);
         $form_data['iwh'] = getimagesize($mailFiles['logo']);
         $form_data['bottom_address'] = Utils::GetValue('address_bottom_mail');
         $form_data['bottom_line'] = Utils::GetValue('line_bottom_mail');
         $message = MailMessage::GetFromTemplate(
                     array(
                        'FROM'    => $form_data['email'],
                        'TO'      => Utils::GetValue('support_email'),
                        'CONTENT-TYPE' => 'text/html',
                        'SUBJECT' => $form_data['subject']
                     ),
                     Site::GetTemplate('layout', 'mail-common'),
                     $form_data,
                     array_values($mailFiles)
                  );
         $message->send();
         if( Site::GetParms( 'isAjax' ) ) {
            $this->viewOk();
            die();
         }
         return Site::CreateUrl($this->menuPath.$this->name.'/ok');
      }
      else return Site::CreateUrl($this->menuPath.$this->name);
   }

   function viewOk() {
      include(Site::GetTemplate($this->name, 'ok'));
      return true;
   }

   function createForm() {
      include_once(Site::GetParms('libPath').'Form.php');
      return new Form(
         array(
               new Input('service'),
               new Input('phone'),
               new Input('email'),
               new TextArea('text')
              ),
         array(
         'name' => $this->name.'Form',
         'id' => $this->name.'Form',
         'data-validate-style' => 'alert',
         'action' => $this->menuPath.$this->name.'/send'
         )
      );
   }
}
?>
